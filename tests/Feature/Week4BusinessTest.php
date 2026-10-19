<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;
use Modules\Shop\Models\Coupon;
use Modules\Shop\Models\Order;
use Modules\Shop\Models\OrderItem;
use Modules\Shop\Services\CartService;
use Modules\Shop\Services\CheckoutService;
use Tests\TestCase;

class Week4BusinessTest extends TestCase
{
    use RefreshDatabase; // D27: test dùng database test riêng; transaction/migration của test không được trỏ vào dữ liệu thật.

    private function product(int $stock = 10, string $price = '350000.00'): Product
    {
        return Product::factory()->create(['stock' => $stock, 'price' => $price]);
    }

    private function user(bool $admin = false): User
    {
        $u = User::factory()->create();
        if ($admin) {
            $u->forceFill(['role' => 'ADMIN'])->save();
        }

        return $u;
    }

    private function buy(User $u, Product $p, int $qty = 1, ?string $token = null, ?string $coupon = null): Order
    {
        app(CartService::class)->change($u, $p->id, $qty);

        return app(CheckoutService::class)->checkout($u, $token ?? (string) Str::uuid(), $coupon);
    }

    private function coupon(array $overrides = []): Coupon
    {
        return Coupon::factory()->create(array_merge(['code' => 'WELCOME10', 'discount_percent' => 10, 'start_date' => today()->subDay(), 'end_date' => today()->addDay(), 'usage_limit' => 10, 'used_count' => 0, 'active' => true], $overrides));
    }

    public function test_valid_registration_hashes_password_and_cannot_set_admin(): void
    {
        $this->post('/register', ['name' => 'New', 'email' => 'new@example.com', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'ADMIN'])->assertRedirect('/products');
        $u = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Password123!', $u->password));
        $this->assertSame('USER', $u->role);
        $this->assertAuthenticatedAs($u);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $u = $this->user();
        $this->post('/register', ['name' => 'New', 'email' => $u->email, 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])->assertSessionHasErrors('email');
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->post('/register', ['name' => 'New', 'email' => 'bad', 'password' => 'Password123!', 'password_confirmation' => 'Password123!'])->assertSessionHasErrors('email');
    }

    public function test_password_confirmation_is_required(): void
    {
        $this->post('/register', ['name' => 'New', 'email' => 'new@example.com', 'password' => 'Password123!', 'password_confirmation' => 'wrong'])->assertSessionHasErrors('password');
    }

    public function test_correct_login(): void
    {
        $u = User::factory()->create(['password' => 'Password123!']);
        $this->post('/login', ['email' => $u->email, 'password' => 'Password123!'])->assertRedirect('/products');
        $this->assertAuthenticatedAs($u);
    }

