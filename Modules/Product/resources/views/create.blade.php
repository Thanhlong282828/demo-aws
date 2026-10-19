
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Product, view namespace product::; controller ở
    Modules/Product/app/Http/Controllers/ProductController.php. create() trả view này. Form tạo mới
    chưa có $product từ DB.
--}}

{{--
    GIẢI THÍCH:
    Dùng layout resources/views/layouts/app.blade.php; phần content bên dưới được chèn tại
    yield("content") của layout.
--}}
@extends('layouts.app')

{{--
    GIẢI THÍCH:
    Đặt tiêu đề tab trình duyệt; layout đọc bằng yield("title").
--}}
@section('title', 'Tạo sản phẩm')

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')
<h1>Tạo sản phẩm</h1>
{{--
    GIẢI THÍCH:
    route("products.store", model nếu có) tạo URL để nhận dữ liệu tạo mới khi submit: POST /products
    → store(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID;
    router tải model từ ID khi request đến.
--}}
<form class="panel form-page" method="POST" action="{{ route('products.store') }}">
{{--
    GIẢI THÍCH:
    Chèn Modules/Product/resources/views/_form.blade.php vào đây; chính partial đó chứa input.
    action của form chỉ là nơi nhận dữ liệu sau khi bấm Lưu.
--}}
@include('product::_form')</form>

@endsection
