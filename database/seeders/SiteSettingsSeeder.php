<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('site_settings')->exists();

        if ($exists) {
            DB::table('site_settings')->update([
                'site_name' => 'TVR Shop',
                'tagline' => 'Your trusted online marketplace in Cambodia',
                'meta_title' => 'TVR Shop - Online Store',
                'meta_description' => 'Shop the latest electronics, fashion, and more at TVR Shop. Fast delivery across Cambodia.',
                'meta_keywords' => 'ecommerce, shopping, electronics, fashion, cambodia, online store',
                'logo' => 'logo_icon/shopping.png',
                'favicon' => 'favicon.ico',
                'contact_email' => 'tharyvireak121@gmail.com',
                'contact_phone' => '071 675 5350',
                'address' => 'Phnom Penh, Cambodia',
                'footer_text' => '© '.date('Y').' TVR Shop. All rights reserved.',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('site_settings')->insert([
                'site_name' => 'TVR Shop',
                'tagline' => 'Your trusted online marketplace in Cambodia',
                'meta_title' => 'TVR Shop - Online Store',
                'meta_description' => 'Shop the latest electronics, fashion, and more at TVR Shop. Fast delivery across Cambodia.',
                'meta_keywords' => 'ecommerce, shopping, electronics, fashion, cambodia, online store',
                'logo' => 'logo_icon/shopping.png',
                'favicon' => 'favicon.ico',
                'contact_email' => 'tharyvireak121@gmail.com',
                'contact_phone' => '071 675 5350',
                'address' => 'Phnom Penh, Cambodia',
                'footer_text' => '© '.date('Y').' TVR Shop. All rights reserved.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('SiteSettingsSeeder: site settings seeded.');
    }
}
