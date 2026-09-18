<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $banners = Banner::where('status', 1)->get();

        return response()->json([
            'status' => true,
            'data'   => BannerResource::collection($banners),
        ])->header('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=86400');
    }
}
