<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders_with_auth_card(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('auth-card', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_dashboard_renders_for_authenticated_user(): void
    {
        $user = \App\Models\User::query()->first();

        if (! $user) {
            $this->markTestSkipped('no admin user available to test');
        }

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('dash-stat', false);
        $response->assertSee('dashboard-wrapper', false);
    }
}
