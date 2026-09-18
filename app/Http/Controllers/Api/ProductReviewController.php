<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    /**
     * GET /api/products/{slug}/reviews
     * List all approved reviews for a product.
     */
    public function index(string $slug)
    {
        $product = Product::where('slug', $slug)->where('status', 1)->first();

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }

        $reviews = ProductReview::with('customer:id,name,profile_image')
            ->where('product_id', $product->id)
            ->approved()
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating') ?? 0;

        return response()->json([
            'status'     => true,
            'avg_rating' => round((float) $avgRating, 1),
            'total'      => $reviews->count(),
            'data'       => \App\Http\Resources\ProductReviewResource::collection($reviews),
        ]);
    }

    /**
     * POST /api/products/{slug}/reviews
     * Submit a review for a product. Requires authentication.
     * A customer may only leave one review per product.
     */
    public function store(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('status', 1)->first();

        if (! $product) {
            return response()->json(['status' => false, 'message' => 'Product not found.'], 404);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:2000',
        ]);

        $customer = $request->user();

        $existing = ProductReview::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status'  => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $review = ProductReview::create([
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'rating'      => $request->rating,
            'review'      => $request->review,
            'is_approved' => false, // pending admin approval
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Review submitted and pending approval.',
            'data'    => [
                'id'         => $review->id,
                'rating'     => (int) $review->rating,
                'review'     => $review->review,
                'created_at' => $review->created_at?->toISOString(),
            ],
        ], 201);
    }
}
