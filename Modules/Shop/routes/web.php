<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\AuthController;
use Modules\Shop\Http\Controllers\CartController;
use Modules\Shop\Http\Controllers\OrderController;
use Modules\Shop\Http\Middleware\Admin;

Route::middleware('guest')->group(function () { // Nhóm trang cho người chưa đăng nhập; GET mở form, POST xử lý dữ liệu form.
    Route::view('/register', 'shop::auth', ['mode' => 'register'])->name('register'); // mode = register nghĩa là truyền biến $mode có giá trị 'register' vào giao diện $mode
    // Laravel mở auth.blade.php và truyền:  $mode = 'register';   |  Vì vậy Blade: <form method="POST" action="{{ route($mode) }}">
    // được render thành HTML thực tế:
    //                  <form method="POST" action="/register">
    //                  <!-- Họ tên, email, mật khẩu và xác nhận mật khẩu -->
    //                  <button>Gửi</button>
    //                   </form>

    // resources > views > layouts > app.blade.php
    // @else<a href="{{ route('login') }}">Đăng nhập</a><a href="{{ route('register') }}">Đăng ký</a>@endauth</nav>
    // Bấm link → mở form (Route::view).

    // [Modules/Shop/resources/views/auth.blade.php
    // Gửi form → xử lý tài khoản (Route::post).

    // Đúng về cách $mode thay đổi, nhưng ban đầu website mặc định vào /products, không phải /register.
    //    - Bấm Đăng ký → mở /register → view nhận $mode = 'register'.
    //    - Bấm Đăng nhập → mở /login → view nhận $mode = 'login'.
    // Cả hai dùng chung auth.blade.php. Khi $mode = 'login':
    //    - Tiêu đề hiện Đăng nhập.
    //    - Ẩn ô Họ tên và Xác nhận mật khẩu.
    //    - Form gửi đến POST /login.
    // Mỗi lần bấm link, Laravel render lại trang với giá trị $mode tương ứng.
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1'); // Bấm Gửi ở form register: gọi register(); throttle giới hạn 10 request/phút theo khóa của middleware.
    Route::view('/login', 'shop::auth', ['mode' => 'login'])->name('login'); // Bấm link Đăng nhập tạo GET /login; truyền $mode = 'login' vào cùng auth.blade.php.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1'); // Form method POST gửi /login: Laravel gọi AuthController@login, không mở view trực tiếp.
});
// D22: URL bạn mở chọn route trước; auth chỉ chặn/cho request đó đi tiếp, không tự chọn /profile và không chạy tất cả route.
Route::middleware('auth')->group(function () { // auth: chặn người chưa đăng nhập vào /profile.  // Vì bên trong middleware auth đã có code kiểm tra và xử lý sẵn. Dòng này bật chức năng đó cho nhóm route:
    // Nếu chưa đăng nhập, Laravel dùng đích chuyển hướng mặc định là route('login'), được cấu hình tại [ApplicationBuilder.php, dòng 291 (line 291)](/Users/lahoathanh/Desktop/xinviec/weekfofileone/vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:291).
    // Nếu đã đăng nhập, middleware cho request đi tiếp bằng $next($request) → route /profile chạy → mở view shop::profile. [Authenticate.php, dòng 59–64 (line 59)](/Users/lahoathanh/Desktop/xinviec/weekfofileone/vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:59)
    //   - authenticate(...): kiểm tra đăng nhập. Chưa đăng nhập thì chặn tại đây.
    //   - $next($request): cho request đi tiếp nếu đã vượt qua kiểm tra.
    // Còn dòng mở view là mình viết trong dự án:
    // Route::view('/profile', 'shop::profile')->name('profile'); // GET /profile đã vượt auth mới tới view Modules/Shop/resources/views/profile.blade.php.

    // Bạn bấm “Hồ sơ” → trình duyệt yêu cầu GET /profile
    //                          ↓
    // Laravel tìm thấy route /profile có middleware auth
    //                          ↓
    //                 Kiểm tra đăng nhập
    //                 ↙                 ↘
    //        Chưa đăng nhập          Đã đăng nhập
    //        Chuyển /login           Cho yêu cầu /profile đi tiếp
    //                                       ↓
    //                               Hiện shop::profile
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // Form trên menu gửi POST /logout; không dùng link GET để đăng xuất.
    Route::view('/profile', 'shop::profile')->name('profile'); // GET /profile đã vượt auth mới tới view Modules/Shop/resources/views/profile.blade.php.
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::get('/my-orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::middleware(Admin::class)->prefix('admin')->group(function () {
        Route::get('/orders', [OrderController::class, 'admin'])->name('admin.orders');
        Route::patch('/orders/{order}/status', [OrderController::class, 'status'])->name('admin.orders.status');
    });
});
