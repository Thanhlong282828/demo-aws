<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;
use Modules\Shop\Models\Coupon;

class Week4Seeder extends Seeder
{
    public function run(): void
    {
        foreach ([['admin@minishop.test', 'Admin', 'ADMIN'], ['user@minishop.test', 'Khách hàng', 'USER']] as [$email,$name,$role]) {
            User::firstOrCreate(['email' => $email], ['name' => $name, 'password' => 'DemoShop123!'])->forceFill(['role' => $role])->save(); // D22/D23: dữ liệu demo local; forceFill role được gọi từ code tin cậy, không từ input register.
        }
        $category = Category::firstOrCreate(['name' => 'Phụ kiện']);
        Product::firstOrCreate(['name' => 'Keyboard'], ['category_id' => $category->id, 'price' => '350000.00', 'stock' => 10, 'description' => 'Bàn phím demo']);
        Product::firstOrCreate(['name' => 'Mouse'], ['category_id' => $category->id, 'price' => '200000.00', 'stock' => 20, 'description' => 'Chuột demo']);
        Coupon::firstOrCreate(['code' => 'WELCOME10'], ['discount_percent' => 10, 'start_date' => today()->startOfMonth(), 'end_date' => today()->endOfMonth(), 'usage_limit' => 100, 'used_count' => 0, 'active' => true]); // D28: mã demo hiệu lực tháng seed; chạy lại không reset used_count hay ngày của mã đã có.
    }
}
