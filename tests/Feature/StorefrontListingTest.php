<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StorefrontListingTest extends TestCase
{
    use RefreshDatabase;
    public function test_shop_index_renders_listing(): void
    {
        $response = $this->get(route('shop.index'));

        $response->assertStatus(200);
        $response->assertSee('xsf-listing-head', false);
        $response->assertSee('id="filterSidebar"', false);
    }

    public function test_shop_ajax_returns_product_list_partial(): void
    {
        $response = $this->get(route('shop.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
    }

    public function test_search_route_renders(): void
    {
        $response = $this->get('/search?q=shirt');

        $response->assertStatus(200);
        $response->assertSee('xsf-listing-head', false);
    }

    public function test_category_route_renders_when_category_exists(): void
    {
        if (! Schema::hasTable('categories')) {
            $this->markTestSkipped('categories table not available');
        }

        $category = Category::query()->whereNotNull('slug')->first();

        if (! $category) {
            $this->markTestSkipped('no category available to test');
        }

        $response = $this->get(route('category.show', $category->slug));

        $response->assertStatus(200);
        $response->assertSee('xsf-breadcrumb', false);
    }
}
