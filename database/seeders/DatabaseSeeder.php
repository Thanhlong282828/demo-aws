<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Product\Database\Seeders\MiniShopSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MiniShopSeeder::class);
        if (! app()->isProduction() || filter_var(env('DEMO_ACCOUNTS', false), FILTER_VALIDATE_BOOL)) {
            foreach (['admin' => 'ADMIN', 'user' => 'USER'] as $name => $role) {
                $user = User::firstOrCreate(['email' => $name.'@minishop.test'], ['name' => ucfirst($name), 'password' => 'DemoShop2026!']);
                $user->forceFill(['role' => $role])->save();
            }
        }
    }
}
