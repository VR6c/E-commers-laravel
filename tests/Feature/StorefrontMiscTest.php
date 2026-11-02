<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StorefrontMiscTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_requires_customer_login(): void
    {
        // Guarded by auth.customer — guests are redirected to login.
        $response = $this->get(route('customer.wishlist.index'));
        $response->assertRedirect(route('customer.login'));
    }

    public function test_static_page_renders_when_available(): void
    {
        if (! Schema::hasTable('pages')) {
            $this->markTestSkipped('pages table not available');
        }

        $page = Page::query()->where('status', 1)->whereNotNull('slug')->first();

        if (! $page) {
            $this->markTestSkipped('no active page available to test');
        }

        $response = $this->get(route('store.page', $page->slug));

        $response->assertStatus(200);
        $response->assertSee('xsf-static-page', false);
    }
}
