<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use App\Models\Vendor;
use Tests\TestCase;

class PortalRoutesTest extends TestCase
{
    /**
     * Test Admin portal landing and redirects.
     */
    public function test_admin_portal_redirects_unauthenticated_user_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_portal_redirects_authenticated_user_to_dashboard(): void
    {
        $user = (new User)->forceFill([
            'id' => 999991,
            'name' => 'Test Admin',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => 'secret',
        ]);

        $response = $this->actingAs($user)->get('/admin');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Test Vendor portal landing and redirects.
     */
    public function test_vendor_portal_redirects_unauthenticated_user_to_vendor_login(): void
    {
        $response = $this->get('/vendor');
        $response->assertRedirect(route('vendor.login'));
    }

    public function test_vendor_portal_redirects_authenticated_vendor_to_dashboard(): void
    {
        $vendor = (new Vendor)->forceFill([
            'id' => 999992,
            'name' => 'Test Vendor',
            'email' => 'vendor_test_' . uniqid() . '@example.com',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $response = $this->actingAs($vendor, 'vendor')->get('/vendor');
        $response->assertRedirect(route('vendor.dashboard'));
    }

    /**
     * Test Customer portal landing and redirects.
     */
    public function test_customer_portal_redirects_unauthenticated_user_to_customer_login(): void
    {
        $response = $this->get('/customer');
        $response->assertRedirect(route('customer.login'));
    }

    public function test_customer_portal_redirects_authenticated_customer_to_profile(): void
    {
        $customer = (new Customer)->forceFill([
            'id' => 999993,
            'name' => 'Test Customer',
            'email' => 'customer_test_' . uniqid() . '@example.com',
            'password' => 'secret',
        ]);

        $response = $this->actingAs($customer, 'customer')->get('/customer');
        $response->assertRedirect(route('customer.profile.edit'));
    }

    /**
     * Test logout endpoints for all three portals.
     */
    public function test_admin_logout_redirects_to_admin_login(): void
    {
        $response = $this->get('/logout');
        $response->assertRedirect(route('admin.login'));
    }

    public function test_vendor_logout_redirects_to_vendor_login(): void
    {
        $vendor = (new Vendor)->forceFill([
            'id' => 999994,
            'name' => 'Test Vendor 2',
            'email' => 'vendor_test2_' . uniqid() . '@example.com',
            'password' => 'secret',
            'status' => 'active',
        ]);

        $response = $this->actingAs($vendor, 'vendor')->get('/vendor/logout');
        $response->assertRedirect(route('vendor.login'));
    }

    public function test_customer_logout_redirects_to_customer_login(): void
    {
        $customer = (new Customer)->forceFill([
            'id' => 999995,
            'name' => 'Test Customer 2',
            'email' => 'customer_test2_' . uniqid() . '@example.com',
            'password' => 'secret',
        ]);

        $response = $this->actingAs($customer, 'customer')->get('/customer/logout');
        $response->assertRedirect(route('customer.login'));
    }
}
