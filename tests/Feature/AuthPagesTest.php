<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    public function test_customer_login_renders_split_auth_card(): void
    {
        $response = $this->get(route('customer.login'));

        $response->assertStatus(200);
        $response->assertSee('auth-card--split', false);
        $response->assertSee('action="'.route('customer.login').'"', false);
    }

    public function test_customer_register_renders_split_auth_card(): void
    {
        $response = $this->get(route('customer.register'));

        $response->assertStatus(200);
        $response->assertSee('auth-card--split', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    public function test_admin_login_renders_auth_card(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('auth-card', false);
    }
}
