<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

class MiniShopSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Accessory', 'Laptop', 'Phone'] as $name) {
            $category = Category::firstOrCreate(['name' => $name]);
            for ($index = 1; $index <= 20; $index++) {
                $productName = ($name === 'Accessory' ? 'Keyboard' : $name).' Demo '.str_pad((string) $index, 2, '0', STR_PAD_LEFT);
                Product::firstOrCreate(['name' => $productName, 'category_id' => $category->id], [
                    'price' => $name === 'Accessory' ? 100000 + $index * 25000 : 2000000 + $index * 100000,
                    'stock' => $index,
                    'description' => 'Dữ liệu mẫu Week 3 để thực hành CRUD và bộ lọc.',
                ]);
            }
        }
    }
}
