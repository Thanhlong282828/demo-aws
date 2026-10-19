
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    Layout component mẫu do thư viện module sinh. CRUD hiện tại dùng layouts.app, không dùng file
    mẫu này. $slot là nội dung giữa thẻ component; description/keywords/author là biến tùy chọn,
    không phải cột DB.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
{{--
    GIẢI THÍCH:
    config() đọc cấu hình Laravel/module, không truy vấn bảng categories/products/customers.
--}}
        <title>Product Module - {{ config('app.name', 'Laravel') }}</title>
        <meta name="description" content="{{ $description ?? '' }}">
        <meta name="keywords" content="{{ $keywords ?? '' }}">
        <meta name="author" content="{{ $author ?? '' }}">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Vite CSS --}}
        {{-- {{ module_vite('build-product', 'resources/assets/sass/app.scss') }} --}}
    </head>
    <body>
{{--
    GIẢI THÍCH:
    $slot do Blade component cung cấp: nội dung được viết giữa thẻ mở và đóng component.
--}}
        {{ $slot }}

        {{-- Vite JS --}}
        {{-- {{ module_vite('build-product', 'resources/assets/js/app.js') }} --}}
    </body>
</html>
