<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;
use Modules\Shop\Models\Order;
use Tests\TestCase;

class Week5ApiTest extends TestCase
{
    use RefreshDatabase;

    private function token(bool $admin = false): array
    {
        $user = User::factory()->create(['password' => 'Password123!']);
        if ($admin) {
            $user->forceFill(['role' => 'ADMIN'])->save();
        }

        return ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
    }

    private function payload(): array
    {
        return ['name' => 'Keyboard', 'category_id' => Category::factory()->create()->id, 'price' => 350000, 'stock' => 10];
    }

    public function test_guest_product_list_is_json_and_paginated(): void
    {
        Product::factory()->count(17)->create();
        $this->getJson('/api/products')->assertOk()->assertJsonCount(15, 'data')->assertJsonPath('meta.total', 17)->assertJsonStructure(['data' => [['id', 'name', 'category']], 'links', 'meta']);
    }

    public function test_product_detail_and_missing_status(): void
    {
        $product = Product::factory()->create();
        $this->getJson('/api/products/'.$product->id)->assertOk()->assertJsonPath('data.id', $product->id);
        $this->getJson('/api/products/999999')->assertNotFound();
    }

    public function test_guest_cannot_create(): void
    {
        $this->postJson('/api/products', $this->payload())->assertUnauthorized();
    }

    public function test_user_cannot_create_or_delete(): void
    {
        $headers = $this->token();
        $product = Product::factory()->create();
        $this->postJson('/api/products', $this->payload(), $headers)->assertForbidden();
        $this->deleteJson('/api/products/'.$product->id, [], $headers)->assertForbidden();
    }

    public function test_admin_product_crud_persists(): void
    {
        $headers = $this->token(true);
        $data = $this->payload();
        $id = $this->postJson('/api/products', $data, $headers)->assertCreated()->json('data.id');
        $data['name'] = 'Updated';
        $this->putJson('/api/products/'.$id, $data, $headers)->assertOk()->assertJsonPath('data.name', 'Updated');
        $this->assertDatabaseHas('products', ['id' => $id, 'name' => 'Updated']);
        $this->deleteJson('/api/products/'.$id, [], $headers)->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $id]);
    }

    public function test_invalid_product_has_consistent_errors(): void
    {
        $this->postJson('/api/products', ['price' => -1, 'stock' => -1], $this->token(true))->assertUnprocessable()->assertJsonValidationErrors(['name', 'category_id', 'price', 'stock'])->assertJsonPath('message', 'Validation failed.');
    }

    public function test_valid_credentials_issue_token(): void
    {
        $user = User::factory()->create(['password' => 'Password123!']);
        $response = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'Password123!'])->assertOk();
        $this->getJson('/api/orders', ['Authorization' => 'Bearer '.$response->json('data.token')])->assertOk();
    }

    public function test_wrong_password_is_401(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong'])->assertUnauthorized();
    }

    public function test_logout_revokes_token(): void
    {
        $headers = $this->token();
        $this->postJson('/api/logout', [], $headers)->assertOk();
        $this->app['auth']->forgetGuards();
        $this->getJson('/api/orders', $headers)->assertUnauthorized();
    }

    public function test_guest_orders_are_401(): void
    {
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_user_only_lists_own_orders(): void
    {
        $user = User::factory()->create();
        $own = Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create();
        $headers = ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken];
        $this->getJson('/api/orders', $headers)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $own->id);
        $this->getJson('/api/orders/'.$own->id, $headers)->assertOk()->assertJsonStructure(['data' => ['items']]);
    }

    public function test_other_users_order_is_forbidden(): void
    {
        $order = Order::factory()->create();
        $this->getJson('/api/orders/'.$order->id, $this->token())->assertForbidden();
    }

    public function test_user_cannot_access_admin_orders(): void
    {
        $headers = $this->token();
        $order = Order::factory()->create();
        $this->getJson('/api/admin/orders', $headers)->assertForbidden();
        $this->putJson('/api/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'], $headers)->assertForbidden();
    }

    public function test_admin_lists_orders_and_updates_status(): void
    {
        $headers = $this->token(true);
        $order = Order::factory()->create(['status' => 'pending']);
        $this->getJson('/api/admin/orders', $headers)->assertOk();
        $this->putJson('/api/admin/orders/'.$order->id.'/status', ['status' => 'confirmed'], $headers)->assertOk()->assertJsonPath('data.status', 'confirmed');
        $this->putJson('/api/admin/orders/'.$order->id.'/status', ['status' => 'invalid'], $headers)->assertUnprocessable();
    }

    public function test_category_crud_and_duplicate_validation(): void
    {
        $headers = $this->token(true);
        $id = $this->postJson('/api/categories', ['name' => 'Accessories'], $headers)->assertCreated()->json('data.id');
        $this->getJson('/api/categories')->assertOk();
        $this->postJson('/api/categories', ['name' => 'Accessories'], $headers)->assertUnprocessable();
        $this->putJson('/api/categories/'.$id, ['name' => 'Accessories'], $headers)->assertOk();
        $this->putJson('/api/categories/'.$id, ['name' => 'New category'], $headers)->assertOk();
        $this->deleteJson('/api/categories/'.$id, [], $headers)->assertOk();
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $product = Product::factory()->create();
        $this->deleteJson('/api/categories/'.$product->category_id, [], $this->token(true))->assertUnprocessable();
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
    }

    public function test_category_writes_require_admin(): void
    {
        $this->postJson('/api/categories', ['name' => 'Test'])->assertUnauthorized();
        $this->postJson('/api/categories', ['name' => 'Test'], $this->token())->assertForbidden();
    }

    public function test_pagination_rejects_unbounded_limit(): void
    {
        $this->getJson('/api/products?per_page=10000')->assertUnprocessable();
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('expired', ['*'], now()->subMinute())->plainTextToken;
        $this->getJson('/api/orders', ['Authorization' => 'Bearer '.$token])->assertUnauthorized();
    }

    public function test_eager_loading_reduces_category_queries(): void
    {
        Product::factory()->count(5)->create();
        DB::enableQueryLog();
        foreach (Product::get() as $product) {
            $product->category->name;
        }
        $lazy = count(DB::getQueryLog());
        DB::flushQueryLog();
        foreach (Product::with('category')->get() as $product) {
            $product->category->name;
        }
        $eager = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertSame(6, $lazy);
        $this->assertSame(2, $eager);
    }

    public function test_production_error_is_sanitized_and_logged(): void
    {
        config(['app.debug' => false]);
        Log::spy();
        Route::get('/api/test-failure', function (): void {
            throw new \RuntimeException('private diagnostic');
        });
        $this->getJson('/api/test-failure')->assertStatus(500)->assertJsonPath('message', 'Server error.')->assertDontSee('private diagnostic');
        Log::shouldHaveReceived('error')->once();
    }
}
