<?php

namespace Modules\Shop\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Shop\Models\Order;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return ['user_id' => User::factory(), 'checkout_token' => (string) Str::uuid(), 'status' => 'pending', 'subtotal' => 10000, 'discount_amount' => 0, 'total' => 10000];
    }
}
