<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\WithWishlistIds;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Page;

class StoreController extends Controller
{
    use WithWishlistIds;

    public function index()
    {
        $banners = \Illuminate\Support\Facades\Cache::remember('storefront_home_banners', 600, function () {
            return Banner::where('status', 1)
                ->orderBy('id', 'desc')
                ->take(3)
                ->get();
        });

        $categories = \Illuminate\Support\Facades\Cache::remember('storefront_home_categories', 600, function () {
            return Category::where('status', 1)
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        });

        $products = \Illuminate\Support\Facades\Cache::remember('storefront_home_products', 300, function () {
            return Product::where('status', 1)
                ->with(['thumbnail', 'primaryVariant'])
                ->withCount('reviews')
                ->orderBy('id', 'desc')
                ->take(10)
                ->get();
        });

        $wishlistIds = $this->getWishlistIds();

        return view('themes.xylo.home', compact('banners', 'categories', 'products', 'wishlistIds'));
    }

    public function showPage($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        return view('themes.xylo.page', compact('page'));
    }
}
