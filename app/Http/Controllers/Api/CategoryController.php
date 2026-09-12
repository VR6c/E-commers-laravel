<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with(['children' => function ($q) {
                $q->where('status', true)->withCount('products');
            }])
            ->withCount('products')
            ->where('status', true)
            ->whereNull('parent_category_id')
            ->get()
            ->filter(function ($cat) {
                $totalProducts = $cat->products_count + $cat->children->sum('products_count');
                return $totalProducts > 0;
            })
            ->values()
            ->map(fn ($cat) => [
                'id'             => $cat->id,
                'slug'           => $cat->slug,
                'name'           => $cat->name,
                'description'    => $cat->description,
                'image_url'      => $cat->image_url,
                'products_count' => $cat->products_count + $cat->children->sum('products_count'),
                'children'       => $cat->children->filter(fn ($c) => $c->products_count > 0)->values()->map(fn ($child) => [
                    'id'             => $child->id,
                    'slug'           => $child->slug,
                    'name'           => $child->name,
                    'description'    => $child->description,
                    'image_url'      => $child->image_url,
                    'products_count' => $child->products_count,
                ]),
            ]);

        return response()->json(['status' => true, 'data' => $categories]);
    }
}
