<?php

namespace App\Services\Admin;

use App\Repositories\Admin\Brand\BrandRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandService
{
    protected $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function store($data)
    {
        $slug = Str::slug($data['name'] ?? 'brand');

        $logoPath = null;
        if (isset($data['logo_url']) && $data['logo_url'] instanceof \Illuminate\Http\UploadedFile) {
            $logoPath = \App\Services\ImageUploadService::upload($data['logo_url'], 'brands/logos');
        }

        return $this->brandRepository->store([
            'slug'        => $slug,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'logo_url'    => $logoPath,
            'status'      => $data['status'] ?? 'active',
        ]);
    }

    public function updateBrand($id, $data)
    {
        $brand = $this->brandRepository->find($id);

        if (isset($data['logo_url']) && $data['logo_url'] instanceof \Illuminate\Http\UploadedFile) {
            if ($brand->logo_url && !\Illuminate\Support\Str::startsWith($brand->logo_url, ['http://', 'https://']) && Storage::exists('public/' . $brand->logo_url)) {
                Storage::delete('public/' . $brand->logo_url);
            }
            $brand->logo_url = \App\Services\ImageUploadService::upload($data['logo_url'], 'brands/logos');
        }

        $brand->slug        = Str::slug($data['name'] ?? $brand->slug);
        $brand->name        = $data['name'] ?? $brand->name;
        $brand->description = $data['description'] ?? $brand->description;
        $brand->status      = $data['status'] ?? 'active';
        $brand->save();

        return $brand;
    }

    public function deleteBrand($id)
    {
        $brand = $this->brandRepository->find($id);

        if ($brand->logo_url && Storage::exists('public/' . $brand->logo_url)) {
            Storage::delete('public/' . $brand->logo_url);
        }

        return $brand->delete();
    }

    public function getBrandById($id)
    {
        return $this->brandRepository->find($id);
    }

}
