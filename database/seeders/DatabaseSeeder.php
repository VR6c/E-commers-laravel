<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /*
     *   php artisan db:seed --class=VendorProductSeeder
     */
    public function run(): void
    {
        $this->call([
            SiteSettingsSeeder::class,
            AdminSeeder::class,
            CustomerSeeder::class,
            CurrencySeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            AttributeSeeder::class,
            BannerSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            ProductSeeder::class,
            PaymentGatewaySeeder::class,
            PaymentGatewayConfigSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            RefundSeeder::class,
            RecipeSeeder::class,
        ]);
    }
}
