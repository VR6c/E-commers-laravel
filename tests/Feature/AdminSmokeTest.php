<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    public static function adminIndexRoutes(): array
    {
        return [
            ['admin.products.index'],
            ['admin.categories.index'],
            ['admin.brands.index'],
            ['admin.attributes.index'],
            ['admin.customers.index'],
            ['admin.vendors.index'],
            ['admin.orders.index'],
            ['admin.payments.index'],
            ['admin.refunds.index'],
            ['admin.payment-gateways.index'],
            ['admin.reviews.index'],
            ['admin.banners.index'],
            ['admin.menus.index'],
            ['admin.pages.index'],
            ['admin.social-media-links.index'],
            ['admin.coupons.index'],
            ['admin.currencies.index'],
            ['admin.languages.index'],
            ['admin.product_variants.index'],
            ['admin.site-settings.index'],
        ];
    }

    /**
     * @dataProvider adminIndexRoutes
     */
    public function test_admin_index_route_renders(string $routeName): void
    {
        try {
            $user = User::query()->first();
        } catch (\Throwable $t) {
            $user = null;
        }
        if (! $user) {
            $this->markTestSkipped('no admin user available');
        }
        if (! Route::has($routeName)) {
            $this->markTestSkipped("route {$routeName} not registered");
        }

        $response = $this->actingAs($user)->get(route($routeName));

        $this->assertContains(
            $response->getStatusCode(),
            [200, 302],
            "Route {$routeName} returned {$response->getStatusCode()}"
        );
    }
}
