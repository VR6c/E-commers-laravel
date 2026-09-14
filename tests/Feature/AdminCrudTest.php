<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    protected function admin(): ?User
    {
        try {
            return User::query()->first();
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function test_products_index_renders_with_shared_crud_partials(): void
    {
        $user = $this->admin();
        if (! $user) {
            $this->markTestSkipped('no admin user available');
        }

        $response = $this->actingAs($user)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee('admin-page-header', false);
        $response->assertSee('id="products-table"', false);
        $response->assertSee('id="deleteProductModal"', false);
        $response->assertSee('id="confirmDeleteProduct"', false);
    }

    public function test_categories_index_renders_with_shared_crud_partials(): void
    {
        $user = $this->admin();
        if (! $user) {
            $this->markTestSkipped('no admin user available');
        }

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('admin-page-header', false);
        $response->assertSee('id="categories-table"', false);
        $response->assertSee('id="deleteCategoryModal"', false);
        $response->assertSee('id="confirmDeleteCategory"', false);
    }

    public function test_categories_create_uses_shared_page_header(): void
    {
        $user = $this->admin();
        if (! $user) {
            $this->markTestSkipped('no admin user available');
        }

        $response = $this->actingAs($user)->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('admin-page-header', false);
        $response->assertSee('id="categoryForm"', false);
    }
}
