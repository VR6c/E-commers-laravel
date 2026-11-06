<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/api/products/reviewed-lip-balm/reviews';

    private Product $product;

    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['slug' => 'review-category', 'status' => true]);
        $brand = Brand::create(['slug' => 'review-brand', 'status' => 'active']);
        $this->vendor = Vendor::create([
            'name' => 'Review Vendor',
            'email' => 'review.vendor@example.com',
            'password' => bcrypt('password'),
            'phone' => '012345678',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'slug' => 'reviewed-lip-balm',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'product_type' => 'single',
            'status' => 1,
        ]);
    }

    /** @return array{0: Customer, 1: array<string, string>} */
    private function customer(string $email): array
    {
        $customer = Customer::create([
            'name' => 'Reviewer',
            'email' => $email,
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $token = $customer->createToken('mobile_app')->plainTextToken;

        return [$customer, ['Authorization' => 'Bearer '.$token]];
    }

    private function orderFor(Customer $customer, string $status, string $paymentMethod = 'cod'): Order
    {
        $order = Order::create([
            'vendor_id' => $this->vendor->id,
            'customer_id' => $customer->id,
            'total_amount' => 10,
            'status' => $status,
            'payment_method' => $paymentMethod,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 10,
        ]);

        return $order;
    }

    public function test_guest_cannot_review(): void
    {
        $this->getJson(self::URL)
            ->assertOk()
            ->assertJson(['status' => true, 'has_reviewed' => false, 'can_review' => false]);

        $this->postJson(self::URL, ['rating' => 5])->assertUnauthorized();
    }

    public function test_customer_without_an_order_cannot_review(): void
    {
        [, $auth] = $this->customer('no-order@example.com');

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['can_review' => false]);

        $this->withHeaders($auth)->postJson(self::URL, ['rating' => 5])
            ->assertForbidden()
            ->assertJson(['message' => 'You can only review products you have purchased and paid for.']);
        $this->assertDatabaseCount('product_reviews', 0);
    }

    public function test_unpaid_order_does_not_allow_review(): void
    {
        [$customer, $auth] = $this->customer('unpaid@example.com');
        $this->orderFor($customer, 'pending', 'abapayway');
        $this->orderFor($customer, 'processing');
        $this->orderFor($customer, 'canceled');

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['can_review' => false]);

        $this->withHeaders($auth)->postJson(self::URL, ['rating' => 5])
            ->assertForbidden();
    }

    public function test_completed_order_allows_review(): void
    {
        [$customer, $auth] = $this->customer('paid@example.com');
        $this->orderFor($customer, 'completed', 'abapayway');

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['has_reviewed' => false, 'can_review' => true]);

        $this->withHeaders($auth)->postJson(self::URL, ['rating' => 5, 'review' => 'Great'])
            ->assertCreated();
    }

    public function test_completed_payment_record_allows_review(): void
    {
        [$customer, $auth] = $this->customer('payment@example.com');
        $order = $this->orderFor($customer, 'processing', 'abapayway');

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin.payments@example.com',
            'password' => bcrypt('password'),
        ]);
        $gateway = PaymentGateway::firstOrCreate(['code' => 'abapayway'], ['name' => 'ABA PayWay']);
        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'gateway_id' => $gateway->id,
            'amount' => 10,
            'status' => 'completed',
        ]);

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['can_review' => true]);
    }

    public function test_reviewing_twice_is_rejected_and_reported(): void
    {
        [$customer, $auth] = $this->customer('twice@example.com');
        $this->orderFor($customer, 'completed');

        $this->withHeaders($auth)->postJson(self::URL, ['rating' => 5, 'review' => 'First'])
            ->assertCreated();

        $this->withHeaders($auth)->postJson(self::URL, ['rating' => 4])
            ->assertStatus(422)
            ->assertJson(['message' => 'You have already reviewed this product.']);

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['has_reviewed' => true, 'can_review' => false]);
    }

    public function test_pending_review_counts_as_reviewed(): void
    {
        [$customer, $auth] = $this->customer('pending-review@example.com');
        $this->orderFor($customer, 'completed');

        // Not approved yet: hidden from the public list, but still counts.
        ProductReview::create([
            'customer_id' => $customer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'review' => 'Great',
            'is_approved' => false,
        ]);

        $this->withHeaders($auth)->getJson(self::URL)
            ->assertJson(['total' => 0, 'has_reviewed' => true, 'can_review' => false]);
    }
}
