<?php

namespace Modules\Shop\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;
use Modules\Shop\Models\CartItem;

class CartService
{
    public function items(User $user): Collection // D24: lấy giỏ của đúng user đang đăng nhập, không nhận user_id từ form.
    {
        return CartItem::with('product')->where('user_id', $user->id)->orderBy('product_id')->get(); // D24: with tải trước product; where giới hạn chủ giỏ, tránh đọc giỏ người khác.
    }

    public function change(User $user, int $productId, int $quantity, bool $add = false): void // D24: add=true cộng thêm; mặc định false đặt quantity mới.   // false: Cập nhật số lượng thành giá trị mới. // - true: Cộng thêm số lượng vào số lượng hiện tại.  // void : Phương thức có thể thực hiện cập nhật database nhưng không trả về kết quả bằng return $value.
    {
        DB::transaction(function () use ($user, $productId, $quantity, $add) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail(); // D24/D27: khóa user trong transaction để các thao tác giỏ/checkout cùng user nối tiếp trên MySQL.
            $product = Product::find($productId); // D24: đọc stock hiện tại từ database, không tin stock do browser gửi.
            if (! $product) {
                throw ValidationException::withMessages(['cart' => 'Sản phẩm không còn tồn tại.']);
            }
            $item = CartItem::where('user_id', $user->id)->where('product_id', $productId)->first();
            $qty = $add ? ($item?->quantity ?? 0) + $quantity : $quantity; // D24: cùng product tăng số lượng; chưa có item thì qty cũ là 0.
            if ($qty < 1 || $qty > $product->stock) { // D24: chặn qty=0, số âm hoặc vượt tồn; kiểm số lượng sau khi cộng.
                throw ValidationException::withMessages(['quantity' => 'Số lượng phải từ 1 đến tồn kho hiện tại.']);
            }CartItem::updateOrCreate(['user_id' => $user->id, 'product_id' => $productId], ['quantity' => $qty]); // D24: tìm theo cặp user/product để cập nhật hoặc tạo một dòng; unique ở DB là chốt bổ sung.
        });
    }

    public function remove(User $user, ?int $productId = null): void // D24: có productId thì xóa một item; null thì clear toàn bộ giỏ của user này.    /  void : Phương thức có thể thực hiện cập nhật database nhưng không trả về kết quả bằng return $value.
    {
        DB::transaction(function () use ($user, $productId) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $q = CartItem::where('user_id', $user->id); // D24: luôn giới hạn user trước khi delete để không xóa giỏ người khác.
            if ($productId !== null) {
                $q->where('product_id', $productId);
            }
            $q->delete();
        });
    }

    public function cents(string $price): int // D24/D25: đổi DECIMAL dạng chuỗi sang integer đơn vị 1/100 VND, tránh tính tiền bằng float.
    { // DECIMAL là kiểu dữ liệu số thập phân có độ chính xác cố định trong MySQL, thường dùng để lưu giá sản phẩm, tiền lương, số dư, doanh thu và các giá trị tài chính.
        [$whole,$fraction] = array_pad(explode('.', $price, 2), 2, ''); // $whole phần nguyên , $fraction phần thập phân

        return ((int) $whole) * 100 + (int) str_pad(substr($fraction, 0, 2), 2, '0');
        // (int) $whole	              Chuyển phần nguyên từ string sang int	            150000
        //    * 100	                     Chuyển phần nguyên sang đơn vị 1/100       	15000000
        //    substr($fraction, 0, 2)   	Lấy tối đa 2 ký tự đầu của phần thập phân      "75"
        //    str_pad(..., 2, '0')      	Thêm số 0 bên phải nếu chưa đủ 2 ký tự      	"75"
        //    (int)	                    Chuyển chuỗi thành số nguyên	                   75
        //    +	                        Cộng phần nguyên và phần thập phân	            15000075
    }

    public function subtotal(Collection $items): int // D24: tổng = cộng giá hiện tại x quantity từng item; product mất tính 0 khi xem, checkout vẫn reject.
    {
        return $items->sum(fn ($i) => $i->product ? $this->cents($i->product->price) * $i->quantity : 0);
    }
}
