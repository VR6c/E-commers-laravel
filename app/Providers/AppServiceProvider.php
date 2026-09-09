<?php

namespace App\Providers;

use App\Repositories\Admin\Attribute\AttributeRepository;
use App\Repositories\Admin\Attribute\AttributeRepositoryInterface;
use App\Repositories\Admin\Banner\BannerRepository;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use App\Repositories\Admin\Brand\BrandRepository;
use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use App\Repositories\Admin\Menu\MenuRepository;
use App\Repositories\Admin\Menu\MenuRepositoryInterface;
use App\Repositories\Admin\MenuItem\MenuItemRepository;
use App\Repositories\Admin\MenuItem\MenuItemRepositoryInterface;
use App\Repositories\Admin\Product\ProductRepository as AdminProductRepository;
use App\Repositories\Admin\Product\ProductRepositoryInterface as AdminProductRepositoryInterface;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepository;
use App\Repositories\Admin\SocialMediaLink\SocialMediaLinkRepositoryInterface;
use App\Repositories\Shared\Product\ProductRepository as SharedProductRepository;
use App\Repositories\Shared\Product\ProductRepositoryInterface as SharedProductRepositoryInterface;
use App\Repositories\Vendor\Product\ProductRepository as VendorProductRepository;
use App\Repositories\Vendor\Product\ProductRepositoryInterface as VendorProductRepositoryInterface;
use App\Repositories\Vendor\SocialMediaLink\SocialMediaLinkRepository as VendorSocialMediaLinkRepository;
use App\Repositories\Vendor\SocialMediaLink\SocialMediaLinkRepositoryInterface as VendorSocialMediaLinkRepositoryInterface;
use App\Services\Shared\ImageService;
use App\Services\Admin\MenuService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Admin\Category\CategoryRepositoryInterface::class,
            \App\Repositories\Admin\Category\CategoryRepository::class
        );

        // Single shared ImageService singleton used by both Admin and Vendor repositories
        $this->app->singleton(ImageService::class, function ($app) {
            return new ImageService;
        });

        // Shared product repository — used by Admin\ProductService and Vendor\ProductService
        $this->app->bind(SharedProductRepositoryInterface::class, SharedProductRepository::class);

        // Admin-scoped repository bindings
        $this->app->bind(AdminProductRepositoryInterface::class, AdminProductRepository::class);

        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);

        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);

        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(MenuService::class, MenuService::class);

        $this->app->bind(SocialMediaLinkRepositoryInterface::class, SocialMediaLinkRepository::class);

        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);

        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);

        // Vendor-scoped repository bindings
        $this->app->bind(VendorProductRepositoryInterface::class, VendorProductRepository::class);

        $this->app->bind(VendorSocialMediaLinkRepositoryInterface::class, VendorSocialMediaLinkRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        error_reporting(E_ALL & ~E_DEPRECATED);

        if ($this->app->environment('production') || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
