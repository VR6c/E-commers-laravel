<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
                'category',
                'brand',
                'thumbnail',
                'primaryVariant',
                'variants.attributeValues.attribute',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 1);

        // Search by name or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by brand
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        // Filter by min/max price (based on primary variant price)
        if ($minPrice = $request->input('min_price')) {
            $query->where(function ($q) use ($minPrice) {
                $q->whereHas('primaryVariant', fn ($sub) => $sub->where('price', '>=', $minPrice))
                  ->orWhereHas('variants', fn ($sub) => $sub->where('price', '>=', $minPrice));
            });
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where(function ($q) use ($maxPrice) {
                $q->whereHas('primaryVariant', fn ($sub) => $sub->where('price', '<=', $maxPrice))
                  ->orWhereHas('variants', fn ($sub) => $sub->where('price', '<=', $maxPrice));
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'  => $query->join('product_variants as pv_sort', function ($join) {
                                $join->on('pv_sort.product_id', '=', 'products.id')
                                     ->whereRaw('pv_sort.is_primary is true');
                            })->orderBy('pv_sort.price', 'asc')->select('products.*'),
            'price_desc' => $query->join('product_variants as pv_sort', function ($join) {
                                $join->on('pv_sort.product_id', '=', 'products.id')
                                     ->whereRaw('pv_sort.is_primary is true');
                            })->orderBy('pv_sort.price', 'desc')->select('products.*'),
            'name_asc'   => $query->orderBy('name', 'asc'),
            'name_desc'  => $query->orderBy('name', 'desc'),
            default      => $query->latest(),
        };

        // Pagination (default 20 per page)
        $perPage = min((int) $request->input('per_page', 20), 100);
        $paginated = $query->paginate($perPage);

        $data = $paginated->getCollection()->map(fn ($p) => $this->formatProduct($p));

        return response()->json([
            'status' => true,
            'data'   => $data,
            'meta'   => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ])->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }

    /**
     * GET /api/products/suggestions
     * Suggestions for mobile: live search autocomplete, recommended items, and matching keywords/categories.
     */
    public function suggestions(Request $request)
    {
        $search = trim((string) ($request->input('q') ?? $request->input('search') ?? ''));
        $categoryId = $request->input('category_id');
        $brandId = $request->input('brand_id');
        $excludeId = $request->input('exclude_id');
        $type = $request->input('type', $search !== '' ? 'search' : 'recommended');
        $limit = min(max((int) $request->input('limit', 10), 1), 50);

        $query = Product::with([
                'category',
                'brand',
                'thumbnail',
                'primaryVariant',
                'variants',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 1);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        $keywords = [];
        $matchingCategories = [];

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });

            // Extract matching categories for search pill navigation
            $matchingCategories = Category::where('status', true)
                ->where('name', 'like', "%{$search}%")
                ->limit(5)
                ->get(['id', 'name', 'slug'])
                ->map(fn ($cat) => [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])
                ->values()
                ->all();
        }

        // Sorting strategy
        if ($search !== '') {
            $query->orderBy('name', 'asc');
        } elseif ($type === 'trending') {
            $query->orderByDesc('reviews_count')->orderByDesc('id');
        } else {
            // Default recommended: latest active products
            $query->latest();
        }

        $products = $query->limit($limit)->get();

        // Extract keywords from matching products or categories
        if ($search !== '') {
            $collectedKeywords = collect();
            foreach ($products as $prod) {
                $collectedKeywords->push($prod->name);
                if (! empty($prod->tags)) {
                    $tagParts = array_map('trim', explode(',', (string) $prod->tags));
                    foreach ($tagParts as $tp) {
                        if (stripos($tp, $search) !== false) {
                            $collectedKeywords->push($tp);
                        }
                    }
                }
            }
            foreach ($matchingCategories as $mCat) {
                $collectedKeywords->push($mCat['name']);
            }
            $keywords = $collectedKeywords->unique()->values()->take(8)->all();
        }

        $data = $products->map(fn ($p) => $this->formatSuggestionProduct($p))->values()->all();

        return response()->json([
            'status'     => true,
            'data'       => $data,
            'keywords'   => $keywords,
            'categories' => $matchingCategories,
        ])->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }

    /**
     * GET /api/products/{slug}/suggestions
     * Related product suggestions for single product view on mobile.
     */
    public function related(string $slug, Request $request)
    {
        $product = Product::where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }

        $limit = min(max((int) $request->input('limit', 10), 1), 50);

        // Fetch related products prioritizing same category, then same brand
        $relatedQuery = Product::with([
                'category',
                'brand',
                'thumbnail',
                'primaryVariant',
                'variants',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 1)
            ->where('id', '!=', $product->id);

        if ($product->category_id) {
            $relatedQuery->where('category_id', $product->category_id);
        } elseif ($product->brand_id) {
            $relatedQuery->where('brand_id', $product->brand_id);
        }

        $relatedProducts = $relatedQuery->latest()->limit($limit)->get();

        // If not enough in category/brand, supplement with latest active products
        if ($relatedProducts->count() < $limit) {
            $existingIds = $relatedProducts->pluck('id')->push($product->id)->all();
            $fillCount = $limit - $relatedProducts->count();

            $fallback = Product::with([
                    'category',
                    'brand',
                    'thumbnail',
                    'primaryVariant',
                    'variants',
                ])
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->where('status', 1)
                ->whereNotIn('id', $existingIds)
                ->latest()
                ->limit($fillCount)
                ->get();

            $relatedProducts = $relatedProducts->concat($fallback);
        }

        $data = $relatedProducts->map(fn ($p) => $this->formatSuggestionProduct($p))->values()->all();

        return response()->json([
            'status'  => true,
            'product' => [
                'id'   => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
            ],
            'data'    => $data,
        ])->header('Cache-Control', 'public, max-age=120, s-maxage=600, stale-while-revalidate=1200');
    }

    /**
     * GET /api/products/{slug}
     * Single product detail with full gallery, variants, and reviews summary.
     */
    public function show(string $slug)
    {
        $product = Product::with([
                'category',
                'brand',
                'images',
                'thumbnail',
                'primaryVariant',
                'variants.attributeValues.attribute',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->where('status', 1)
            ->first();

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->formatProduct($product, true),
        ])->header('Cache-Control', 'public, max-age=60, s-maxage=300, stale-while-revalidate=600');
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function formatProduct(Product $p, bool $full = false): array
    {
        $base = [
            'id'                => $p->id,
            'slug'              => $p->slug,
            'name'              => $p->name,
            'description'       => $full ? $p->description : null,
            'short_description' => $p->short_description,
            'price'             => $p->getConvertedPriceAttribute(),
            'thumbnail'         => $p->thumbnail ? product_image_url($p->thumbnail->image_url) : null,
            'category'          => $p->category?->name ?? '',
            'category_id'       => $p->category_id,
            'brand'             => $p->brand?->name ?? null,
            'brand_id'          => $p->brand_id,
            'rating'            => round((float) ($p->reviews_avg_rating ?? 0), 1),
            'reviews_count'     => $p->reviews_count ?? 0,
            'variants'          => $p->variants->map(fn ($v) => [
                'id'             => $v->id,
                'name'           => $v->name,
                'price'          => $v->converted_price,
                'discount_price' => $v->converted_discount_price,
                'stock'          => $v->stock,
                'sku'            => $v->SKU,
                'is_primary'     => (bool) $v->is_primary,
                'attributes'     => $v->attributeValues->map(fn ($av) => [
                    'id'    => $av->id,
                    'name'  => $av->attribute?->name ?? '',
                    'value' => $av->value,
                ])->values()->all(),
            ])->values()->all(),
        ];

        if ($full) {
            $base['gallery'] = $p->images
                ->where('type', '!=', 'thumb')
                ->map(fn ($img) => product_image_url($img->image_url))
                ->values()
                ->all();
            $base['tags']   = $p->tags;
            $base['weight'] = $p->weight;
            $base['sku']    = $p->SKU;
        }

        // Remove null description from list view
        if (! $full) {
            unset($base['description']);
        }

        return $base;
    }

    private function formatSuggestionProduct(Product $p): array
    {
        return [
            'id'             => $p->id,
            'slug'           => $p->slug,
            'name'           => $p->name,
            'price'          => $p->getConvertedPriceAttribute(),
            'discount_price' => $p->getConvertedDiscountPriceAttribute(),
            'thumbnail'      => $p->thumbnail ? product_image_url($p->thumbnail->image_url) : null,
            'category'       => $p->category?->name ?? '',
            'category_id'    => $p->category_id,
            'brand'          => $p->brand?->name ?? null,
            'brand_id'       => $p->brand_id,
            'rating'         => round((float) ($p->reviews_avg_rating ?? 0), 1),
            'reviews_count'  => (int) ($p->reviews_count ?? 0),
        ];
    }
}
