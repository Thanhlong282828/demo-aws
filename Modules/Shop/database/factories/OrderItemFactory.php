<?php

namespace Modules\Shop\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Product\Models\Product;
use Modules\Shop\Models\Order;
use Modules\Shop\Models\OrderItem;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return ['order_id' => Order::factory(), 'product_id' => Product::factory(), 'product_name' => 'Demo item', 'quantity' => 1, 'unit_price' => 10000];
    }
}
