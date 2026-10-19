
@extends('layouts.app') {{-- Dùng khung/menu resources/views/layouts/app.blade.php. --}}

@section('content') {{-- Phần form này được chèn vào @yield('content') của layout; thay nội dung trang sản phẩm. --}}
    <h1>
        {{ $mode === 'register' ? 'Đăng ký' : 'Đăng nhập' }} {{-- $mode do Route::view truyền vào; chỉ đổi tiêu đề, không phải chữ trên nút. --}}
    </h1>

    <form
        method="POST" {{-- Bấm nút Gửi tạo request POST để controller xử lý dữ liệu. --}}
        action="{{ route($mode) }}" {{-- register => URL /register; login => URL /login; route() tìm theo tên đặt bằng ->name(). --}}
        class="panel form-page"
    >
        @csrf {{-- Sinh input ẩn CSRF token để Laravel kiểm tra form; không phải mật khẩu hay session ID. --}}

        @if ($mode === 'register') {{-- Chỉ đăng ký mới cần họ tên; /login render lại view với mode login nên ẩn ô này. --}}
            <label>
                Họ tên
                <input
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                >
            </label>
        @endif

        <label>
            Email
            <input
                type="email"
                name="email"
                value="{{ old('email') }}" {{-- Điền lại email sau validation lỗi; không điền lại mật khẩu. --}}
                required
            >
        </label>

        <label>
            Mật khẩu
            <input
                type="password"
                name="password"
                required
                autocomplete="{{ $mode === 'register' ? 'new-password' : 'current-password' }}"
            >
        </label>

        @if ($mode === 'register')
            <label>
                Xác nhận mật khẩu
                <input
                    type="password"
                    name="password_confirmation" {{-- Rule confirmed kiểm ô này có trùng password không. --}}
                    required
                >
            </label>
        @endif

        <button>Gửi</button> {{-- button trong form mặc định submit: gửi tới action của form, không cần href; chữ Gửi giữ nguyên. --}}
    </form>
@endsection
