<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'review'     => 'nullable|string|max:500',
        ]);

        $customerId = Auth::guard('customer')->id();
        $productId  = $request->product_id;

        // Guard: customer must have a completed-payment order containing this product
        $hasPurchased = Order::where('customer_id', $customerId)
            ->whereHas('details', fn ($q) => $q->where('product_id', $productId))
            ->whereHas('payments', fn ($q) => $q->where('status', 'completed'))
            ->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'You must purchase this product before reviewing it.');
        }

        // Guard: already reviewed
        $alreadyReviewed = ProductReview::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'You have already submitted a review for this product.');
        }

        ProductReview::create([
            'customer_id' => $customerId,
            'product_id'  => $productId,
            'rating'      => $request->rating,
            'review'      => $request->review,
            'is_approved' => true,
        ]);

        return back()->with('success', 'Review submitted successfully.');
    }
}
