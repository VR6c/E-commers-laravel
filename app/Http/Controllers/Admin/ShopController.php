<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ShopController extends Controller
{
    public function index()
    {
        return view('admin.shops.index');
    }

    public function getData(Request $request)
    {
        $query = Shop::query();

        return DataTables::of($query)
            ->addColumn('logo', function ($shop) {
                if ($shop->logo) {
                    $logoUrl = ImageUploadService::isRemoteUrl($shop->logo)
                        ? $shop->logo
                        : asset('storage/' . $shop->logo);
                    return '<img src="' . e($logoUrl) . '" width="50" class="img-thumbnail">';
                }
                return 'No Logo';
            })
            ->addColumn('action', function ($shop) {
                return '
                    <span class="border border-edit dt-trash rounded-3 d-inline-block"><a href="'.route('admin.shops.edit', $shop->id).'"><i class="bi bi-pencil-fill pencil-edit-color"></i></a></span>
                    <span class="border border-danger dt-trash rounded-3 d-inline-block" onclick="deleteShop('.$shop->id.')"><i class="bi bi-trash-fill text-danger"></i></span>
                ';
            })
            ->rawColumns(['logo', 'action'])
            ->make(true);
    }

    public function create()
    {
        // For multi-vendor, we might need a list of vendors, but for now let's keep it simple.
        return view('admin.shops.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('logo')) {
            $data['logo'] = ImageUploadService::upload($request->file('logo'), 'shops');
        }

        Shop::create($data);

        return redirect()->route('admin.shops.index')->with('success', 'Shop created successfully');
    }

    public function edit(Shop $shop)
    {
        return view('admin.shops.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('logo')) {
            if ($shop->logo && !ImageUploadService::isRemoteUrl($shop->logo) && Storage::disk('public')->exists($shop->logo)) {
                Storage::disk('public')->delete($shop->logo);
            }
            $data['logo'] = ImageUploadService::upload($request->file('logo'), 'shops');
        }

        $shop->update($data);

        return redirect()->route('admin.shops.index')->with('success', 'Shop updated successfully.');
    }

    public function destroy(Shop $shop)
    {
        if ($shop->logo && !ImageUploadService::isRemoteUrl($shop->logo) && Storage::disk('public')->exists($shop->logo)) {
            Storage::disk('public')->delete($shop->logo);
        }

        $shop->delete();

        return response()->json(['success' => true, 'message' => 'Shop deleted successfully.']);
    }
}
