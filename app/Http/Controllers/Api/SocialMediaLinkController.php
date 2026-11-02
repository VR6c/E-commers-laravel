<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialMediaLink;
use Illuminate\Http\Request;

class SocialMediaLinkController extends Controller
{
    public function index(Request $request)
    {
        $data = SocialMediaLink::all()->map(fn ($l) => [
            'id' => $l->id,
            'type' => $l->type,
            'platform' => $l->platform,
            'link' => $l->link,
            'name' => $l->name,
        ]);

        return response()->json(['status' => true, 'data' => $data]);
    }
}
