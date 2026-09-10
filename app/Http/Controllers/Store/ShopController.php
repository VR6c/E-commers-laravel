<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\WithWishlistIds;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use WithWishlistIds;

    public function index(Request $request)
    {
        $filters = [
            'brand' => $request->input('brand', []),
            'price_min' => $request->input('price_min', 0),
            'price_max' => $request->input('price_max', 1000),
            'color' => $request->input('color', []),
            'size' => $request->input('size', []),
        ];

        $sort = $request->input('sort', 'newest');

        $query = Product::with(['thumbnail', 'primaryVariant', 'variants.attributeValues'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('products.status', 1)
            ->when(! empty($filters['category']), function ($q) use ($filters) {
                $q->whereIn('category_id', $filters['category']);
            })
            ->when(! empty($filters['brand']), function ($q) use ($filters) {
                $q->whereIn('brand_id', $filters['brand']);
            })
            ->whereHas('variants', function ($variantQuery) use ($filters) {
                $variantQuery
                    ->when($filters['price_min'], function ($q) use ($filters) {
                        $q->where('price', '>=', $filters['price_min']);
                    })
                    ->when($filters['price_max'], function ($q) use ($filters) {
                        $q->where('price', '<=', $filters['price_max']);
                    })
                    ->when(! empty($filters['color']), function ($q) use ($filters) {
                        $q->whereHas('attributeValues', function ($avQuery) use ($filters) {
                            $avQuery->whereIn('value', $filters['color'])
                                ->whereHas('attribute', function ($aQuery) {
                                    $aQuery->where('name', 'Color');
                                });
                        });
                    })
                    ->when(! empty($filters['size']), function ($q) use ($filters) {
                        $q->whereHas('attributeValues', function ($avQuery) use ($filters) {
                            $avQuery->whereIn('value', $filters['size'])
                                ->whereHas('attribute', function ($aQuery) {
                                    $aQuery->where('name', 'Size');
                                });
                        });
                    });
            });

        if ($sort === 'popular') {
            $query->orderBy('reviews_count', 'desc');
        } elseif ($sort === 'price_asc' || $sort === 'price_desc') {
            $dir = $sort === 'price_asc' ? 'asc' : 'desc';
            $query->leftJoin('product_variants', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->whereRaw('product_variants.is_primary is true');
            })->select('products.*')->orderBy('product_variants.price', $dir);
        } else {
            $query->orderBy('products.id', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        $brands = \Illuminate\Support\Facades\Cache::remember('storefront_shop_brands', 3600, function () {
            return Brand::where('status', 1)->withCount('products')->get();
        });

        $categories = \Illuminate\Support\Facades\Cache::remember('storefront_shop_categories', 3600, function () {
            return Category::where('status', 1)->withCount('products')->get();
        });

        $wishlistIds = $this->getWishlistIds();

        if ($request->ajax()) {
            return view('themes.xylo.partials.product-list', compact('products', 'wishlistIds'))->render();
        }

        return view('themes.xylo.shop', compact('products', 'categories', 'brands', 'wishlistIds'));
    }
}
