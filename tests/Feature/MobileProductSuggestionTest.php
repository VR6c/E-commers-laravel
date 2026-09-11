<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileProductSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggestions_and_related_products_full_suite(): void
    {
        $category = Category::create([
            'name'   => 'Smartphones',
            'slug'   => 'smartphones',
            'status' => true,
        ]);

        $categoryLaptops = Category::create([
            'name'   => 'Laptops',
            'slug'   => 'laptops',
            'status' => true,
        ]);

        $brand = Brand::create([
            'name'   => 'Apple',
            'slug'   => 'apple',
            'status' => 'active',
        ]);

        $vendor = Vendor::create([
            'name'     => 'Mobile Vendor',
            'email'    => 'vendor@example.com',
            'password' => bcrypt('password'),
            'phone'    => '012345678',
            'status'   => 'active',
        ]);

        $shop = Shop::create([
            'vendor_id' => $vendor->id,
            'name'      => 'Mobile Shop',
            'slug'      => 'mobile-shop',
            'status'    => 'active',
        ]);

        $iphone15 = Product::create([
            'shop_id'      => $shop->id,
            'vendor_id'    => $vendor->id,
            'slug'         => 'apple-iphone-15-pro',
            'name'         => 'Apple iPhone 15 Pro',
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'tags'         => 'apple, iphone, flagship',
            'product_type' => 'single',
            'status'       => 1,
        ]);

        ProductVariant::create([
            'product_id'   => $iphone15->id,
            'variant_slug' => 'apple-iphone-15-pro-default',
            'name'         => 'Default',
            'price'        => 999.00,
            'stock'        => 10,
            'is_primary'   => true,
        ]);

        $iphone14 = Product::create([
            'shop_id'      => $shop->id,
            'vendor_id'    => $vendor->id,
            'slug'         => 'apple-iphone-14',
            'name'         => 'Apple iPhone 14',
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'tags'         => 'apple, iphone',
            'product_type' => 'single',
            'status'       => 1,
        ]);

        ProductVariant::create([
            'product_id'   => $iphone14->id,
            'variant_slug' => 'apple-iphone-14-default',
            'name'         => 'Default',
            'price'        => 799.00,
            'stock'        => 5,
            'is_primary'   => true,
        ]);

        $macbook = Product::create([
            'shop_id'      => $shop->id,
            'vendor_id'    => $vendor->id,
            'slug'         => 'macbook-pro-16',
            'name'         => 'Apple MacBook Pro 16',
            'category_id'  => $categoryLaptops->id,
            'brand_id'     => $brand->id,
            'tags'         => 'apple, macbook, laptop',
            'product_type' => 'single',
            'status'       => 1,
        ]);

        ProductVariant::create([
            'product_id'   => $macbook->id,
            'variant_slug' => 'macbook-pro-16-default',
            'name'         => 'Default',
            'price'        => 2499.00,
            'stock'        => 3,
            'is_primary'   => true,
        ]);

        // Inactive product to verify exclusion
        Product::create([
            'shop_id'      => $shop->id,
            'vendor_id'    => $vendor->id,
            'slug'         => 'disabled-iphone',
            'name'         => 'Disabled iPhone',
            'category_id'  => $category->id,
            'brand_id'     => $brand->id,
            'product_type' => 'single',
            'status'       => 0,
        ]);

        // 1. Test live search autocomplete (q=iphone)
        $resp1 = $this->getJson('/api/products/suggestions?q=iphone');
        $resp1->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id', 'slug', 'name', 'price', 'thumbnail',
                        'category', 'category_id', 'brand', 'brand_id',
                        'rating', 'reviews_count',
                    ],
                ],
                'keywords',
                'categories',
            ])
            ->assertJson(['status' => true]);

        // 2 active iPhones returned, inactive excluded
        $this->assertCount(2, $resp1->json('data'));
        $this->assertNotEmpty($resp1->json('keywords'));

        // 2. Test recommended feed (no query)
        $resp2 = $this->getJson('/api/products/suggestions');
        $resp2->assertStatus(200)
            ->assertJson(['status' => true]);
        $this->assertCount(3, $resp2->json('data'));

        // 3. Test category-scoped suggestions
        $resp3 = $this->getJson('/api/products/suggestions?category_id=' . $categoryLaptops->id);
        $resp3->assertStatus(200);
        $this->assertCount(1, $resp3->json('data'));
        $this->assertEquals('Apple MacBook Pro 16', $resp3->json('data.0.name'));

        // 4. Test limit parameter
        $resp4 = $this->getJson('/api/products/suggestions?limit=1');
        $resp4->assertStatus(200);
        $this->assertCount(1, $resp4->json('data'));

        // 5. Test related product suggestions by slug
        $resp5 = $this->getJson('/api/products/apple-iphone-15-pro/suggestions');
        $resp5->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'product' => ['id', 'name', 'slug'],
                'data',
            ])
            ->assertJson(['status' => true]);

        // Related must contain iPhone 14, but NOT iPhone 15 Pro
        $relatedIds = collect($resp5->json('data'))->pluck('id')->all();
        $this->assertContains($iphone14->id, $relatedIds);
        $this->assertNotContains($iphone15->id, $relatedIds);

        // 6. Test 404 for missing slug
        $resp6 = $this->getJson('/api/products/non-existent-item/suggestions');
        $resp6->assertStatus(404)
            ->assertJson(['status' => false, 'message' => 'Product not found.']);
    }
}
