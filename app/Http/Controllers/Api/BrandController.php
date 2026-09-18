<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $brands = Brand::where('status', 'active')->get();

        return response()->json([
            'status' => true,
            'data'   => BrandResource::collection($brands),
        ])->header('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=86400');
    }
}
