
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Category, view namespace category::; controller ở
    Modules/Category/app/Http/Controllers/CategoryController.php. CategoryController::show(Category
    $category) truyền category và products; products lấy qua $category->products()->paginate(10),
    chỉ thuộc danh mục đó.
--}}

{{--
    FILE: Modules/Category/resources/views/show.blade.php

    LUỒNG DỮ LIỆU:
    routes/web.php
        ↓
    CategoryController@show
        ↓
    Category Model + Product Model
        ↓
    show.blade.php
--}}

{{-- Kế thừa giao diện chung từ resources/views/layouts/app.blade.php --}}

{{--
    GIẢI THÍCH:
    Dùng layout resources/views/layouts/app.blade.php; phần content bên dưới được chèn tại
    yield("content") của layout.
--}}
@extends('layouts.app')

{{--
    $category lấy từ đâu?
    → CategoryController::show(Category $category)
    → Controller truyền sang View bằng:
      ['category' => $category]

    $category->name:
    → Lấy tên danh mục từ Model Category
    → Ví dụ: "Điện thoại"
--}}

{{--
    GIẢI THÍCH:
    Đặt tiêu đề tab trình duyệt; layout đọc bằng yield("title"). Thuộc tính sau dấu -> đọc từ record
    của model, tương ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn kho,
    email/phone/company/status là dữ liệu Customer; không lấy tên từ route.
--}}
@section('title', $category->name)

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')

    {{--
        $category được truyền từ CategoryController::show()

        $category->name:
        → Truy cập thuộc tính name của danh mục hiện tại.
    --}}
    <h1>
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route.
--}}
        Danh mục: {{ $category->name }}
    </h1>

    {{--
        $products lấy từ đâu?
        → CategoryController::show()

        'products' => $category->products()
            ->orderBy('name')
            ->paginate(10)

        $products:
        → Đối tượng phân trang chứa sản phẩm
          thuộc danh mục hiện tại.

        $products->total():
        → Tổng số sản phẩm trong danh mục,
          không chỉ số sản phẩm của trang hiện tại.

        route('categories.index'):
        → Tạo URL đến trang danh sách danh mục.
    --}}
    <p>
{{--
    GIẢI THÍCH:
    total() là tổng record khớp query ở tất cả các trang; không chỉ số record của trang hiện tại.
--}}
        {{ $products->total() }} sản phẩm ·

{{--
    GIẢI THÍCH:
    route("categories.index", model nếu có) tạo URL để mở danh sách: GET /categories → index(). Hàm
    route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model từ
    ID khi request đến.
--}}
        <a href="{{ route('categories.index') }}">
            Quay lại danh mục
        </a>
    </p>

    {{-- Bảng hiển thị sản phẩm --}}
    <div class="table-wrap">
        <table>

            {{-- Tiêu đề bảng --}}
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Giá (VND)</th>
                    <th>Tồn kho</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>

                {{--
                    @forelse:
                    → Duyệt từng sản phẩm trong $products.
                    → Nếu không có sản phẩm thì chạy @empty.

                    $products:
                    → Controller truyền sang View.

                    $product:
                    → Biến đại diện cho một sản phẩm
                      ở mỗi lần lặp.

                    Ví dụ:
                    $products = [iPhone, Samsung, Oppo]

                    Lần 1: $product = iPhone
                    Lần 2: $product = Samsung
                    Lần 3: $product = Oppo
                --}}

{{--
    GIẢI THÍCH:
    $products là danh sách view nhận từ controller. Vòng lặp tự tạo $product cho từng record hiện
    tại; ví dụ record id=2,name=Laptop thì $product->name đọc "Laptop" từ cột name. Biến số nhiều và
    số ít khác nhau.
--}}
                @forelse ($products as $product)

                <tr>

                        {{--
                            $product->name:
                            → Lấy tên sản phẩm từ Model Product.
                        --}}
                        <td>
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route.
--}}
                            {{ $product->name }}
                        </td>

                        {{--
                            $product->price:
                            → Lấy giá sản phẩm từ database.

                            (float):
                            → Ép giá trị sang số thực.

                            number_format(..., 2):
                            → Hiển thị giá với 2 chữ số thập phân.
                        --}}
                        <td>
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route. number_format(..., 2) định dạng số với 2 chữ số thập phân để hiển thị; không sửa giá
    trong database.
--}}
                            {{ number_format((float) $product->price, 2) }}
                        </td>

                        {{--
                            $product->stock:
                            → Lấy số lượng tồn kho.
                        --}}
                        <td>
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route.
--}}
                            {{ $product->stock }}
                        </td>

                        {{--
                            route('products.edit', $product):
                            → Tạo URL sửa sản phẩm.

                            $product:
                            → Laravel dùng Route Model Binding
                              để lấy route key (thường là id).

                            Ví dụ:
                            /products/5/edit
                        --}}
                        <td>
{{--
    GIẢI THÍCH:
    route("products.edit", model nếu có) tạo URL để mở form sửa: GET /products/ID/edit → edit(). Hàm
    route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model từ
    ID khi request đến.
--}}
                            <a href="{{ route('products.edit', $product) }}">
                                Sửa
        </a>
                        </td>
                </tr>

                {{--
                    @empty:
                    → Chạy khi $products không có phần tử.
                --}}

{{--
    GIẢI THÍCH:
    Nếu danh sách của forelse rỗng thì hiện hàng thông báo; không xem danh sách rỗng là lỗi DB.
--}}
                @empty

                <tr>
                        <td colspan="4">
                            Danh mục chưa có sản phẩm.
                        </td>
                </tr>

                @endforelse

            </tbody>
        </table>
    </div>

    {{--
        @include:
        → Nhúng component phân trang.

        'paginator' => $products:
        → Truyền đối tượng phân trang $products
          sang component với tên $paginator.

        Bên trong pagination.blade.php:
        $paginator->currentPage()
        $paginator->lastPage()
        $paginator->nextPageUrl()
    --}}

{{--
    GIẢI THÍCH:
    Chèn resources/views/components/pagination.blade.php; key paginator đổi tên biến truyền vào
    thành $paginator bên file đó.
--}}
    @include('components.pagination', [
        'paginator' => $products
    ])

@endsection
