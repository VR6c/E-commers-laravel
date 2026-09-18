<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
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
            ->values();

        return response()->json([
            'status' => true,
            'data'   => CategoryResource::collection($categories),
        ])->header('Cache-Control', 'public, max-age=1800, s-maxage=86400, stale-while-revalidate=86400');
    }
}
