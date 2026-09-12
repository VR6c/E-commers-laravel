<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\Admin\BannerService;
use App\Traits\UpdatesModelStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class BannerController extends Controller
{
    use UpdatesModelStatus;
    protected $bannerService;

    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(Request $request)
    {
        $banners = $this->bannerService->getAllBanners();

        return view('admin.banners.index', compact('banners'));
    }

    public function getData(Request $request)
    {
        $banners = Banner::all();

        return DataTables::of($banners)
            ->addColumn('image', function ($banner) {
                if (!$banner->image_url) {
                    return null;
                }
                if (\Illuminate\Support\Str::startsWith($banner->image_url, ['http://', 'https://'])) {
                    return $banner->image_url;
                }
                return Storage::disk('public')->url($banner->image_url);
            })
            ->addColumn('title', function ($banner) {
                return $banner->title ?? '';
            })
            ->addColumn('action', function ($banner) {
                return '';
            })
            ->make(true);
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $this->bannerService->store($request);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $banner = Banner::findOrFail($id);

        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $this->bannerService->update($request, $id);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $this->bannerService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Banner deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting banner with ID {$id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the banner.',
            ]);
        }
    }

    public function updateStatus(Request $request)
    {
        return $this->performStatusUpdate(Banner::class, $request);
    }
}
