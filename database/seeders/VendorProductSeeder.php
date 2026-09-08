<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorProductSeeder extends Seeder
{
    public function run(): void
    {
        $sizeAttr  = Attribute::firstOrCreate(['name' => 'Size']);
        $colorAttr = Attribute::firstOrCreate(['name' => 'Color']);

            foreach (['Small', 'Medium', 'Large', 'XL'] as $size) {
                AttributeValue::firstOrCreate(['attribute_id' => $sizeAttr->id, 'value' => $size]);
            }
            foreach (['Red', 'Blue', 'Black', 'White', 'Green'] as $color) {
                AttributeValue::firstOrCreate(['attribute_id' => $colorAttr->id, 'value' => $color]);
            }

            $vendor = Vendor::firstOrCreate(
                ['email' => 'vendor@shop.com'],
                [
                    'name'     => 'Main Vendor Store',
                    'password' => Hash::make('password'),
                    'phone'    => '+855 12 345 678',
                    'status'   => 'active',
                ]
            );

            $category = Category::first();
            $brand    = Brand::first();
            if (!$category) {
                $category = Category::create(['slug' => 'general', 'name' => 'General', 'status' => true]);
            }

            $shopId = DB::table('shops')->where('vendor_id', $vendor->id)->value('id') ?? 1;

            $products = [
                ['name' => 'Classic White T-Shirt',         'min' => 10,  'max' => 25,  'type' => 'variable'],
                ['name' => 'Slim Fit Jeans',                'min' => 30,  'max' => 80,  'type' => 'variable'],
                ['name' => 'Leather Jacket',                'min' => 60,  'max' => 150, 'type' => 'variable'],
                ['name' => 'Running Sneakers',              'min' => 40,  'max' => 100, 'type' => 'variable'],
                ['name' => 'Casual Hoodie',                 'min' => 25,  'max' => 60,  'type' => 'variable'],
                ['name' => 'Polo Shirt',                    'min' => 15,  'max' => 40,  'type' => 'variable'],
                ['name' => 'Summer Shorts',                 'min' => 15,  'max' => 35,  'type' => 'variable'],
                ['name' => 'Formal Dress Shirt',            'min' => 30,  'max' => 70,  'type' => 'variable'],
                ['name' => 'Yoga Pants',                    'min' => 20,  'max' => 50,  'type' => 'variable'],
                ['name' => 'Denim Jacket',                  'min' => 50,  'max' => 120, 'type' => 'variable'],
                ['name' => 'Wireless Bluetooth Speaker',    'min' => 25,  'max' => 80,  'type' => 'simple'],
                ['name' => 'Noise Cancelling Headphones',   'min' => 50,  'max' => 200, 'type' => 'simple'],
                ['name' => 'Smart Watch',                   'min' => 80,  'max' => 250, 'type' => 'simple'],
                ['name' => 'USB-C Hub 7-in-1',              'min' => 20,  'max' => 60,  'type' => 'simple'],
                ['name' => 'Portable Power Bank 20000mAh',  'min' => 25,  'max' => 70,  'type' => 'simple'],
                ['name' => 'Mechanical Keyboard',           'min' => 40,  'max' => 150, 'type' => 'simple'],
                ['name' => 'Wireless Mouse',                'min' => 15,  'max' => 50,  'type' => 'simple'],
                ['name' => 'LED Desk Lamp',                 'min' => 20,  'max' => 60,  'type' => 'simple'],
                ['name' => '4K Webcam',                     'min' => 50,  'max' => 150, 'type' => 'simple'],
                ['name' => 'Portable SSD 1TB',              'min' => 60,  'max' => 130, 'type' => 'simple'],
                ['name' => 'Hiking Backpack 40L',           'min' => 40,  'max' => 100, 'type' => 'variable'],
                ['name' => 'Travel Neck Pillow',            'min' => 10,  'max' => 30,  'type' => 'simple'],
                ['name' => 'Luggage Strap',                 'min' => 5,   'max' => 15,  'type' => 'simple'],
                ['name' => 'Waterproof Rain Jacket',        'min' => 40,  'max' => 120, 'type' => 'variable'],
                ['name' => 'Packing Cubes Set',             'min' => 15,  'max' => 40,  'type' => 'simple'],
                ['name' => 'Stainless Steel Water Bottle',  'min' => 10,  'max' => 35,  'type' => 'simple'],
                ['name' => 'Portable Coffee Maker',         'min' => 20,  'max' => 60,  'type' => 'simple'],
                ['name' => 'Travel Adapter Universal',      'min' => 10,  'max' => 30,  'type' => 'simple'],
                ['name' => 'Compact Tripod',                'min' => 20,  'max' => 70,  'type' => 'simple'],
                ['name' => 'Sunglasses UV400',              'min' => 10,  'max' => 50,  'type' => 'variable'],
                ['name' => 'Ceramic Coffee Mug',            'min' => 5,   'max' => 20,  'type' => 'simple'],
                ['name' => 'Bamboo Cutting Board',          'min' => 10,  'max' => 35,  'type' => 'simple'],
                ['name' => 'Non-Stick Frying Pan',          'min' => 15,  'max' => 50,  'type' => 'simple'],
                ['name' => 'Electric Kettle 1.7L',          'min' => 20,  'max' => 60,  'type' => 'simple'],
                ['name' => 'Aromatherapy Diffuser',         'min' => 15,  'max' => 50,  'type' => 'simple'],
                ['name' => 'Memory Foam Pillow',            'min' => 20,  'max' => 60,  'type' => 'variable'],
                ['name' => 'Microfiber Bed Sheet Set',      'min' => 25,  'max' => 80,  'type' => 'variable'],
                ['name' => 'Scented Candle Set',            'min' => 10,  'max' => 40,  'type' => 'simple'],
                ['name' => 'Wall Clock Minimalist',         'min' => 15,  'max' => 50,  'type' => 'simple'],
                ['name' => 'Plant Pot Ceramic Set',         'min' => 15,  'max' => 45,  'type' => 'variable'],
                ['name' => 'Protein Powder Vanilla',        'min' => 20,  'max' => 60,  'type' => 'simple'],
                ['name' => 'Yoga Mat Non-Slip',             'min' => 15,  'max' => 50,  'type' => 'variable'],
                ['name' => 'Resistance Bands Set',          'min' => 10,  'max' => 35,  'type' => 'simple'],
                ['name' => 'Digital Kitchen Scale',         'min' => 10,  'max' => 30,  'type' => 'simple'],
                ['name' => 'Foam Roller Massage',           'min' => 15,  'max' => 40,  'type' => 'simple'],
                ['name' => 'Jump Rope Speed',               'min' => 5,   'max' => 20,  'type' => 'simple'],
                ['name' => 'Vitamin C Supplements 90ct',    'min' => 10,  'max' => 30,  'type' => 'simple'],
                ['name' => 'Face Moisturizer SPF 30',       'min' => 10,  'max' => 40,  'type' => 'simple'],
                ['name' => 'Lip Gloss Set 6 Colors',        'min' => 8,   'max' => 25,  'type' => 'variable'],
                ['name' => 'Hair Serum Argan Oil',          'min' => 10,  'max' => 35,  'type' => 'simple'],
                ['name' => 'Nail Polish Collection',        'min' => 8,   'max' => 30,  'type' => 'variable'],
            ];

            $imagePool = [
                'https://i.postimg.cc/zBCkRRvb/T-Shirt-removebg-preview.png',
                'https://i.postimg.cc/YS1FXBHT/images-removebg-preview.png',
                'https://i.postimg.cc/2Sn3YdKZ/images-1-removebg-preview-2.png',
                'https://i.postimg.cc/WpDkKZTM/images-2-removebg-preview-1.png',
            ];

            $sizeValues  = AttributeValue::where('attribute_id', $sizeAttr->id)->get();
            $colorValues = AttributeValue::where('attribute_id', $colorAttr->id)->get();

            foreach ($products as $index => $item) {
                $slug = Str::slug($item['name']) . '-' . ($index + 1);

                $product = Product::create([
                    'shop_id'      => $shopId,
                    'vendor_id'    => $vendor->id,
                    'slug'         => $slug,
                    'name'         => $item['name'],
                    'description'  => "Premium quality {$item['name']}. Great value for money with fast delivery.",
                    'category_id'  => $category->id,
                    'brand_id'     => $brand?->id,
                    'product_type' => $item['type'],
                    'status'       => 1,
                ]);

                $imageUrl  = $imagePool[$index % count($imagePool)];
                $imageName = 'product-' . ($index + 1) . '-' . basename($imageUrl);

                $product->images()->create([
                    'name'      => $imageName,
                    'image_url' => $imageUrl,
                    'type'      => 'thumb',
                ]);

                if ($item['type'] === 'variable') {
                    foreach ($sizeValues->take(2) as $sizeVal) {
                        foreach ($colorValues->take(2) as $colorVal) {
                            $price         = rand($item['min'], $item['max']);
                            $discountPrice = rand((int) ($price * 0.6), $price);

                            $variant = $product->variants()->create([
                                'variant_slug'   => Str::slug("{$item['name']} {$sizeVal->value}-{$colorVal->value}") . '-' . uniqid(),
                                'name'           => "{$sizeVal->value} / {$colorVal->value}",
                                'price'          => $price,
                                'discount_price' => $discountPrice,
                                'stock'          => rand(20, 200),
                                'SKU'            => strtoupper(Str::random(2)) . rand(1000, 9999),
                                'weight'         => round(rand(1, 30) / 10, 1),
                                'dimensions'     => rand(5, 30) . 'x' . rand(5, 30) . 'x' . rand(1, 10) . ' cm',
                                'is_primary'     => 1,
                            ]);

                            foreach ([$sizeVal->id, $colorVal->id] as $attrValueId) {
                                DB::table('product_variant_attribute_values')->insert([
                                    'product_id'         => $product->id,
                                    'product_variant_id' => $variant->id,
                                    'attribute_value_id' => $attrValueId,
                                    'created_at'         => now(),
                                    'updated_at'         => now(),
                                ]);
                                ProductAttributeValue::firstOrCreate([
                                    'product_id'         => $product->id,
                                    'attribute_value_id' => $attrValueId,
                                ]);
                            }
                        }
                    }
                } else {
                    $price         = rand($item['min'], $item['max']);
                    $discountPrice = rand((int) ($price * 0.6), $price);

                    $product->variants()->create([
                        'variant_slug'   => Str::slug($item['name']) . '-default-' . uniqid(),
                        'name'           => 'Default',
                        'price'          => $price,
                        'discount_price' => $discountPrice,
                        'stock'          => rand(20, 300),
                        'SKU'            => strtoupper(Str::random(2)) . rand(1000, 9999),
                        'weight'         => round(rand(1, 30) / 10, 1),
                        'dimensions'     => rand(5, 30) . 'x' . rand(5, 30) . 'x' . rand(1, 10) . ' cm',
                        'is_primary'     => 1,
                    ]);
                }
            }

            $this->command->info('VendorProductSeeder: 1 vendor + ' . count($products) . ' products seeded successfully.');
    }
}
