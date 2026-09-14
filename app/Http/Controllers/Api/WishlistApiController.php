<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistApiController extends Controller
{
    /**
     * GET /api/wishlist
     * List all products in the authenticated customer's wishlist.
     */
    public function index(Request $request)
    {
        $customer = $request->user();

        $products = $customer->wishlistProducts()
            ->with([
                'thumbnail',
                'category',
                'brand',
                'primaryVariant',
                'variants.attributeValues.attribute',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(fn ($p) => $this->formatProduct($p));

        return response()->json([
            'status' => true,
            'count'  => $products->count(),
            'data'   => $products,
        ]);
    }

    /**
     * POST /api/wishlist/toggle
     * Add the product if it is not wishlisted, remove it if it is.
     * Optional body field "action": "add" | "remove" | "toggle" (default: "toggle").
     *
     * Body: { "product_id": 1, "action": "toggle" }
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'action'     => 'nullable|string|in:add,remove,toggle',
        ]);

        $customer = $request->user();
        $action = $request->input('action', 'toggle');

        $existing = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($action === 'add') {
            if (! $existing) {
                Wishlist::create([
                    'customer_id' => $customer->id,
                    'product_id'  => $request->product_id,
                ]);
            }
            $status = 'added';
        } elseif ($action === 'remove') {
            if ($existing) {
                $existing->delete();
            }
            $status = 'removed';
        } else {
            if ($existing) {
                $existing->delete();
                $status = 'removed';
            } else {
                Wishlist::create([
                    'customer_id' => $customer->id,
                    'product_id'  => $request->product_id,
                ]);
                $status = 'added';
            }
        }

        $count = $customer->wishlistProducts()->count();

        return response()->json([
            'status'  => true,
            'action'  => $status,
            'count'   => $count,
            'message' => $status === 'added'
                ? 'Product added to wishlist.'
                : 'Product removed from wishlist.',
        ]);
    }

    /**
     * DELETE /api/wishlist/{product_id}
     * Explicitly remove a product from the wishlist.
     */
    public function destroy(Request $request, int $productId)
    {
        $customer = $request->user();

        $deleted = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $productId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'status'  => false,
                'message' => 'Product not found in wishlist.',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'count'   => $customer->wishlistProducts()->count(),
            'message' => 'Product removed from wishlist.',
        ]);
    }

    /**
     * GET /api/wishlist/ids
     * Lightweight endpoint — returns only the array of wishlisted product IDs.
     * Useful for the mobile app to mark heart icons on any product listing.
     */
    public function ids(Request $request)
    {
        $ids = $request->user()
            ->wishlistProducts()
            ->pluck('products.id')
            ->values();

        return response()->json([
            'status' => true,
            'data'   => $ids,
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────

    private function formatProduct($product): array
    {
        return [
            'id'                => $product->id,
            'slug'              => $product->slug,
            'name'              => $product->name,
            'short_description' => $product->short_description ?? '',
            'thumbnail'         => $product->thumbnail
                ? product_image_url($product->thumbnail->image_url)
                : '',
            'category'          => $product->category?->name ?? '',
            'brand'             => $product->brand?->name ?? null,
            'price'             => $product->primaryVariant
                ? (float) $product->primaryVariant->converted_price
                : (float) ($product->converted_price ?? 0),
            'discount_price'    => $product->primaryVariant
                ? ($product->primaryVariant->converted_discount_price
                    ? (float) $product->primaryVariant->converted_discount_price
                    : null)
                : null,
            'rating'            => round((float) ($product->reviews_avg_rating ?? 0), 1),
            'reviews_count'     => (int) ($product->reviews_count ?? 0),
            'variants'          => $product->variants ? $product->variants->map(fn ($v) => [
                'id'             => $v->id,
                'name'           => $v->name,
                'price'          => (float) $v->converted_price,
                'discount_price' => $v->converted_discount_price ? (float) $v->converted_discount_price : null,
                'stock'          => $v->stock,
                'sku'            => $v->SKU,
                'is_primary'     => (bool) $v->is_primary,
                'attributes'     => $v->attributeValues ? $v->attributeValues->map(fn ($av) => [
                    'id'    => $av->id,
                    'name'  => $av->attribute?->name ?? '',
                    'value' => $av->value,
                ])->values()->all() : [],
            ])->values()->all() : [],
        ];
    }
}