    public function test_incorrect_login(): void
    {
        $u = $this->user();
        $this->post('/login', ['email' => $u->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_profile_redirects(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_authenticated_profile(): void
    {
        $this->actingAs($this->user())->get('/profile')->assertOk();
    }

    public function test_logout_protects_profile(): void
    {
        $this->actingAs($this->user())->post('/logout')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_guest_can_browse(): void
    {
        $this->product();
        $this->get('/products')->assertOk();
    }

    public function test_user_cannot_access_admin_or_product_mutations(): void
    {
        $u = $this->user();
        $p = $this->product();
        $this->actingAs($u)->get('/products/create')->assertForbidden();
        $this->post('/products', [])->assertForbidden();
        $this->patch('/products/'.$p->id, [])->assertForbidden();
        $this->delete('/products/'.$p->id)->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->post('/categories', [])->assertForbidden();
    }

    public function test_admin_can_access_crud(): void
    {
        $this->actingAs($this->user(true))->get('/products/create')->assertOk();
        $this->get('/categories/create')->assertOk();
        $this->get('/admin/orders')->assertOk();
    }

    public function test_cart_add_increments_without_duplicate_rows(): void
    {
        $u = $this->user();
        $p = $this->product();
        $this->actingAs($u)->post('/cart/items', ['product_id' => $p->id])->assertRedirect('/cart');
        $this->post('/cart/items', ['product_id' => $p->id])->assertRedirect('/cart');
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['quantity' => 2]);
    }

    public function test_update_and_multiple_product_total(): void
    {
        $u = $this->user();
        $a = $this->product();
        $b = $this->product(5, '200000.00');
        $cart = app(CartService::class);
        $cart->change($u, $a->id, 2);
        $cart->change($u, $b->id, 1);
        $this->assertSame(90000000, $cart->subtotal($cart->items($u)));
        $this->actingAs($u)->patch('/cart/items/'.$a->id, ['quantity' => 3])->assertRedirect();
        $this->assertSame(125000000, $cart->subtotal($cart->items($u)));
        $this->get('/cart')->assertOk()->assertSee('1,250,000.00');
    }

    public function test_invalid_quantity_zero(): void
    {
        $this->invalidQuantity(0);
    }

    public function test_invalid_quantity_negative(): void
    {
        $this->invalidQuantity(-5);
    }

    public function test_invalid_quantity_above_stock(): void
    {
        $this->invalidQuantity(11);
    }

    private function invalidQuantity(int $qty): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->post('/cart/items', ['product_id' => $p->id, 'quantity' => $qty])->assertSessionHasErrors();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_remove_and_clear_own_cart_only(): void
    {
        $u = $this->user();
        $other = $this->user();
        $p = $this->product();
        $cart = app(CartService::class);
        $cart->change($u, $p->id, 1);
        $cart->change($other, $p->id, 1);
        $this->actingAs($u)->delete('/cart/items/'.$p->id)->assertRedirect();
        $this->assertDatabaseCount('cart_items', 1);
        $cart->change($u, $p->id, 1);
        $this->delete('/cart')->assertRedirect();
        $this->assertDatabaseHas('cart_items', ['user_id' => $other->id]);
        $this->assertDatabaseMissing('cart_items', ['user_id' => $u->id]);
    }

    public function test_empty_checkout_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->checkout($this->user(), (string) Str::uuid());
    }

    public function test_checkout_snapshot_stock_and_cart(): void
    {
        $u = $this->user();
        $p = $this->product();
        $order = $this->buy($u, $p, 2);
        $this->assertSame(70000000, $order->total);
        $this->assertSame(8, $p->fresh()->stock);
        $this->assertDatabaseCount('cart_items', 0);
        $p->update(['price' => '1.00', 'name' => 'Changed']);
        $this->assertSame(35000000, $order->items->first()->unit_price);
        $this->assertNotSame('Changed', $order->items->first()->product_name);
        $this->actingAs($u)->get('/my-orders/'.$order->id)->assertOk()->assertSee('700,000.00');
        $this->get('/my-orders')->assertOk();
    }

    public function test_exact_stock_checkout(): void
    {
        $p = $this->product(2);
        $this->buy($this->user(), $p, 2);
        $this->assertSame(0, $p->fresh()->stock);
    }

    public function test_stock_changed_after_add_rejects(): void
    {
        $u = $this->user();
        $p = $this->product();
        app(CartService::class)->change($u, $p->id, 2);
        $p->update(['stock' => 1]);
        try {
            app(CheckoutService::class)->checkout($u, (string) Str::uuid());
            $this->fail('Expected stock rejection');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('cart_items', 1);
        }
    }

    public function test_product_deleted_after_add_rejects(): void
    {
        $u = $this->user();
        $p = $this->product();
        app(CartService::class)->change($u, $p->id, 1);
        $p->delete();
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->checkout($u, (string) Str::uuid());
    }

    public function test_checkout_uses_current_price(): void
    {
        $u = $this->user();
        $p = $this->product();
        app(CartService::class)->change($u, $p->id, 1);
        $p->update(['price' => '100000.00']);
        $o = app(CheckoutService::class)->checkout($u, (string) Str::uuid());
        $this->assertSame(10000000, $o->total);
    }

    public function test_duplicate_checkout_returns_same_order(): void // D27: mô phỏng retry cùng token; phải trả cùng order, chỉ trừ stock một lần.
    {
        $u = $this->user();
        $p = $this->product();
        $token = (string) Str::uuid();
        $a = $this->buy($u, $p, 1, $token);
        $b = app(CheckoutService::class)->checkout($u, $token);
        $this->assertSame($a->id, $b->id);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(9, $p->fresh()->stock);
    }

    public function test_http_double_checkout_and_token_validation(): void // D27: kiểm HTTP replay và token sai; khác với chỉ gọi service trực tiếp.
    {
        $u = $this->user();
        $p = $this->product();
        app(CartService::class)->change($u, $p->id, 1);
        $token = (string) Str::uuid();
        $this->actingAs($u)->withSession(['checkout_token' => $token])->post('/checkout', ['checkout_token' => $token])->assertRedirect();
        $this->post('/checkout', ['checkout_token' => $token])->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->post('/checkout', ['checkout_token' => (string) Str::uuid()])->assertStatus(419);
    }

    public function test_owner_protection(): void
    {
        $owner = $this->user();
        $o = $this->buy($owner, $this->product());
        $this->actingAs($this->user())->get('/my-orders/'.$o->id)->assertForbidden();
        $this->get('/my-orders')->assertDontSee('/my-orders/'.$o->id, false);
    }

    public function test_valid_status_and_terminal_rejection(): void
    {
        $o = $this->buy($this->user(), $this->product());
        $s = app(CheckoutService::class);
        $s->transition($o, 'confirmed');
        $s->transition($o, 'completed');
        $this->expectException(ValidationException::class);
        $s->transition($o, 'pending');
    }

    public function test_cancellation_restocks_once_and_is_terminal(): void
    {
        $p = $this->product();
        $o = $this->buy($this->user(), $p, 2);
        $s = app(CheckoutService::class);
        $s->transition($o, 'cancelled');
        $this->assertSame(10, $p->fresh()->stock);
        try {
            $s->transition($o, 'completed');
            $this->fail('Expected terminal rejection');
        } catch (ValidationException $e) {
            $this->assertSame(10, $p->fresh()->stock);
        }
    }

    public function test_nonadmin_cannot_change_status(): void
    {
        $u = $this->user();
        $o = $this->buy($u, $this->product());
        $this->actingAs($u)->patch('/admin/orders/'.$o->id.'/status', ['status' => 'confirmed'])->assertForbidden();
    }

    public function test_admin_can_change_status(): void
    {
        $o = $this->buy($this->user(), $this->product());
        $this->actingAs($this->user(true))->patch('/admin/orders/'.$o->id.'/status', ['status' => 'confirmed'])->assertRedirect();
        $this->assertSame('confirmed', $o->fresh()->status);
    }

    public function test_midcheckout_failure_rolls_everything_back(): void // D25/D27: tạo lỗi giữa checkout để chứng minh rollback cả order/items/stock/cart/coupon.
    {
        $u = $this->user();
        $a = $this->product();
        $b = $this->product();
        $c = $this->coupon();
        $cart = app(CartService::class);
        $cart->change($u, $a->id, 1);
        $cart->change($u, $b->id, 1);
        $count = 0;
        OrderItem::creating(function () use (&$count) {
            if (++$count === 2) {
                throw new \RuntimeException('Simulated item failure');
            }
        });
        try {
            app(CheckoutService::class)->checkout($u, (string) Str::uuid(), $c->code);
            $this->fail('Expected rollback');
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulated item failure', $e->getMessage());
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('order_items', 0);
            $this->assertDatabaseCount('cart_items', 2);
            $this->assertSame(10, $a->fresh()->stock);
            $this->assertSame(10, $b->fresh()->stock);
            $this->assertSame(0, $c->fresh()->used_count);
        } finally {
            OrderItem::flushEventListeners();
        }
    }

    public function test_coupon_discount_snapshot_and_count(): void // D28: kiểm số giảm, tổng sau giảm, lượt dùng và lịch sử không đổi khi coupon hiện tại đổi.
    {
        $u = $this->user();
        $c = $this->coupon();
        $o = $this->buy($u, $this->product(), 2, null, $c->code);
        $this->assertSame(7000000, $o->discount_amount);
        $this->assertSame(63000000, $o->total);
        $this->assertSame(1, $c->fresh()->used_count);
        $c->update(['discount_percent' => 90]);
        $this->assertSame(10, $o->fresh()->discount_percent);
    }

    public function test_missing_coupon_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->coupon('MISSING');
    }

    public function test_inactive_coupon_rejected(): void
    {
        $this->coupon(['active' => false]);
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->coupon('WELCOME10');
    }

    public function test_future_coupon_rejected(): void
    {
        $this->coupon(['start_date' => today()->addDay()]);
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->coupon('WELCOME10');
    }

    public function test_expired_coupon_rejected(): void
    {
        $this->coupon(['end_date' => today()->subDay()]);
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->coupon('WELCOME10');
    }

    public function test_exhausted_coupon_rejected(): void
    {
        $this->coupon(['used_count' => 10]);
        $this->expectException(ValidationException::class);
        app(CheckoutService::class)->coupon('WELCOME10');
    }

    public function test_coupon_revalidated_at_checkout(): void
    {
        $u = $this->user();
        $p = $this->product();
        $c = $this->coupon();
        app(CartService::class)->change($u, $p->id, 1);
        app(CheckoutService::class)->coupon($c->code);
        $c->update(['active' => false]);
        try {
            app(CheckoutService::class)->checkout($u, (string) Str::uuid(), $c->code);
            $this->fail('Expected rejection');
        } catch (ValidationException $e) {
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('cart_items', 1);
            $this->assertSame(0, $c->fresh()->used_count);
        }
    }

    public function test_coupon_apply_and_remove_http(): void
    {
        $this->coupon();
        $this->actingAs($this->user())->post('/cart/coupon', ['code' => 'welcome10'])->assertSessionHas('coupon', 'WELCOME10');
        $this->get('/cart')->assertOk();
        $this->delete('/cart/coupon')->assertSessionMissing('coupon');
    }
}
