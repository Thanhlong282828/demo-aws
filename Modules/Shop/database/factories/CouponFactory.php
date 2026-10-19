<?php

namespace Modules\Shop\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Shop\Models\Coupon;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return ['code' => strtoupper(fake()->unique()->bothify('SAVE####??')), 'discount_percent' => 10, 'start_date' => today()->subDay(), 'end_date' => today()->addMonth(), 'usage_limit' => 100, 'used_count' => 0, 'active' => true];
    }
}
