<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Shop\Database\Factories\OrderItemFactory;

class OrderItem extends Model
{
    use HasFactory;

    protected static function newFactory(): OrderItemFactory
    {
        return OrderItemFactory::new();
    }

    protected $fillable = ['order_id', 'product_id', 'product_name', 'quantity', 'unit_price']; // D25: product_name/unit_price là bản chụp lúc mua, giữ lịch sử kể cả product đổi tên/giá hoặc xóa.
}
