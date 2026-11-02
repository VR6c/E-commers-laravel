<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'electronics',
                'parent_slug' => null,
                'name' => 'Electronics',
                'description' => 'Electronic devices',
                'image' => 'https://i.postimg.cc/mgTTQWtW/07-300x300-1-1-removebg-preview.png',
            ],
            [
                'slug' => 'fashion',
                'parent_slug' => null,
                'name' => 'Fashion',
                'description' => 'Clothing and accessories',
                'image' => 'https://i.postimg.cc/QM9NMkFF/cat7-removebg-preview.png',
            ],
            [
                'slug' => 'smartphones',
                'parent_slug' => 'electronics',
                'name' => 'Smartphones',
                'description' => 'Latest mobile phones',
                'image' => 'https://i.postimg.cc/ZKwJFD39/cat1-removebg-preview.png',
            ],
            [
                'slug' => 't-shirts',
                'parent_slug' => 'fashion',
                'name' => 'T-Shirts',
                'description' => 'Casual wear t-shirts',
                'image' => 'https://i.postimg.cc/VkSP9smT/cat2-removebg-preview.png',
            ],
        ];

        foreach ($categories as $data) {
            $parentId = $data['parent_slug']
                ? Category::where('slug', $data['parent_slug'])->value('id')
                : null;

            $imageName = basename($data['image']);
            try {
                $localPath = 'categories/'.$imageName;
                Storage::disk('public')->put($localPath, file_get_contents($data['image']));
            } catch (\Exception $e) {
                $localPath = $data['image'];
            }

            Category::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image_url' => $localPath,
                    'parent_category_id' => $parentId,
                    'status' => true,
                ]
            );
        }
    }
}
