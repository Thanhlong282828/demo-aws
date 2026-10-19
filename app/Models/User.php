<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])] // Cho phép create($data) gán ba trường này; không cho form tự gán role ADMIN.
#[Hidden(['password', 'remember_token'])] // Ẩn các trường này khi chuyển model thành array/JSON; đây không phải thao tác hash.
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, \Laravel\Sanctum\HasApiTokens, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array // Eloquent tự đọc cấu hình chuyển kiểu này; controller không cần gọi $user->casts().
    {
        return [
            'email_verified_at' => 'datetime', // Đọc ngày xác minh email dưới dạng đối tượng ngày giờ; D22 chưa triển khai xác minh email.
            'password' => 'hashed', // Khi User::create($data) gán password, Laravel hash trước khi lưu; không lưu mật khẩu gốc.
        ];
    }
}
