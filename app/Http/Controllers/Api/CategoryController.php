<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = Cache::remember('api:categories:index', 300, function () {
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

            return CategoryResource::collection($categories)->resolve();
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ])->header('Cache-Control', 'public, max-age=1800, s-maxage=86400, stale-while-revalidate=86400');
    }
}
