
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Product, view namespace product::; controller ở
    Modules/Product/app/Http/Controllers/ProductController.php. edit(Product $product) nhận model
    theo ID trên URL rồi truyền qua view; compact('product') tương đương ['product' => $product].
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
@section('title', 'Sửa sản phẩm')

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route.
--}}
<h1>Sửa sản phẩm #{{ $product->id }}</h1>
{{--
    GIẢI THÍCH:
    route("products.update", model nếu có) tạo URL để nhận dữ liệu cập nhật khi bấm Lưu: PUT
    /products/ID → update(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL
    dùng ID; router tải model từ ID khi request đến.
--}}
<form class="panel form-page" method="POST" action="{{ route('products.update', $product) }}">
{{--
    GIẢI THÍCH:
    HTML form vẫn method POST; input ẩn _method=PUT giúp router gọi action update khi bấm Lưu.
--}}
@method('PUT')
{{--
    GIẢI THÍCH:
    Chèn Modules/Product/resources/views/_form.blade.php vào đây; chính partial đó chứa input.
    action của form chỉ là nơi nhận dữ liệu sau khi bấm Lưu.
--}}
@include('product::_form')</form>

@endsection
