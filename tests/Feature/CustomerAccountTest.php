<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_requires_customer_login(): void
    {
        $response = $this->get(route('customer.profile.edit'));
        $response->assertRedirect(route('customer.login'));
    }

    public function test_profile_renders_for_authenticated_customer(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'test.customer@example.com',
            'password' => 'password123',
        ]);

        $response = $this->actingAs($customer, 'customer')->get(route('customer.profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('xsf-account-nav', false);
        $response->assertSee('id="customer-profile-form"', false);
        $response->assertSee('id="profilePreview"', false);
    }
}
