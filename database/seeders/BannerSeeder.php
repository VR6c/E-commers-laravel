<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Ready to Shop',
                'description' => 'Your one-stop shop for everything you need.',
                'type' => 'promotion',
                'status' => 1,
                'image' => 'https://i.postimg.cc/sxpYkSgk/shoes-ready.png',
            ],
            [
                'title' => 'New Arrivals',
                'description' => 'Check out the latest products just added to our store.',
                'type' => 'promotion',
                'status' => 1,
                'image' => 'https://i.postimg.cc/ZKwJFD39/cat1-removebg-preview.png',
            ],
            [
                'title' => 'Summer Sale',
                'description' => 'Up to 50% off on selected fashion items this season.',
                'type' => 'sale',
                'status' => 1,
                'image' => 'https://i.postimg.cc/QM9NMkFF/cat7-removebg-preview.png',
            ],
            [
                'title' => 'Top Electronics',
                'description' => 'Discover our best-rated gadgets and electronics.',
                'type' => 'featured',
                'status' => 1,
                'image' => 'https://i.postimg.cc/mgTTQWtW/07-300x300-1-1-removebg-preview.png',
            ],
        ];

        foreach ($banners as $data) {
            $imageName = basename($data['image']);
            $localPath = 'banners/'.$imageName;

            if (! Storage::disk('public')->exists($localPath)) {
                try {
                    $contents = file_get_contents($data['image']);
                    if ($contents !== false) {
                        Storage::disk('public')->put($localPath, $contents);
                    }
                } catch (\Exception $e) {
                    $localPath = $data['image']; // fall back to remote URL
                }
            }

            Banner::firstOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'type' => $data['type'],
                    'status' => $data['status'],
                    'image_url' => $localPath,
                ]
            );
        }

        $this->command->info('BannerSeeder: '.count($banners).' banners seeded.');
    }
}
