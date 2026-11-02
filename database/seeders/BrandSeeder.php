<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $logoPath = 'brands/logo-ready.png';
        if (! Storage::disk('public')->exists($logoPath)) {
            try {
                Storage::disk('public')->put($logoPath, file_get_contents('https://placehold.co/200x200/png'));
            } catch (\Exception $e) {
                // skip if download fails
            }
        }

        DB::table('brands')->insertOrIgnore([
            'slug' => 'awesome-brand',
            'name' => 'Awesome Brand',
            'description' => 'A high-quality brand known for its awesome products.',
            'logo_url' => $logoPath,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
