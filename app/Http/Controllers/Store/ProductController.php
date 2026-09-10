<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /*public function show($slug)
    {
        $product = Product::where('slug', $slug)
        ->with(['thumbnail', 'reviews'])        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->firstOrFail();
        return view('themes.xylo.product-detail', compact('product'));
    }*/

    public function show($slug)
    {
        $product = Product::with([
            'attributeValues.attribute',
            'reviews',
            'primaryVariant',
            'variants.attributeValues',
            'images',
            'category',
            'category.parent',
        ])->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->where('slug', $slug)
            ->firstOrFail();

        $primaryVariant = $product->primaryVariant ?? $product->variants->firstWhere('is_primary', true) ?? $product->variants->first();
        $inStock = $primaryVariant && $primaryVariant->stock > 0;

        $variantMap = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'attributes' => $variant->attributeValues->pluck('id')->sort()->values()->toArray(),
            ];
        });

        $breadcrumbs = [];
        $category = $product->category;

        while ($category) {
            $breadcrumbs[] = $category;
            $category = $category->parent;
        }

        $breadcrumbs = array_reverse($breadcrumbs);

        // Determine review eligibility for the logged-in customer
        $hasPurchased    = false;
        $alreadyReviewed = false;

        if (Auth::guard('customer')->check()) {
            $customerId = Auth::guard('customer')->id();

            $hasPurchased = Order::where('customer_id', $customerId)
                ->whereHas('details', fn ($q) => $q->where('product_id', $product->id))
                ->whereHas('payments', fn ($q) => $q->where('status', 'completed'))
                ->exists();

            $alreadyReviewed = ProductReview::where('product_id', $product->id)
                ->where('customer_id', $customerId)
                ->exists();
        }

        return view('themes.xylo.product-detail', compact(
            'product', 'inStock', 'variantMap', 'breadcrumbs',
            'hasPurchased', 'alreadyReviewed'
        ));
    }

    public function getVariantPrice(Request $request)
    {
        $variantId = $request->input('variant_id');
        $productId = $request->input('product_id');
        $variant = ProductVariant::with('product')
            ->where('id', $variantId)
            ->where('product_id', $productId)
            ->first();

        if ($variant) {
            $stockStatus = $variant->stock > 0 ? 'IN STOCK' : 'OUT OF STOCK';
            $isOutOfStock = $variant->stock <= 0;

            return response()->json([
                'success' => true,
                'price' => number_format($variant->converted_price, 2),
                'stock' => $stockStatus,
                'is_out_of_stock' => $isOutOfStock,
                'currency_symbol' => activeCurrency()->symbol,
            ]);
        } else {
            return response()->json(['success' => false]);
        }
    }
}
