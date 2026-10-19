
{{-- 
    D24 / D25 / D28: GIỎ HÀNG

    CartController@index truyền:
    - $items       : Danh sách sản phẩm trong giỏ
    - $subtotal    : Tổng tiền trước giảm giá (đơn vị cents)
    - $discount    : Số tiền được giảm (đơn vị cents)
    - $coupon      : Coupon đang áp dụng
    - $couponError : Thông báo lỗi coupon

    method="POST"       : Gửi request POST
    @method('PATCH')    : Giả lập HTTP PATCH
    @method('DELETE')   : Giả lập HTTP DELETE
    route()             : Tạo URL từ tên route
--}}

{{-- Kế thừa layout chính --}}
@extends('layouts.app')

{{-- Bắt đầu nội dung trang --}}
@section('content')

    <h1>Giỏ hàng</h1>

    {{-- ============================================
         1. DANH SÁCH SẢN PHẨM TRONG GIỎ
         ============================================ --}}

    <table>

        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th>Thao tác</th>
            </tr>
        </thead>

        <tbody>

            {{-- Duyệt từng Cart Item trong $items --}}
            @forelse($items as $item)

                <tr>

                    {{-- 
                        Lấy tên sản phẩm qua quan hệ product.

                        ?-> : Nullsafe operator
                        ??  : Giá trị mặc định nếu null

                        Nếu sản phẩm đã bị xóa thì hiển thị thông báo.
                    --}}
                    <td>
                        {{ $item->product?->name ?? 'Sản phẩm đã xóa — cần bỏ khỏi giỏ' }}
                    </td>

                    {{-- Giá sản phẩm --}}
                    <td>
                        {{ $item->product?->price }}
                    </td>

                    {{-- ==================================
                         2. CẬP NHẬT SỐ LƯỢNG
                         ================================== --}}
                    <td>

                        <form
                            method="POST"
                            action="{{ route('cart.update', $item->product_id) }}"
                        >

                            {{-- CSRF Token --}}
                            @csrf

                            {{-- Giả lập phương thức PATCH --}}
                            @method('PATCH')

                            {{-- Số lượng sản phẩm --}}
                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                value="{{ $item->quantity }}"
                                required
                            >

                            <button>
                                Cập nhật
                            </button>

                        </form>

                    </td>

                    {{-- ==================================
                         3. TÍNH THÀNH TIỀN
                         // PHP thuần: tự khởi tạo đối tượng
                         $service = new CartService();                          

                         // Laravel: nhờ Service Container lấy đối tượng
                         $service = app(CartService::class);
                         cents() trong đoạn code của bạn là phương thức được lập trình viên tự viết trong CartService, không phải hàm mặc định của PHP hay Laravel.
                         ================================== --}}
                    <td>

                        {{ 
                            number_format(
                                (
                                    $item->product
                                        ? app(\Modules\Shop\Services\CartService::class)
                                            ->cents($item->product->price)
                                        : 0
                                ) * $item->quantity / 100,
                                2
                            )
                        }}

                    </td>

                    {{-- ==================================
                         4. XÓA MỘT SẢN PHẨM KHỎI GIỎ
                         ================================== --}}
                    <td>

                        <form
                            method="POST"
                            action="{{ route('cart.remove', $item->product_id) }}"
                        >

                            @csrf

                            {{-- Giả lập DELETE --}}
                            @method('DELETE')

                            <button class="danger">
                                Xóa
                            </button>

                        </form>

                    </td>

                </tr>

            {{-- Nếu không có sản phẩm --}}
            @empty

                <tr>
                    <td colspan="5">
                        Giỏ hàng trống.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- ============================================
         5. TÍNH TỔNG TIỀN VÀ GIẢM GIÁ
         ============================================ --}}

    <div class="panel">

        {{-- Tổng tiền trước khi giảm --}}
        {{--         
        Ví dụ giỏ hàng 1.000.000 VND, coupon WELCOME10 giảm 10%:
        Biến	          Giá trị lưu trong code	    Hiển thị
        $subtotal	          100000000	            1,000,000.00 VND
        $discount	           10000000         	100,000.00 VND
        $subtotal - $discount     90000000          	900,000.00 VND
        
                chia 100 để trở lại VND. 

        number_format($subtotal / 100, 2)
        number_format(..., 2) định dạng số với hai chữ số sau dấu thập phân.    
        --}}
        <p>
            Tạm tính:
            {{ number_format($subtotal / 100, 2) }} VND
        </p>

        {{-- Mã coupon và số tiền được giảm  |  number_format(..., 2) định dạng số với hai chữ số sau dấu thập phân
                Còn:
                    {{ $coupon?->code }}
        Dấu ?-> nghĩa là: có coupon thì lấy code, không có thì trả null, không báo lỗi vì truy cập thuộc tính trên null. 
        --}}
        <p>
            Giảm giá {{ $coupon?->code }}:
            {{ number_format($discount / 100, 2) }} VND
        </p>

        {{-- Tổng tiền sau giảm giá --}}
        <strong>
            Tổng:
            {{ number_format(($subtotal - $discount) / 100, 2) }} VND
        </strong>


        {{-- ==================================
             6. THÔNG BÁO LỖI COUPON
             ================================== --}}

        @if($couponError)

            <p class="error">
                {{ $couponError }}
                Hãy bỏ coupon hoặc áp dụng mã khác.
            </p>

        @endif


        {{-- ==================================
             7. ÁP DỤNG MÃ GIẢM GIÁ
             ================================== --}}

        <form
            method="POST"
            action="{{ route('coupon.apply') }}"
        >

            @csrf

            <label>
                Coupon

                {{-- 
                    session('coupon'):
                    Lấy mã coupon đang lưu trong session.
                --}}
                <input
                    name="code"
                    required
                    maxlength="100"
                    value="{{ session('coupon') }}"
                >
            </label>

            <button>
                Áp dụng
            </button>

        </form>


        {{-- ==================================
             8. BỎ MÃ GIẢM GIÁ
             ================================== --}}

        <form
            method="POST"
            action="{{ route('coupon.remove') }}"
        >

            @csrf

            @method('DELETE')

            <button>
                Bỏ coupon
            </button>

        </form>

    </div>


    {{-- ============================================
         9. CHECKOUT VÀ XÓA TOÀN BỘ GIỎ HÀNG
         ============================================ --}}

    {{-- Chỉ hiển thị khi giỏ hàng có ít nhất một item --}}
    @if($items->isNotEmpty())

        {{-- ==================================
             9.1 CHECKOUT
             ================================== --}}

        <form
            method="POST"
            action="{{ route('checkout') }}"
        >

            @csrf

            {{-- 
                Checkout Token:
                Lấy token đang lưu trong session.

                Token này có thể dùng để hỗ trợ
                chống gửi trùng request checkout,
                tùy logic kiểm tra ở backend.
            --}}
            <input
                type="hidden"
                name="checkout_token"
                value="{{ session('checkout_token') }}"
            >

            <button>
                Checkout
            </button>

        </form>


        {{-- ==================================
             9.2 XÓA TOÀN BỘ GIỎ HÀNG
             ================================== --}}

        <form
            method="POST"
            action="{{ route('cart.clear') }}"
        >

            @csrf

            @method('DELETE')

            <button class="danger">
                Xóa toàn bộ giỏ
            </button>

        </form>

    @endif

@endsection
