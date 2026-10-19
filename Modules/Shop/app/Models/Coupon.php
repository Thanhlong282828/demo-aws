<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shop\Database\Factories\CouponFactory;

class Coupon extends Model
{
    use HasFactory;

    protected static function newFactory(): CouponFactory
    {
        return CouponFactory::new();
    }

    protected $fillable = ['code', 'discount_percent', 'start_date', 'end_date', 'usage_limit', 'used_count', 'active'];

    protected function casts(): array
    {
        return ['start_date' => 'immutable_date', 'end_date' => 'immutable_date', 'active' => 'boolean']; // D28: Eloquent tự chuyển ngày và bool; immutable_date không bị sửa trực tiếp khi cộng/trừ ngày.
    }
}
