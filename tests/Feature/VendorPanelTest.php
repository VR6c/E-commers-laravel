<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use App\Traits\SyncsProductVariants;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class VendorPanelTest extends TestCase
{
    use SyncsProductVariants;

    protected function setUp(): void
    {
        parent::setUp();
        if (! \Illuminate\Support\Facades\Schema::hasTable('vendors')) {
            $this->artisan('migrate');
        }
    }

    public function test_vendor_login_renders_with_auth_card(): void
    {
        $response = $this->get(route('vendor.login'));

        $response->assertStatus(200);
        $response->assertSee('vl-card', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_vendor_dashboard_requires_auth(): void
    {
        $response = $this->get(route('vendor.dashboard'));
        $this->assertContains($response->getStatusCode(), [301, 302]);
    }

    public function test_pending_vendor_login_is_blocked_with_warning(): void
    {
        $email = 'pending_' . uniqid() . '@example.com';
        $vendor = Vendor::create([
            'name'     => 'Pending Vendor',
            'email'    => $email,
            'password' => Hash::make('password123'),
            'status'   => 'pending',
        ]);

        try {
            $response = $this->post(route('vendor.login.submit'), [
                'email'    => $email,
                'password' => 'password123',
            ]);

            $response->assertRedirect(route('vendor.login'));
            $response->assertSessionHas('warning');
            $this->assertFalse(auth()->guard('vendor')->check());
        } finally {
            $vendor->delete();
        }
    }

    public function test_active_vendor_can_login_and_access_dashboard(): void
    {
        $email = 'active_' . uniqid() . '@example.com';
        $vendor = Vendor::create([
            'name'     => 'Active Vendor',
            'email'    => $email,
            'password' => Hash::make('password123'),
            'status'   => 'active',
        ]);

        try {
            $response = $this->post(route('vendor.login.submit'), [
                'email'    => $email,
                'password' => 'password123',
            ]);

            $response->assertRedirect(route('vendor.dashboard'));
            $this->assertTrue(auth()->guard('vendor')->check());

            $dashResponse = $this->actingAs($vendor, 'vendor')->get(route('vendor.dashboard'));
            $dashResponse->assertStatus(200);
            $dashResponse->assertSee('Vendor Dashboard', false);
        } finally {
            auth()->guard('vendor')->logout();
            $vendor->delete();
        }
    }

    public function test_sync_product_variants_marks_only_first_variant_as_primary(): void
    {
        $category = Category::firstOrCreate(['name' => 'Test Cat', 'slug' => 'test-cat-' . uniqid()]);
        $vendor = Vendor::create([
            'name'     => 'Variant Vendor',
            'email'    => 'variant_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'status'   => 'active',
        ]);
        $shop = Shop::create([
            'vendor_id' => $vendor->id,
            'name'      => 'Test Shop',
            'slug'      => 'test-shop-' . uniqid(),
            'status'    => 'active',
        ]);
        $product = Product::create([
            'shop_id'      => $shop->id,
            'vendor_id'    => $vendor->id,
            'name'         => 'Test Variable Product',
            'slug'         => 'test-var-prod-' . uniqid(),
            'category_id'  => $category->id,
            'product_type' => 'variable',
        ]);

        try {
            $variants = [
                ['name' => 'Variant A', 'price' => 10.00, 'stock' => 5, 'SKU' => 'SKU-A-' . uniqid()],
                ['name' => 'Variant B', 'price' => 20.00, 'stock' => 8, 'SKU' => 'SKU-B-' . uniqid()],
                ['name' => 'Variant C', 'price' => 30.00, 'stock' => 12, 'SKU' => 'SKU-C-' . uniqid()],
            ];

            $this->syncVariants($variants, $product);

            $createdVariants = $product->variants()->orderBy('id')->get();
            $this->assertCount(3, $createdVariants);
            $this->assertTrue((bool) $createdVariants[0]->is_primary, 'First variant must be primary');
            $this->assertFalse((bool) $createdVariants[1]->is_primary, 'Second variant must not be primary');
            $this->assertFalse((bool) $createdVariants[2]->is_primary, 'Third variant must not be primary');
        } finally {
            $product->variants()->delete();
            $product->delete();
            $shop->delete();
            $vendor->delete();
        }
    }

    public function test_vendor_order_show_and_safe_multi_tenant_item_deletion(): void
    {
        $vendorA = Vendor::create([
            'name'     => 'Vendor A',
            'email'    => 'va_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'status'   => 'active',
        ]);
        $vendorB = Vendor::create([
            'name'     => 'Vendor B',
            'email'    => 'vb_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'status'   => 'active',
        ]);
        $shopA = Shop::create(['vendor_id' => $vendorA->id, 'name' => 'Shop A', 'slug' => 'shop-a-' . uniqid()]);
        $shopB = Shop::create(['vendor_id' => $vendorB->id, 'name' => 'Shop B', 'slug' => 'shop-b-' . uniqid()]);
        $cat = Category::firstOrCreate(['name' => 'Order Cat', 'slug' => 'order-cat-' . uniqid()]);

        $prodA = Product::create([
            'shop_id'      => $shopA->id,
            'vendor_id'    => $vendorA->id,
            'name'         => 'Item Vendor A',
            'slug'         => 'prod-a-' . uniqid(),
            'category_id'  => $cat->id,
            'product_type' => 'simple',
        ]);
        $prodB = Product::create([
            'shop_id'      => $shopB->id,
            'vendor_id'    => $vendorB->id,
            'name'         => 'Item Vendor B',
            'slug'         => 'prod-b-' . uniqid(),
            'category_id'  => $cat->id,
            'product_type' => 'simple',
        ]);

        $order = Order::create([
            'guest_email'  => 'customer@example.com',
            'total_amount' => 150.00,
            'status'       => 'pending',
        ]);

        $detailA = OrderDetail::create([
            'order_id'   => $order->id,
            'product_id' => $prodA->id,
            'quantity'   => 1,
            'price'      => 50.00,
        ]);
        $detailB = OrderDetail::create([
            'order_id'   => $order->id,
            'product_id' => $prodB->id,
            'quantity'   => 1,
            'price'      => 100.00,
        ]);

        try {
            // Vendor A views the order
            $showResponse = $this->actingAs($vendorA, 'vendor')->get(route('vendor.orders.show', $order->id));
            $showResponse->assertStatus(200);
            $showResponse->assertSee('Item Vendor A');
            $showResponse->assertDontSee('Item Vendor B');

            // Vendor A removes their order items
            $deleteResponse = $this->actingAs($vendorA, 'vendor')->delete(route('vendor.orders.destroy', $order->id));
            $deleteResponse->assertStatus(200);

            // Verify detailA is deleted, but detailB and master order still exist!
            $this->assertDatabaseMissing('order_details', ['id' => $detailA->id]);
            $this->assertDatabaseHas('order_details', ['id' => $detailB->id]);
            $this->assertDatabaseHas('orders', ['id' => $order->id]);

            // Re-fetch order to verify updated total
            $order->refresh();
            $this->assertEquals(100.00, (float) $order->total_amount);
        } finally {
            OrderDetail::where('order_id', $order->id)->delete();
            $order->delete();
            $prodA->delete();
            $prodB->delete();
            $shopA->delete();
            $shopB->delete();
            $vendorA->delete();
            $vendorB->delete();
        }
    }
}
