<?php

namespace App\Services\Admin;

use App\Models\Banner;
use App\Repositories\Admin\Banner\BannerRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    protected $bannerRepository;

    public function __construct(BannerRepositoryInterface $bannerRepository)
    {
        $this->bannerRepository = $bannerRepository;
    }

    public function getAllBanners()
    {
        return $this->bannerRepository->getAllBanners();
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'                       => 'required|in:promotion,sale,seasonal,featured,announcement',
            'languages.en.title'         => 'required|string|max:255',
            'languages.en.description'   => 'nullable|string',
            'languages.en.image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
        ]);

        $banner = $this->bannerRepository->createBanner($request->only('type'));

        $imageUrl = null;
        if ($request->hasFile('languages.en.image')) {
            $imageUrl = \App\Services\ImageUploadService::upload($request->file('languages.en.image'), 'banner_images');
        }

        $banner->update([
            'title'       => $request->input('languages.en.title'),
            'description' => $request->input('languages.en.description'),
            'image_url'   => $imageUrl,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'type'                       => 'required|in:promotion,sale,seasonal,featured,announcement',
            'languages.en.title'         => 'required|string|max:255',
            'languages.en.description'   => 'nullable|string',
            'languages.en.image'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10000',
        ]);

        $banner = $this->bannerRepository->getBannerById($id);
        $this->bannerRepository->updateBanner($banner, $request->only('type'));

        $imageUrl = $banner->image_url;
        if ($request->hasFile('languages.en.image')) {
            if ($imageUrl && !\Illuminate\Support\Str::startsWith($imageUrl, ['http://', 'https://']) && Storage::disk('public')->exists($imageUrl)) {
                Storage::disk('public')->delete($imageUrl);
            }
            $imageUrl = \App\Services\ImageUploadService::upload($request->file('languages.en.image'), 'banner_images');
        }

        $banner->update([
            'title'       => $request->input('languages.en.title', $banner->title),
            'description' => $request->input('languages.en.description', $banner->description),
            'image_url'   => $imageUrl,
        ]);
    }

    public function delete(int $id)
    {
        $banner = $this->bannerRepository->getBannerById($id);
        $this->bannerRepository->deleteBanner($banner);
    }
}
