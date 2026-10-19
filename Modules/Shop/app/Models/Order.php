<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Shop\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    protected $fillable = ['user_id', 'checkout_token', 'status', 'subtotal', 'discount_amount', 'total', 'coupon_code', 'discount_percent'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class); // D25/D26: một order có nhiều items; items()->create tự điền order_id.
    }
}
