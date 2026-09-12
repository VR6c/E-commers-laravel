<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::where('status', 1)->get()->map(fn ($b) => [
            'id'          => $b->id,
            'type'        => $b->type,
            'title'       => $b->title,
            'description' => $b->description,
            'image_url'   => $b->image_url
                ? (\Illuminate\Support\Str::startsWith($b->image_url, ['http://', 'https://'])
                    ? $b->image_url
                    : \Illuminate\Support\Facades\Storage::disk('public')->url($b->image_url))
                : null,
        ]);

        return response()->json(['status' => true, 'data' => $banners])
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=86400');
    }
}
