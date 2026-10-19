<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Category\Models\Category;
use Modules\Product\Database\Seeders\MiniShopSeeder;
use Modules\Product\Models\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MiniShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::factory()->create();
        $admin->forceFill(['role' => 'ADMIN'])->save();
        $this->actingAs($admin);
    }

    public function test_product_crud_and_relationships(): void
    {
        $category = Category::factory()->create(['name' => 'Accessory']);
        $data = ['name' => 'Keyboard ABC', 'category_id' => $category->id, 'price' => '125000.50', 'stock' => 3, 'description' => 'Test'];
        $this->get(route('products.create'))->assertOk()->assertSee('Accessory');
        $this->post(route('products.store'), $data)->assertRedirect(route('products.index'))->assertSessionHasNoErrors();
        $product = Product::sole();
        $this->assertSame('Accessory', $product->category->name);
        $this->assertSame($product->id, $category->products()->sole()->id);
        $this->get(route('products.index'))->assertOk()->assertSee('Keyboard ABC')->assertSee('Accessory');
        $this->get(route('products.edit', $product))->assertOk()->assertSee('Keyboard ABC')->assertSee('125000.50');
        $this->put(route('products.update', $product), [...$data, 'name' => 'Updated', 'stock' => 0])->assertSessionHasNoErrors()->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Updated', 'stock' => 0]);
        $this->delete(route('products.destroy', $product))->assertRedirect(route('products.index'));
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public static function invalidProductInputs(): array
    {
        return [
            'empty name' => ['name', ''],
            'negative price' => ['price', -100],
            'text price' => ['price', 'abc'],
            'negative stock' => ['stock', -1],
            'fractional stock' => ['stock', 2.5],
            'missing category' => ['category_id', 999999],
            'zero price' => ['price', 0],
            'price precision' => ['price', '1.234'],
            'overflow stock' => ['stock', 2147483648],
            'blank whitespace name' => ['name', '   '],
        ];
    }

    #[DataProvider('invalidProductInputs')]
    public function test_invalid_product_does_not_change_database(string $field, mixed $value): void
    {
        $category = Category::factory()->create();
        $data = ['name' => 'Keyboard ABC', 'category_id' => $category->id, 'price' => 100, 'stock' => 1];
        $this->from(route('products.create'))->post(route('products.store'), [...$data, $field => $value])
            ->assertRedirect(route('products.create'))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('products', 0);
        $product = Product::factory()->for($category)->create(['name' => 'Original']);
        $this->from(route('products.edit', $product))->put(route('products.update', $product), [...$data, $field => $value])->assertSessionHasErrors($field);
        $this->assertSame('Original', $product->fresh()->name);
    }

    public function test_invalid_input_preserves_name_and_shows_errors(): void
    {
        $category = Category::factory()->create();
        $this->followingRedirects()->from(route('products.create'))->post(route('products.store'), ['name' => 'Keyboard ABC', 'category_id' => $category->id, 'price' => -100, 'stock' => 0])
            ->assertOk()->assertSee('Keyboard ABC')->assertSee('Giá phải lớn hơn 0.');
    }

    public function test_combined_filters_sort_and_second_page_keep_parameters(): void
    {
        $category = Category::factory()->create(['name' => 'Accessory']);
        for ($i = 1; $i <= 25; $i++) {
            Product::factory()->for($category)->create(['name' => 'Keyboard '.$i, 'price' => 100000 + $i * 1000]);
        }
        Product::factory()->create(['name' => 'Keyboard excluded category', 'price' => 150000]);
        Product::factory()->for($category)->create(['name' => 'Mouse excluded name', 'price' => 150000]);
        Product::factory()->for($category)->create(['name' => 'Keyboard excluded price', 'price' => 2000000]);
        $filters = ['keyword' => 'Keyboard', 'category_id' => $category->id, 'min_price' => 100000, 'max_price' => 1000000, 'sort' => 'price_asc', 'page' => 2];
        $response = $this->get(route('products.index', $filters))->assertOk()->assertDontSee('excluded');
        $products = $response->viewData('products');
        $this->assertSame(25, $products->total());
        $this->assertCount(10, $products);
        $this->assertSame('Keyboard 11', $products->first()->name);
        $this->assertSame('Keyboard 20', $products->last()->name);
        parse_str(parse_url($products->url(3), PHP_URL_QUERY), $query);
        foreach ($filters as $key => $value) {
            $this->assertEquals($key === 'page' ? 3 : $value, $query[$key]);
        }
        $desc = $this->get(route('products.index', [...$filters, 'sort' => 'price_desc', 'page' => 1]))->viewData('products');
        $this->assertSame('Keyboard 25', $desc->first()->name);
    }

    public function test_newest_sort_empty_result_and_literal_wildcard_search(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['name' => 'Normal keyboard', 'created_at' => now()->subDay()]);
        $newest = Product::factory()->for($category)->create(['name' => 'Keyboard 100%_']);
        $this->assertSame($newest->id, $this->get(route('products.index'))->viewData('products')->first()->id);
        $this->get(route('products.index', ['keyword' => 'no-result']))->assertOk()->assertSee('Không tìm thấy sản phẩm');
        $this->assertSame(1, $this->get(route('products.index', ['keyword' => '%_']))->viewData('products')->total());
    }

    public function test_invalid_filter_redirects_to_clean_listing(): void
    {
        $this->get(route('products.index', ['min_price' => 200, 'max_price' => 100]))->assertRedirect(route('products.index'))->assertSessionHasErrors('max_price');
        $this->get(route('products.index', ['sort' => 'DROP TABLE products']))->assertRedirect(route('products.index'))->assertSessionHasErrors('sort');
        $this->get(route('products.index', ['category_id' => 999]))->assertSessionHasErrors('category_id');
    }

    public function test_category_crud_count_detail_and_delete_protection(): void
    {
        $this->get(route('categories.create'))->assertOk();
        $this->post(route('categories.store'), ['name' => 'Accessory'])->assertSessionHasNoErrors()->assertRedirect(route('categories.index'));
        $category = Category::sole();
        $this->get(route('categories.edit', $category))->assertOk()->assertSee('Accessory');
        $this->put(route('categories.update', $category), ['name' => 'Accessory'])->assertSessionHasNoErrors();
        $this->put(route('categories.update', $category), ['name' => 'Accessories'])->assertSessionHasNoErrors();
        $product = Product::factory()->for($category)->create(['name' => 'Visible keyboard']);
        $response = $this->get(route('categories.index'))->assertOk();
        $this->assertSame(1, $response->viewData('categories')->first()->products_count);
        $this->get(route('categories.show', $category))->assertOk()->assertSee('Visible keyboard');
        $this->delete(route('categories.destroy', $category))->assertSessionHas('error')->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $product->delete();
        $this->delete(route('categories.destroy', $category))->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_name_validation_and_not_found_routes(): void
    {
        $category = Category::factory()->create(['name' => 'Unique category']);
        $this->post(route('categories.store'), ['name' => ''])->assertSessionHasErrors('name');
        $this->post(route('categories.store'), ['name' => $category->name])->assertSessionHasErrors('name');
        $this->get('/products/999/edit')->assertNotFound();
        $this->delete('/products/999')->assertNotFound();
        $this->get('/categories/999')->assertNotFound();
    }

    public function test_database_foreign_key_prevents_deleting_used_category(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();
        $this->expectException(QueryException::class);
        $category->delete();
    }

    public function test_seed_has_sixty_products_and_is_repeatable(): void
    {
        $this->seed(MiniShopSeeder::class);
        $this->seed(MiniShopSeeder::class);
        $this->assertDatabaseCount('products', 60);
        $this->assertDatabaseCount('categories', 3);
    }
}
