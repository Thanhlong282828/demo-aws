
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Category, view namespace category::; controller ở
    Modules/Category/app/Http/Controllers/CategoryController.php. create() trả view này. Form tạo
    mới chưa có $category từ DB.
--}}

{{--
    FILE: Modules/Category/resources/views/create.blade.php

    CHỨC NĂNG:
    → Hiển thị trang tạo danh mục mới.
    → Gửi dữ liệu tên danh mục lên CategoryController::store().
--}}

{{--
    @extends('layouts.app'):
    → Kế thừa giao diện chính của ứng dụng.

    Laravel tìm layout tại:
    resources/views/layouts/app.blade.php
--}}

{{--
    GIẢI THÍCH:
    Dùng layout resources/views/layouts/app.blade.php; phần content bên dưới được chèn tại
    yield("content") của layout.
--}}
@extends('layouts.app')

{{--
    @section('title', 'Tạo danh mục'):
    → Truyền tiêu đề "Tạo danh mục"
      vào section title của layout chính.
--}}

{{--
    GIẢI THÍCH:
    Đặt tiêu đề tab trình duyệt; layout đọc bằng yield("title").
--}}
@section('title', 'Tạo danh mục')

{{--
    @section('content'):
    → Bắt đầu nội dung trang.
    → Nội dung được đưa vào @yield('content')
      trong layouts/app.blade.php.
--}}

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')

    {{-- Tiêu đề hiển thị trên trang --}}
    <h1>
        Tạo danh mục
    </h1>

    {{--
        <form>:
        → Tạo biểu mẫu nhập dữ liệu.

        class="panel form-page":
        → Các class CSS dùng để định dạng giao diện.

        method="POST":
        → Gửi dữ liệu bằng HTTP POST.

        action="{{ route('categories.store') }}":
        → Laravel tạo URL từ route có tên categories.store.

        → Route này thường gọi:
          CategoryController::store()

        Ví dụ URL:
        POST /categories
    --}}
    <form
        class="panel form-page"
        method="POST"
{{--
    GIẢI THÍCH:
    route("categories.store", model nếu có) tạo URL để nhận dữ liệu tạo mới khi submit: POST
    /categories → store(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL
    dùng ID; router tải model từ ID khi request đến.
--}}
        action="{{ route('categories.store') }}"
    >

        {{--
            @include('category::_form'):
            → Nhúng file Blade dùng chung cho Form.

            Đường dẫn trong module:
            Modules/Category/resources/views/_form.blade.php

            File _form.blade.php chứa:
            → @csrf
            → Input name
            → old('name')
            → @error('name')
            → Nút lưu danh mục
            → Link quay lại

            category:::
            → Namespace View của Module Category.

            _form:
            → Tên file _form.blade.php.
        --}}

{{--
    GIẢI THÍCH:
    Chèn Modules/Category/resources/views/_form.blade.php vào đây; chính partial đó chứa input.
    action của form chỉ là nơi nhận dữ liệu sau khi bấm Lưu.
--}}
        @include('category::_form')

    </form>

@endsection
