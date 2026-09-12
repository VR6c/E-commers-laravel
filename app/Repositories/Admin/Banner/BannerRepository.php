<?php

namespace App\Repositories\Admin\Banner;

use App\Models\Banner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BannerRepository implements BannerRepositoryInterface
{
    public function getAllBanners(): Collection
    {
        return Banner::orderBy('created_at', 'desc')->get();
    }

    public function getBannerById(int $id): Banner
    {
        return Banner::findOrFail($id);
    }

    public function createBanner(array $data): Banner
    {
        return Banner::create(['type' => $data['type']]);
    }

    public function updateBanner(Banner $banner, array $data): Banner
    {
        $banner->type = $data['type'];
        $banner->save();
        return $banner;
    }

    public function deleteBanner(Banner $banner): bool
    {
        if ($banner->image_url && !\Illuminate\Support\Str::startsWith($banner->image_url, ['http://', 'https://']) && Storage::disk('public')->exists($banner->image_url)) {
            Storage::disk('public')->delete($banner->image_url);
        }
        return $banner->delete();
    }
}
