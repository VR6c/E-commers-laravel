<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::where('status', 'active')->get()->map(fn ($b) => [
            'id'          => $b->id,
            'slug'        => $b->slug,
            'logo_url'    => $b->logo_url,
            'status'      => $b->status,
            'name'        => $b->name,
            'description' => $b->description,
        ]);

        return response()->json(['status' => true, 'data' => $brands])
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=86400');
    }
}
