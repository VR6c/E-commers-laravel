<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            $query->whereHas('primaryVariant', fn ($q) => $q->where('price', '>=', $minPrice))
                  ->orWhereHas('variants', fn ($q) => $q->where('price', '>=', $minPrice));
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->whereHas('primaryVariant', fn ($q) => $q->where('price', '<=', $maxPrice))
                  ->orWhereHas('variants', fn ($q) => $q->where('price', '<=', $maxPrice));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'price_asc'  => $query->join('product_variants as pv_sort', function ($join) {
                                $join->on('pv_sort.product_id', '=', 'products.id')
                                     ->where('pv_sort.is_primary', true);
                            })->orderBy('pv_sort.price', 'asc')->select('products.*'),
            'price_desc' => $query->join('product_variants as pv_sort', function ($join) {
                                $join->on('pv_sort.product_id', '=', 'products.id')
                                     ->where('pv_sort.is_primary', true);
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
        ]);
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
        ]);
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
            'rating'            => round((float) ($p->reviews_avg_rating ?? $p->averageRating()), 1),
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
}
