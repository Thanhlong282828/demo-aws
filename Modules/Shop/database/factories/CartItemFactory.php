<?php

namespace Modules\Shop\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;
use Modules\Shop\Models\CartItem;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'product_id' => Product::factory(), 'quantity' => 1];
    }
}
