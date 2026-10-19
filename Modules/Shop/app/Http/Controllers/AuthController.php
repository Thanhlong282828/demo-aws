<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): RedirectResponse // POST /register gọi hàm này; $request chứa dữ liệu form vừa gửi.
    {
        $data = $request->validate([
            'name' => 'required|string|max:255', // Tên bắt buộc, là chuỗi và dài tối đa 255 ký tự.
            'email' => 'required|email|max:255|unique:users,email', // Email đúng định dạng và chưa có trong cột email của bảng users.
            'password' => ['required', 'confirmed', Password::min(8)], // Ít nhất 8 ký tự; confirmed so với ô password_confirmation.
        ]); // Sai rule: Laravel trả lỗi về form; đúng: $data nhận các trường đã validate.
        $user = User::create($data); // Lưu user vào MySQL; cast password => hashed trong User tự hash trước khi lưu.
        Auth::login($user); // Đăng nhập ngay tài khoản vừa tạo; không yêu cầu nhập lại password. User vẫn còn trong database khi logout.
        $request->session()->regenerate(); // Đổi session ID, giữ dữ liệu session; giúp chống lợi dụng session ID cũ.

        return redirect()->route('products.index'); // Trình duyệt mở GET /products, rồi ProductController@index hiển thị sản phẩm.
    }

    public function login(Request $request): RedirectResponse // POST /login gọi hàm này khi bấm Gửi ở form đăng nhập.
    {
        $data = $request->validate([
            'email' => 'required|email', // Kiểm định dạng đầu vào; chưa xác nhận email này có tài khoản hay chưa.
            'password' => 'required|string', // Mật khẩu người dùng vừa nhập; không tự hash nó rồi so sánh chuỗi.
        ]);
        if (! Auth::attempt($data)) { // Laravel tìm user theo email, kiểm password với hash đã lưu; đúng thì đăng nhập, sai trả false.
            throw ValidationException::withMessages(['email' => 'Email hoặc mật khẩu không đúng.']); // Hiện lỗi tại form; không chạy các dòng phía dưới.
        }
        $request->session()->regenerate(); // Đăng nhập thành công: đổi session ID nhưng vẫn giữ trạng thái đăng nhập.

        return redirect()->intended(route('products.index')); // Về URL được yêu cầu trước khi bị auth chặn, ví dụ /profile; nếu không có thì /products.
    }

    public function logout(Request $request): RedirectResponse // Form Đăng xuất trên menu gửi POST /logout tới đây.
    {
        Auth::logout(); // Bỏ trạng thái đăng nhập; dòng này không tự làm thay hai thao tác session phía dưới.
        $request->session()->invalidate(); // Xóa dữ liệu session và đổi session ID; cart lưu database nên vẫn còn.
        $request->session()->regenerateToken(); // Tạo CSRF token mới cho các form sau logout; CSRF token khác session ID.

        return redirect()->route('login'); // Mở GET /login để hiện form. Mở lại /profile sẽ bị middleware auth chặn.
    }
}
