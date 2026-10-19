
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Category, view namespace category::; controller ở
    Modules/Category/app/Http/Controllers/CategoryController.php. edit(Category $category) nhận
    model theo ID trên URL rồi truyền qua view; compact('category') tương đương ['category' =>
    $category].
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
@section('title', 'Sửa danh mục')

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')
<h1>Sửa danh mục</h1>
{{--
    GIẢI THÍCH:
    route("categories.update", model nếu có) tạo URL để nhận dữ liệu cập nhật khi bấm Lưu: PUT
    /categories/ID → update(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào
    URL dùng ID; router tải model từ ID khi request đến.
--}}
<form class="panel form-page" method="POST" action="{{ route('categories.update', $category) }}">
{{--
    GIẢI THÍCH:
    HTML form vẫn method POST; input ẩn _method=PUT giúp router gọi action update khi bấm Lưu.
--}}
@method('PUT')
{{--
    GIẢI THÍCH:
    Chèn Modules/Category/resources/views/_form.blade.php vào đây; chính partial đó chứa input.
    action của form chỉ là nơi nhận dữ liệu sau khi bấm Lưu.
--}}
@include('category::_form')</form>

@endsection
{{--
 Ô input được lấy từ dòng này:
       @include('category::_form')
Nó chèn nội dung file:
       Modules/Category/resources/views/_form.blade.php
--}}
