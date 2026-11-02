<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = Cache::remember('api:banners:index', 300, function () {
            return BannerResource::collection(Banner::where('status', 1)->get())->resolve();
        });

        return response()->json([
            'status' => true,
            'data' => $data,
        ])->header('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=86400');
    }
}
