<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Shop\Models\Order;
use Modules\Shop\Services\CartService;
use Modules\Shop\Services\CheckoutService;

class CartController extends Controller
{
    public function index(Request $request, CartService $cart, CheckoutService $checkout): View // D24/D28: GET /cart; Laravel inject request/services, controller chuẩn bị dữ liệu cho shop::cart.
    {
        if (! $request->session()->has('checkout_token') || Order::where('checkout_token', $request->session()->get('checkout_token'))->exists()) {
            $request->session()->put('checkout_token', (string) Str::uuid()); // D27: cấp token cho lượt checkout mới; mở giỏ sau order cũ sẽ đổi token.
        }
        $items = $cart->items($request->user()); // D24: user hiện tại do session/auth xác định; không lấy user_id từ input.
        $subtotal = $cart->subtotal($items); // D24: tính subtotal trên server; view chỉ hiển thị.
        // $subtotal  > gọi Service function subtotal(

        // return $items->sum(fn ($i) => $i->product ? $this->cents($i->product->price) * $i->quantity : 0);

        // sum : tính tổng > kiểm tra có product ko , có thì lấy funcion cents() trong Service (luật kiểu số xuất) >( lấy bảng product => giá  ) x tổng số lượng cột quantity bảng cart_items  > nếu ko có product thì = 0
        $coupon = null;
        $couponError = null;
        if ($request->session()->has('coupon')) {
            try {
                $coupon = $checkout->coupon($request->session()->get('coupon')); // D28: kiểm tra mã đã apply còn hợp lệ không trước khi preview.
            } catch (ValidationException $e) {
                $couponError = $e->getMessage();
            }
        }
        $discount = $coupon ? intdiv($subtotal * $coupon->discount_percent, 100) : 0;

        // $coupon?->code  : có biến $coupon ko nếu có lấy mục trong cột code trong bảng coupons  |  $coupon = lấy trong CheckoutService function coupon() : Lấy cột code trong bảng Coupon - xoá khoản trắng - chuyển chữ hoa thành thường > nếu có KHUYẾN MÃI thì cho lockforUpdate - first () lấy cort đầu tiên - kiểm tra ngày bắt đầu, ngày hết hạn ,đếm số lượng lớn hơn giới hạn , % chiếc khấu >1  ,% chiếc khấu > 100 > báo lỗi  ,
        //
        // $discount / 100, 2 : $discount kiểm tra có $coupon ?  intdiv (dùng để chia hai số nguyên và lấy phần nguyên của kết quả, bỏ phần thập phân.
        // `intdiv(int $num1, int $num2): int`
        // ) có : lấy tổng giá số lượng sản phẩm  x  %giảm giá từ cột discount_percent rồi chia cho 100 , Nếu không có Coupon thì giảm 0
        return view('shop::cart', compact('items', 'subtotal', 'discount', 'coupon', 'couponError')); // D24/D28: compact tạo biến cùng tên trong Modules/Shop/resources/views/cart.blade.php.
    }

    public function add(Request $request, CartService $cart): RedirectResponse // D24: POST /cart/items gọi add; thêm cùng product sẽ cộng quantity.
    {
        $data = $request->validate(['product_id' => 'required|integer|min:1', 'quantity' => 'sometimes|integer|min:1']);
        $cart->change($request->user(), $data['product_id'], $data['quantity'] ?? 1, true); // D24: không gửi quantity thì thêm 1; tham số true bật chế độ cộng thêm.

        return redirect()->route('cart.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function update(Request $request, int $product, CartService $cart): RedirectResponse // D24: PATCH /cart/items/{product}; $product là ID lấy từ URL.
    {
        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $cart->change($request->user(), $product, $data['quantity']); // D24: bỏ tham số add nên mặc định false, đặt quantity mới thay vì cộng.

        return back()->with('success', 'Đã cập nhật.');
    }

    public function remove(Request $request, int $product, CartService $cart): RedirectResponse // D24: DELETE một product trong cart hiện tại, không xóa Product khỏi catalog.
    {
        $cart->remove($request->user(), $product);

        return back();
    }

    public function clear(Request $request, CartService $cart): RedirectResponse // D24: DELETE /cart; service chỉ xóa cart của user hiện tại.
    {
        $cart->remove($request->user());

        return back();
    }

    public function applyCoupon(Request $request, CheckoutService $checkout): RedirectResponse // D28: POST /cart/coupon kiểm mã, lưu vào session để preview; chưa tăng used_count.
    {
        $data = $request->validate(['code' => 'required|string|max:100']);
        $coupon = $checkout->coupon($data['code']);
        $request->session()->put('coupon', $coupon->code); // D28: lưu mã đã chuẩn hóa, không tin percent/discount do browser gửi.

        return back()->with('success', 'Đã áp dụng coupon.');
    }

    public function removeCoupon(Request $request): RedirectResponse // D28: bỏ mã khỏi session; không xóa coupon trong database.
    {
        $request->session()->forget('coupon');

        return back();
    }

    public function checkout(Request $request, CheckoutService $checkout): RedirectResponse // D25/D27: POST /checkout gọi hàm này; không dùng GET để tạo order.
    {
        $data = $request->validate(['checkout_token' => 'required|uuid']);
        abort_unless(hash_equals((string) $request->session()->get('checkout_token'), $data['checkout_token']), 419); // D27: token phải khớp session; sai/trang cũ có thể 419. Token này khác CSRF token.
        $order = $checkout->checkout($request->user(), $data['checkout_token'], $request->session()->get('coupon')); // D25/D28: giao business logic và transaction cho service; fail sẽ không chạy các dòng success tiếp theo.
        $request->session()->forget('coupon');

        return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công.'); // D25/D26: thành công chuyển GET /my-orders/{id}; OrderController kiểm owner rồi hiện chi tiết.
    }
}
