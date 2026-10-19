
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    Layout chung: các trang extends("layouts.app"); yield(title/content) nhận phần section từ trang
    con. CSS chỉ tạo màu/bố cục, không lấy dữ liệu DB.
--}}
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Mini Shop')</title>
<style>
*{box-sizing:border-box}body{font:16px/1.5 system-ui,sans-serif;margin:0;color:#17243b;background:#f5f7fb}header{background:#17243b;color:white;padding:20px 5%}nav{display:flex;gap:24px;align-items:center}nav a{color:white}main{max-width:1120px;margin:32px auto;padding:0 20px}a{color:#245bb5}h1{font-size:28px}.panel,form.filters{background:white;padding:20px;border:1px solid #dce3ee;border-radius:10px;margin:20px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px}label{display:block;font-weight:600;margin-bottom:12px}input,select,textarea{display:block;width:100%;padding:10px;border:1px solid #aab8cd;border-radius:5px;font:inherit;margin-top:5px}button,.button{display:inline-block;background:#245bb5;color:white;border:0;padding:9px 14px;border-radius:5px;font:inherit;cursor:pointer;text-decoration:none}.danger{background:#b52338}.muted{color:#5f6d82}.error{color:#a51c30}.alert{padding:14px;border-radius:6px;background:#e6f5e9;margin:16px 0}.alert.error{background:#ffe9ed}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;background:white}th,td{text-align:left;padding:12px;border-bottom:1px solid #dce3ee;vertical-align:middle}th{background:#edf1f7}.actions{display:flex;gap:10px;align-items:center}.actions form{margin:0}.pagination{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:20px 0}.pagination span,.pagination a{padding:6px 10px;border:1px solid #dce3ee;background:white;border-radius:4px}.pagination .current{background:#17243b;color:white}.form-page{max-width:650px}small.error{display:block;margin-top:4px}textarea{min-height:100px}.toolbar{display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}
</style>
</head>
<body>
<header>
<nav style="flex-wrap:wrap">
<strong>MINI SHOP · WEEK 5</strong>
<a href="{{ route('products.index') }}">Sản phẩm</a>
@auth {{-- Blade chỉ hiện nhóm này khi đã đăng nhập; ẩn/hiện menu không thay middleware bảo vệ route. --}}
<a href="{{ route('profile') }}">Hồ sơ</a> {{-- Link tạo GET /profile; middleware auth kiểm tra rồi mở shop::profile. --}}
<a href="{{ route('cart.index') }}">Giỏ hàng</a>
<a href="{{ route('orders.index') }}">Đơn của tôi</a>
@if(auth()->user()->role === 'ADMIN')<a href="{{ route('categories.index') }}">Danh mục</a><a href="{{ route('admin.orders') }}">Quản lý đơn</a>@endif
<form method="POST" action="{{ route('logout') }}">@csrf<button>Đăng xuất</button></form> {{-- Gửi POST /logout tới AuthController@logout. --}}
@else {{-- Chưa đăng nhập: hiện hai link GET mở trang có form. --}}
<a href="{{ route('login') }}">Đăng nhập</a> {{-- GET /login -> Shop/routes/web.php -> auth.blade.php nhận $mode = 'login'. --}}
<a href="{{ route('register') }}">Đăng ký</a> {{-- GET /register -> cùng view auth.blade.php nhận $mode = 'register'. --}}
@endauth
</nav>
</header>
<main>

{{--
    GIẢI THÍCH:
    Thông báo do controller redirect()->with("success", ...); session giữ tạm qua redirect.
--}}
@if(session('success'))<div class="alert" role="status">{{ session('success') }}</div>
@endif

{{--
    GIẢI THÍCH:
    Thông báo chặn thao tác do controller with("error", ...), ví dụ category có sản phẩm không được
    xóa.
--}}
@if(session('error'))<div class="alert error" role="alert">{{ session('error') }}</div>
@endif

{{--
    GIẢI THÍCH:
    $errors do middleware web của Laravel chia sẻ từ lỗi validation trong session; any() hỏi có lỗi
    không.
--}}
@if($errors->any())<div class="alert error" role="alert">
<strong>Vui lòng kiểm tra dữ liệu:</strong>
<ul>

{{--
    GIẢI THÍCH:
    $error là từng chuỗi thông báo trong vòng foreach($errors->all() as $error); biến được tạo ngay
    tại vòng lặp.
--}}
@foreach($errors->all() as $error)<li>{{ $error }}</li>

@endforeach</ul>
</div>
@endif
@yield('content')
</main>
</body>
</html>
