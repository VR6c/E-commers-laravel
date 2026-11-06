<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    /**
     * GET /api/products/{slug}/reviews
     * List all approved reviews for a product.
     *
     * Public, but when a customer's Bearer token is sent:
     *  - `has_reviewed`: the customer already reviewed the product (including
     *    reviews not yet approved);
     *  - `can_review`: the customer bought and paid for it and hasn't reviewed
     *    it yet, so clients can show or hide the review form up front.
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

        $customer = Auth::guard('sanctum')->user();
        $hasReviewed = $customer !== null && ProductReview::where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->exists();
        $canReview = $customer !== null && ! $hasReviewed
            && $this->hasPaidFor($customer->id, $product->id);

        return response()->json([
            'status' => true,
            'avg_rating' => round((float) $avgRating, 1),
            'total' => $reviews->count(),
            'has_reviewed' => $hasReviewed,
            'can_review' => $canReview,
            'data' => \App\Http\Resources\ProductReviewResource::collection($reviews),
        ]);
    }

    /**
     * POST /api/products/{slug}/reviews
     * Submit a review for a product. Requires authentication.
     * A customer may only leave one review per product, and only for a product
     * they have bought and paid for.
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
                'status' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        if (! $this->hasPaidFor($customer->id, $product->id)) {
            return response()->json([
                'status' => false,
                'message' => 'You can only review products you have purchased and paid for.',
            ], 403);
        }

        try {
            $review = ProductReview::create([
                'customer_id' => $customer->id,
                'product_id' => $product->id,
                'rating' => $request->rating,
                'review' => $request->review,
                'is_approved' => true, // published immediately, no admin approval needed
            ]);
        } catch (UniqueConstraintViolationException) {
            // A concurrent request (e.g. double-tap) already saved this customer's review
            return response()->json([
                'status' => false,
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        $review->load('customer:id,name,profile_image');

        $approved = ProductReview::where('product_id', $product->id)->approved();

        return response()->json([
            'status' => true,
            'message' => 'Review submitted successfully.',
            'avg_rating' => round((float) $approved->avg('rating'), 1),
            'total' => $approved->count(),
            'data' => new \App\Http\Resources\ProductReviewResource($review),
        ], 201);
    }

    /**
     * Whether the customer has a paid order containing the product: either a
     * completed order (ABA PayWay approved, or cash collected and marked
     * completed by the vendor) or an order with a successful payment record.
     */
    private function hasPaidFor(int $customerId, int $productId): bool
    {
        return Order::where('customer_id', $customerId)
            ->whereHas('details', fn ($q) => $q->where('product_id', $productId))
            ->where(fn ($q) => $q
                ->where('status', 'completed')
                ->orWhereHas('payments', fn ($p) => $p->where('status', 'completed')))
            ->exists();
    }
}
