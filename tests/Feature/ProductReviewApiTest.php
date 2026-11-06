<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['slug' => 'review-category', 'status' => true]);
        $brand = Brand::create(['slug' => 'review-brand', 'status' => 'active']);
        $vendor = Vendor::create([
            'name' => 'Review Vendor',
            'email' => 'review.vendor@example.com',
            'password' => bcrypt('password'),
            'phone' => '012345678',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'vendor_id' => $vendor->id,
            'slug' => 'reviewed-lip-balm',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_type' => 'single',
            'status' => 1,
        ]);
    }

    private function customerToken(string $email): array
    {
        $customer = Customer::create([
            'name' => 'Reviewer',
            'email' => $email,
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        return [$customer, $customer->createToken('mobile_app')->plainTextToken];
    }

    public function test_guest_sees_has_reviewed_false(): void
    {
        $this->getJson('/api/products/reviewed-lip-balm/reviews')
            ->assertOk()
            ->assertJson(['status' => true, 'has_reviewed' => false]);
    }

    public function test_customer_who_has_not_reviewed_sees_false(): void
    {
        [, $token] = $this->customerToken('fresh@example.com');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/products/reviewed-lip-balm/reviews')
            ->assertOk()
            ->assertJson(['has_reviewed' => false]);
    }

    public function test_customer_with_pending_review_sees_true(): void
    {
        [$customer, $token] = $this->customerToken('reviewer@example.com');

        // Not approved yet: hidden from the public list, but still counts.
        ProductReview::create([
            'customer_id' => $customer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'review' => 'Great',
            'is_approved' => false,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/products/reviewed-lip-balm/reviews')
            ->assertOk()
            ->assertJson(['total' => 0, 'has_reviewed' => true]);
    }

    public function test_submitting_twice_is_rejected_and_then_reported(): void
    {
        [, $token] = $this->customerToken('twice@example.com');
        $auth = ['Authorization' => 'Bearer '.$token];

        $this->withHeaders($auth)
            ->postJson('/api/products/reviewed-lip-balm/reviews', ['rating' => 5, 'review' => 'First'])
            ->assertCreated();

        $this->withHeaders($auth)
            ->postJson('/api/products/reviewed-lip-balm/reviews', ['rating' => 4])
            ->assertStatus(422)
            ->assertJson(['message' => 'You have already reviewed this product.']);

        $this->withHeaders($auth)
            ->getJson('/api/products/reviewed-lip-balm/reviews')
            ->assertOk()
            ->assertJson(['has_reviewed' => true]);
    }
}
