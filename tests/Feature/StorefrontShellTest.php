<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontShellTest extends TestCase
{
    use RefreshDatabase;
    public function test_home_renders_with_new_header_shell(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // New token-based storefront shell markup.
        $response->assertSee('xsf-header', false);
        $response->assertSee('xsf-nav', false);

        // Preserved shell functionality: search, cart + wishlist indicators.
        $response->assertSee('id="search-input"', false);
        $response->assertSee('id="cart-count"', false);
        $response->assertSee('id="wishlist-count"', false);
    }

    public function test_home_renders_new_footer(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('xsf-footer', false);
    }

    public function test_home_renders_new_sections(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Token-based home sections.
        $response->assertSee('xsf-hero', false);
        $response->assertSee('xsf-section', false);
        $response->assertSee('xsf-features', false);
    }

    public function test_home_has_accessibility_skip_link_and_main_landmark(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('class="skip-link"', false);
        $response->assertSee('id="main-content"', false);
    }
}
