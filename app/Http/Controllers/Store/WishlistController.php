<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();

        $products = $customer->wishlistProducts()
            ->with(['thumbnail', 'primaryVariant'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('wishlists.created_at', 'desc')
            ->get();

        // All displayed products are already wishlisted
        $wishlistIds = $products->pluck('id')->toArray();

        return view('themes.xylo.wishlist', compact('products', 'wishlistIds'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $customer = Auth::guard('customer')->user();

        $wishlist = Wishlist::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $count = $customer->wishlistProducts()->count();

            return response()->json([
                'status' => 'removed',
                'message' => 'Removed from wishlist.',
                'count' => $count,
            ]);
        }

        Wishlist::create([
            'customer_id' => $customer->id,
            'product_id' => $request->product_id,
        ]);

        $count = $customer->wishlistProducts()->count();

        return response()->json([
            'status' => 'added',
            'message' => 'Added to wishlist.',
            'count' => $count,
        ]);
    }
}
