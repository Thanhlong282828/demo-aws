<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Product\Models\Product;
use Modules\Shop\Database\Factories\CartItemFactory;

class CartItem extends Model
{
    use HasFactory;

    protected static function newFactory(): CartItemFactory
    {
        return CartItemFactory::new();
    }

    protected $fillable = ['user_id', 'product_id', 'quantity'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class); // D24: cart_items.product_id liên kết products.id; $item->product có thể null nếu product đã xóa.
    }
}
