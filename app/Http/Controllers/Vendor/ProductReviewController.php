<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductReviewController extends Controller
{
    public function index()
    {
        return view('vendor.reviews.index');
    }

    public function getData(Request $request)
    {
        $vendorId = auth()->guard('vendor')->id();

        $reviews = ProductReview::with(['product', 'customer'])
            ->whereHas('product', fn ($q) => $q->where('vendor_id', $vendorId));

        return DataTables::of($reviews)
            ->addColumn('product_name', fn ($r) => $r->product?->name ?? 'N/A')
            ->addColumn('customer_name', fn ($r) => optional($r->customer)->name ?? 'Guest')
            ->addColumn('status', function ($review) {
                return $review->is_approved
                    ? '<span class="badge bg-success-soft">Approved</span>'
                    : '<span class="badge bg-warning-soft">Pending</span>';
            })
            ->addColumn('action', function ($review) {
                return '
                    <div class="dt-actions">
                        <a href="' . route('vendor.reviews.show', $review->id) . '"
                           class="btn-action btn-action-view" title="View Review">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                        <button type="button"
                                class="btn-action btn-action-delete"
                                onclick="deleteReview(' . $review->id . ')" title="Delete">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function show(ProductReview $review)
    {
        $vendorId = auth()->guard('vendor')->id();

        if (! $review->product || $review->product->vendor_id !== $vendorId) {
            abort(403, 'Unauthorized access');
        }

        $review->load(['product', 'customer']);

        return view('vendor.reviews.show', compact('review'));
    }

    public function destroy(ProductReview $review)
    {
        $vendorId = auth()->guard('vendor')->id();

        if (! $review->product || $review->product->vendor_id !== $vendorId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }

        try {
            $review->delete();

            return response()->json(['success' => true, 'message' => 'Review deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete review.']);
        }
    }
}
