<?php

namespace App\Console\Commands;

use App\Models\StoreSetting;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class DataImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    public function handle()
    {
        $this->info('Starting data import...');

        $this->info('Creating categories and products...');
        $this->createCategoriesAndProducts();
        $this->info('Categories and products created successfully.');

        $this->info('Running menu seeder');
        $this->call('db:seed', ['--class' => 'MenuSeeder']);

        $this->info('Running currency seeder');
        $this->call('db:seed', ['--class' => 'CurrencySeeder']);

        $this->info('Running brand seeder');
        $this->call('db:seed', ['--class' => 'BrandSeeder']);

        $this->info('Running category seeder');
        $this->call('db:seed', ['--class' => 'CategorySeeder']);

        $this->info('Running product seeder');
        $this->call('db:seed', ['--class' => 'ProductSeeder']);
        $this->info('Running banner seeder');
        $this->call('db:seed', ['--class' => 'BannerSeeder']);

        $this->info('Running page seeder');
        $this->call('db:seed', ['--class' => 'PageSeeder']);

        $this->info('Data import completed successfully!');
    }

    protected function createCategoriesAndProducts()
    {
        $seller = Vendor::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Seller',
                'email' => 'seller@example.com',
                'password' => Hash::make('abc123'),
                'phone' => '+923001234567',
                'profile_image' => 'https://i.postimg.cc/FHxQs4Br/images-10.jpg',
            ]
        );

        StoreSetting::insert([
            ['key' => 'default_currency', 'value' => 'USD'],
            ['key' => 'meta_title', 'value' => 'Welcome to TVR - Your Laravel eCommerce Journey Begins!'],
            ['key' => 'meta_description', 'value' => 'Welcome to TVR! You have successfully installed the ultimate Laravel eCommerce boilerplate. Set up your store, configure settings, and start selling with a powerful multi-vendor, multilingual platform.'],
            ['key' => 'phone_number', 'value' => '071 675 5350'],
        ]);
    }
}
