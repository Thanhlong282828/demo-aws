<?php

namespace Modules\Shop\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;
use Modules\Shop\Models\CartItem;
use Modules\Shop\Models\Coupon;
use Modules\Shop\Models\Order;

class CheckoutService
{
    public function __construct(private CartService $cart) {} // D25: Laravel inject CartService để tái sử dụng hàm đổi tiền, không phải gọi constructor thủ công.

    public function coupon(string $code, bool $lock = false): Coupon // D28: dùng chung validation khi Apply và khi Checkout; checkout truyền lock=true trong transaction.
    {
        $query = Coupon::where('code', strtoupper(trim($code))); // D28: bỏ khoảng trắng, chuẩn hóa mã in hoa rồi tìm trong database.
        if ($lock) {
            $query->lockForUpdate(); // D28: khóa coupon trước khi kiểm lượt dùng để các checkout tranh lượt xử lý nối tiếp trên MySQL.
        }
        $coupon = $query->first();
        if (! $coupon || ! $coupon->active || today()->lt($coupon->start_date) || today()->gt($coupon->end_date) || $coupon->used_count >= $coupon->usage_limit || $coupon->discount_percent < 1 || $coupon->discount_percent > 100) {
            throw ValidationException::withMessages(['coupon' => 'Coupon không hợp lệ, hết hạn hoặc đã hết lượt dùng.']); // D28: reject mã thiếu/inactive/chưa tới ngày/hết hạn/hết lượt hoặc percent sai, không tự bỏ mã rồi mua nguyên giá.
        }

        return $coupon;
    }

    public function checkout(User $user, string $token, ?string $code = null): Order // D25: nhận user từ server, token từ form đã đối chiếu session, code coupon từ session.
    {
        return DB::transaction(function () use ($user, $token, $code) { // D25: order/items/stock/coupon/cart cùng transaction; exception sẽ rollback tất cả, thành công mới commit.
            User::whereKey($user->id)->lockForUpdate()->firstOrFail(); // D27: cùng user khóa chung với CartService; chống cart bị sửa giữa lúc checkout.
            $existing = Order::where('checkout_token', $token)->first(); // D27: tìm order của token cũ trước khi xét giỏ rỗng để browser retry vẫn nhận order đã tạo.
            if ($existing) {
                abort_unless($existing->user_id === $user->id, 403);

                return $existing; // D27: replay trả cùng order, không tạo items mới, không trừ stock hay tăng coupon lần nữa.
            }
            $items = CartItem::where('user_id', $user->id)->orderBy('product_id')->lockForUpdate()->get(); // D25: lấy và khóa đúng cart của user; giỏ rỗng thì từ chối.
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng trống.']);
            }
            $products = Product::whereIn('id', $items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id'); // D25: khóa products theo thứ tự ID ổn định, rồi kiểm tồn kho lại ngay trong transaction.
            $subtotal = 0;
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if (! $product || $item->quantity < 1 || $product->stock < $item->quantity) { // D25/D27: reject product đã xóa, quantity sai hoặc stock giảm sau khi add cart.
                    throw ValidationException::withMessages(['stock' => 'Sản phẩm đã bị xóa hoặc không đủ tồn kho.']);
                }
                $subtotal += $this->cart->cents($product->price) * $item->quantity; // D25: chọn giá hiện tại lúc checkout; server tự tính, không nhận total từ client.
            }
            $coupon = $code ? $this->coupon($code, true) : null; // D28: validate lại dù Apply trước đó đã hợp lệ, vì coupon có thể đổi/hết hạn/hết lượt.
            $discount = $coupon ? intdiv($subtotal * $coupon->discount_percent, 100) : 0; // D28: tính % trên subtotal integer; làm tròn xuống đến 0.01 VND.
            $order = Order::create(['user_id' => $user->id, 'checkout_token' => $token, 'subtotal' => $subtotal, 'discount_amount' => $discount, 'total' => $subtotal - $discount, 'coupon_code' => $coupon?->code, 'discount_percent' => $coupon?->discount_percent ?? 0]); // D25/D28: lưu bản chụp subtotal/discount/total/code/%; sửa coupon sau này không đổi order cũ.
            foreach ($items as $item) {
                $product = $products[$item->product_id];
                $order->items()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $item->quantity, 'unit_price' => $this->cart->cents($product->price)]); // D25: relationship tự gán order_id; lưu tên và giá lúc mua để giữ lịch sử khi product đổi/xóa.
                $product->decrement('stock', $item->quantity); // D25: trừ tồn trong cùng transaction với tạo order/items.
            }
            if ($coupon) {
                $coupon->increment('used_count'); // D28: chỉ checkout mới tăng lượt dùng; nếu bước sau fail thì transaction cũng rollback lượt này.
            }
            CartItem::where('user_id', $user->id)->delete(); // D25: clear cart ở cuối transaction; lỗi trước/sau đây đều rollback, không mất cart khi checkout fail.

            return $order;
        }, 3);
    }

    public function transition(Order $order, string $status): void // D26: chỉ admin route gọi sau khi qua middleware; service vẫn kiểm quy tắc trạng thái.
    {
        DB::transaction(function () use ($order, $status) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail(); // D26: đọc trạng thái mới nhất dưới lock; không tin trạng thái model lấy trước transaction.
            $allowed = ['pending' => ['confirmed', 'cancelled'], 'confirmed' => ['completed', 'cancelled'], 'completed' => [], 'cancelled' => []]; // D26: trạng thái cuối có mảng rỗng; không được completed->pending hay cancelled->completed.
            if (! in_array($status, $allowed[$order->status] ?? [], true)) { // D26: kiểm cả trạng thái nguồn và đích; chỉ kiểm tên nằm trong enum là chưa đủ.
                throw ValidationException::withMessages(['status' => 'Không được chuyển sang trạng thái này.']);
            }
            if ($status === 'cancelled') {
                foreach ($order->items()->orderBy('product_id')->get() as $item) {
                    Product::whereKey($item->product_id)->increment('stock', $item->quantity); // D26: hủy hoàn stock một lần; product đã xóa thì không được tạo lại; không hoàn lượt coupon theo design.
                }
            }
            $order->update(['status' => $status]); // D26: đổi status và hoàn stock cùng transaction; fail thì rollback cả hai.
        }, 3);
    }
}
