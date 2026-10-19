@extends('layouts.app') {{-- Route /profile chỉ render view này sau khi auth xác nhận đã đăng nhập. --}}
@section('content')<h1>Hồ sơ</h1><div class="panel">{{ auth()->user()->name }} {{-- auth()->user() lấy user hiện tại đã đăng nhập; không phải tự tạo user mới. --}} · {{ auth()->user()->email }} · {{ auth()->user()->role }}</div>@endsection
