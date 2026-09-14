<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\WithWishlistIds;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use WithWishlistIds;
    public function suggestions(Request $request)
    {
        $query = $request->input('q');

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->with('thumbnail')
            ->limit(10)
            ->get(['id', 'slug', 'name']);

        $data = $products->map(fn ($p) => [
            'id'        => $p->id,
            'slug'      => $p->slug,
            'thumbnail' => product_image_url(optional($p->thumbnail)->image_url),
            'name'      => $p->name,
        ]);

        return response()->json($data);
    }

    public function searchResults(Request $request)
    {
        $query = $request->input('q');

        $products = Product::where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->with(['thumbnail', 'primaryVariant'])
            ->withCount('reviews')
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $wishlistIds = $this->getWishlistIds();

        return view('themes.xylo.search-results', compact('products', 'query', 'wishlistIds'));
    }
}
