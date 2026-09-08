<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registration_and_login_flow(): void
    {
        // 1. Register
        $registerData = [
            'name' => 'Mobile Test User',
            'email' => 'mobile.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '012345678',
        ];

        $response = $this->postJson('/api/customer/register', $registerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'customer' => ['id', 'name', 'email'],
                    'token',
                ],
            ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // 2. Login
        $loginResponse = $this->postJson('/api/customer/login', [
            'email' => 'mobile.test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['token', 'customer'],
            ]);

        // 3. Get Profile with Sanctum Bearer Token
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/customer/profile');

        $profileResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'email' => 'mobile.test@example.com',
                ],
            ]);
    }

    public function test_public_catalog_api_endpoints(): void
    {
        $category = Category::create([
            'slug' => 'test-mobile-category',
            'status' => true,
        ]);

        $brand = Brand::create([
            'slug' => 'test-mobile-brand',
            'status' => 'active',
        ]);

        Banner::create([
            'title' => 'Test Mobile Banner',
            'status' => 1,
            'type' => 'promotion',
        ]);

        $user = User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
        ]);

        $vendor = Vendor::create([
            'name' => 'Mobile Vendor',
            'email' => 'vendor.store@example.com',
            'password' => bcrypt('password'),
            'phone' => '012345678',
            'status' => 'active',
        ]);

        $shop = Shop::create([
            'vendor_id' => $vendor->id,
            'name' => 'Mobile Shop',
            'slug' => 'mobile-shop',
            'status' => 'active',
        ]);

        Product::create([
            'shop_id' => $shop->id,
            'vendor_id' => $vendor->id,
            'slug' => 'mobile-smartphone',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_type' => 'single',
            'status' => 1,
        ]);

        // Test Banners
        $this->getJson('/api/banners')->assertStatus(200);

        // Test Brands
        $this->getJson('/api/brands')->assertStatus(200);

        // Test Categories
        $this->getJson('/api/categories')->assertStatus(200);

        // Test Products List
        $this->getJson('/api/products')->assertStatus(200);

        // Test Single Product Detail
        $this->getJson('/api/products/mobile-smartphone')->assertStatus(200);
    }

    public function test_wishlist_endpoints_require_authentication(): void
    {
        // Unauthenticated request should fail with 401
        $this->getJson('/api/wishlist')->assertStatus(401);

        // Authenticated request
        $customer = Customer::create([
            'name' => 'Wishlist Customer',
            'email' => 'wishlist@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $token = $customer->createToken('mobile_app')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/wishlist')
            ->assertStatus(200);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/wishlist/ids')
            ->assertStatus(200);
    }
}
