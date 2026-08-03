<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\ProductSearch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->vendor = Vendor::create([
            'name' => 'Test Vendor',
            'user_id' => $user->id,
            'store_name' => 'Test Vendor Store',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
            'phone' => '1234567890',
            'status' => 'active',
        ]);

        $this->shop = Shop::create([
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Shop',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);
    }

    private function createProduct(string $name, float $price, array $attributes = []): Product
    {
        $category = $attributes['category_id'] ?? Category::create(['name' => 'Default Cat', 'slug' => 'default-cat-' . Str::random(5)])->id;

        $product = Product::create(array_merge([
            'shop_id' => $this->shop->id,
            'vendor_id' => $this->vendor->id,
            'category_id' => $category,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(5),
            'product_type' => 'simple',
            'status' => 1,
        ], $attributes));

        ProductVariant::create([
            'product_id' => $product->id,
            'variant_slug' => Str::slug($name) . '-variant-' . Str::random(5),
            'price' => $price,
            'SKU' => 'SKU-' . Str::upper(Str::random(6)),
            'is_primary' => true,
        ]);

        return $product;
    }

    public function test_product_search_component_renders_successfully(): void
    {
        Livewire::test(ProductSearch::class)
            ->assertStatus(200)
            ->assertViewIs('livewire.product-search');
    }

    public function test_can_search_products_by_name(): void
    {
        $matchingProduct = $this->createProduct('Wireless Gaming Mouse', 49.99);
        $otherProduct = $this->createProduct('Mechanical Keyboard', 89.99);

        Livewire::test(ProductSearch::class)
            ->set('search', 'Gaming Mouse')
            ->assertSee($matchingProduct->name)
            ->assertDontSee($otherProduct->name);
    }

    public function test_can_filter_products_by_category(): void
    {
        $categoryA = Category::create(['name' => 'Electronics', 'slug' => 'electronics']);
        $categoryB = Category::create(['name' => 'Books', 'slug' => 'books']);

        $productA = $this->createProduct('Smartphone', 299.99, ['category_id' => $categoryA->id]);
        $productB = $this->createProduct('Novel Book', 19.99, ['category_id' => $categoryB->id]);

        Livewire::test(ProductSearch::class)
            ->set('category_id', $categoryA->id)
            ->assertSee($productA->name)
            ->assertDontSee($productB->name);
    }

    public function test_can_filter_products_by_brand(): void
    {
        $brandA = Brand::create(['name' => 'Logitech', 'slug' => 'logitech']);
        $brandB = Brand::create(['name' => 'Razer', 'slug' => 'razer']);

        $productA = $this->createProduct('MX Master', 99.99, ['brand_id' => $brandA->id]);
        $productB = $this->createProduct('DeathAdder', 69.99, ['brand_id' => $brandB->id]);

        Livewire::test(ProductSearch::class)
            ->set('brand_id', $brandA->id)
            ->assertSee($productA->name)
            ->assertDontSee($productB->name);
    }

    public function test_can_filter_products_by_price_range(): void
    {
        $cheapProduct = $this->createProduct('Budget Earbuds', 15.00);
        $expensiveProduct = $this->createProduct('Pro Headset', 250.00);

        Livewire::test(ProductSearch::class)
            ->set('min_price', 10.00)
            ->set('max_price', 50.00)
            ->assertSee($cheapProduct->name)
            ->assertDontSee($expensiveProduct->name);
    }

    public function test_can_sort_products_by_price(): void
    {
        $productLow = $this->createProduct('Cheap Cable', 5.00);
        $productHigh = $this->createProduct('High-End Monitor', 500.00);

        Livewire::test(ProductSearch::class)
            ->set('sort_by', 'price_asc')
            ->assertSeeInOrder([$productLow->name, $productHigh->name]);
    }

    public function test_search_resets_pagination_page(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createProduct("Product Item {$i}", 10.00 + $i);
        }

        Livewire::test(ProductSearch::class)
            ->call('gotoPage', 2)
            ->set('search', 'Product Item')
            ->assertSet('paginators.page', 1);
    }

    public function test_can_clear_all_filters(): void
    {
        $category = Category::create(['name' => 'Hardware', 'slug' => 'hardware']);

        Livewire::test(ProductSearch::class)
            ->set('search', 'Sample')
            ->set('category_id', $category->id)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('category_id', null);
    }
}
