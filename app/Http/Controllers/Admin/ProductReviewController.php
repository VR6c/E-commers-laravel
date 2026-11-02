<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.reviews.index');
    }

    public function getData()
    {
        $reviews = ProductReview::with(['product', 'customer']);

        return DataTables::of($reviews)
            // Column keys match the JS DataTable columns config: 'product', 'customer', 'status', 'action'
            ->addColumn('product', fn ($r) => $r->product?->name ?? 'N/A')
            ->addColumn('customer', fn ($r) => optional($r->customer)->name ?? 'Guest')
            ->addColumn('status', function ($review) {
                // Fixed: use is_approved (boolean), not the non-existent 'status' field
                return $review->is_approved
                    ? '<span class="badge bg-success">Approved</span>'
                    : '<span class="badge bg-warning text-dark">Pending</span>';
            })
            ->addColumn('action', function ($review) {
                return '<a href="'.route('admin.reviews.show', $review->id).'" class="btn-action btn-action-edit" title="View"><i class="bi bi-eye-fill"></i></a>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show(ProductReview $review)
    {
        $review->load(['product', 'customer']);

        return view('admin.reviews.show', compact('review'));
    }

    public function edit(ProductReview $review)
    {
        return view('admin.reviews.edit', compact('review'));
    }

    public function update(Request $request, ProductReview $review)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'is_approved' => 'required|boolean',
        ]);

        $review->update([
            'rating' => $request->rating,
            'review' => $request->review,
            'is_approved' => $request->is_approved,
        ]);

        return redirect()->route('admin.reviews.show', $review->id)
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Toggle the approval status of a review (AJAX).
     */
    public function toggleApprove(ProductReview $review)
    {
        $review->update(['is_approved' => ! $review->is_approved]);

        $label = $review->is_approved ? 'approved' : 'pending';

        return response()->json([
            'success' => true,
            'is_approved' => $review->is_approved,
            'message' => "Review marked as {$label}.",
        ]);
    }

    public function destroy(ProductReview $review)
    {
        try {
            $review->delete();

            return response()->json(['success' => true, 'message' => 'Review deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete review.']);
        }
    }
}
