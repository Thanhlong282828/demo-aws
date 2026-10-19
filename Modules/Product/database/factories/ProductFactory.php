<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'price' => fake()->numberBetween(10000, 10000000),
            'stock' => fake()->numberBetween(0, 100),
            'description' => fake()->sentence(),
        ];
    }
}
