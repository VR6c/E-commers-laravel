<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BulkProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param int $count Number of products to seed (default 100)
     */
    public function run(int $count = 100): void
    {
        $this->seedProducts($count);
    }

    public function seedProducts(int $count = 100): int
    {
        try {
            DB::connection()->getPdo()->exec('ROLLBACK;');
        } catch (\Throwable $t) {
        }

        // 1. Ensure Attributes (Size & Color) exist
        $sizeAttr  = Attribute::firstOrCreate(['name' => 'Size']);
        $colorAttr = Attribute::firstOrCreate(['name' => 'Color']);

        $sizes = ['Small', 'Medium', 'Large', 'XL', 'XXL'];
        foreach ($sizes as $s) {
            AttributeValue::firstOrCreate(['attribute_id' => $sizeAttr->id, 'value' => $s]);
        }

        $colors = ['Black', 'White', 'Blue', 'Red', 'Green', 'Silver', 'Gold', 'Navy'];
        foreach ($colors as $c) {
            AttributeValue::firstOrCreate(['attribute_id' => $colorAttr->id, 'value' => $c]);
        }

        $sizeValues  = AttributeValue::where('attribute_id', $sizeAttr->id)->get();
        $colorValues = AttributeValue::where('attribute_id', $colorAttr->id)->get();

        // 2. Ensure Vendor and Shop exist
        $vendor = Vendor::first() ?? Vendor::create([
            'name'     => 'Main Store Vendor',
            'email'    => 'vendor@store.com',
            'password' => Hash::make('password'),
            'phone'    => '+1234567890',
            'status'   => 'active',
        ]);

        $shop = Shop::first() ?? Shop::create([
            'vendor_id'   => $vendor->id,
            'name'        => 'Official Store',
            'slug'        => 'official-store',
            'description' => 'Official verified shop.',
            'status'      => 'active',
        ]);

        // 3. Ensure Categories exist
        $existingCategories = Category::where('status', true)->get();
        if ($existingCategories->isNotEmpty()) {
            $categories = $existingCategories->all();
        } else {
            $categoriesData = [
                ['name' => 'Electronics & Gadgets', 'slug' => 'electronics-gadgets'],
                ['name' => 'Fashion & Apparel',     'slug' => 'fashion-apparel'],
                ['name' => 'Home & Kitchen',        'slug' => 'home-kitchen'],
                ['name' => 'Health & Beauty',       'slug' => 'health-beauty'],
                ['name' => 'Sports & Outdoors',     'slug' => 'sports-outdoors'],
                ['name' => 'Smartphones & Mobile',  'slug' => 'smartphones-mobile'],
                ['name' => 'Footwear & Shoes',      'slug' => 'footwear-shoes'],
                ['name' => 'Accessories & Jewelry', 'slug' => 'accessories-jewelry'],
            ];

            $categories = [];
            foreach ($categoriesData as $cData) {
                $categories[] = Category::firstOrCreate(
                    ['slug' => $cData['slug']],
                    ['name' => $cData['name'], 'status' => true]
                );
            }
        }

        // 4. Ensure Brands exist (status must be 'active' for PostgreSQL constraint)
        $existingBrands = Brand::where('status', 'active')->get();
        if ($existingBrands->isNotEmpty()) {
            $brands = $existingBrands->all();
        } else {
            $brandsData = ['TechNova', 'ApexStyle', 'UrbanLiving', 'FitPulse', 'LuxeGear', 'AuraBeauty', 'EcoPrime', 'Vanguard'];
            $brands = [];
            foreach ($brandsData as $bName) {
                $brands[] = Brand::firstOrCreate(
                    ['slug' => Str::slug($bName)],
                    ['name' => $bName, 'status' => 'active']
                );
            }
        }

        // 5. Product definitions templates
        $productTemplates = [
            // Electronics
            ['name' => 'Pro Sound Active Noise Cancelling Headphones', 'cat' => 'electronics-gadgets', 'min' => 89, 'max' => 199, 'type' => 'variable', 'photo' => '1505740420928-5e560c06d30e'],
            ['name' => 'Ultra Slim Bluetooth Wireless Speaker', 'cat' => 'electronics-gadgets', 'min' => 35, 'max' => 79, 'type' => 'simple', 'photo' => '1608043152269-423dbba4e7e1'],
            ['name' => 'Smart Fitness Tracker Watch Pro', 'cat' => 'electronics-gadgets', 'min' => 59, 'max' => 129, 'type' => 'variable', 'photo' => '1523275335684-37898b6baf30'],
            ['name' => 'Multi-Port USB-C Charging Hub 8-in-1', 'cat' => 'electronics-gadgets', 'min' => 29, 'max' => 59, 'type' => 'simple', 'photo' => '1593642632559-0c6d3fc62b89'],
            ['name' => 'Mechanical Wireless RGB Gaming Keyboard', 'cat' => 'electronics-gadgets', 'min' => 69, 'max' => 149, 'type' => 'variable', 'photo' => '1587829741301-dc798b83add3'],
            ['name' => 'Ergonomic Optical Gaming Mouse', 'cat' => 'electronics-gadgets', 'min' => 25, 'max' => 65, 'type' => 'simple', 'photo' => '1615663245857-ac93bb7c39e7'],
            ['name' => '4K Ultra HD Streamer Webcam', 'cat' => 'electronics-gadgets', 'min' => 45, 'max' => 95, 'type' => 'simple', 'photo' => '1593642634402-b0eb5e2eebc9'],
            ['name' => 'High Speed Portable SSD 2TB', 'cat' => 'electronics-gadgets', 'min' => 99, 'max' => 189, 'type' => 'simple', 'photo' => '1597872200969-2b65d56bd16b'],
            ['name' => 'Fast Charging Power Bank 30000mAh', 'cat' => 'electronics-gadgets', 'min' => 39, 'max' => 79, 'type' => 'simple', 'photo' => '1609091839311-d5365f9ff1c5'],
            ['name' => 'Wireless Desk Charging Station 3-in-1', 'cat' => 'electronics-gadgets', 'min' => 30, 'max' => 65, 'type' => 'simple', 'photo' => '1586953208448-b95a79798f07'],

            // Smartphones
            ['name' => 'Titanium Pro Smartphone 5G', 'cat' => 'smartphones-mobile', 'min' => 499, 'max' => 899, 'type' => 'variable', 'photo' => '1510557880182-3d4d3cba35a5'],
            ['name' => 'Lite Edition 6.5-inch Smartphone', 'cat' => 'smartphones-mobile', 'min' => 199, 'max' => 349, 'type' => 'variable', 'photo' => '1574944985070-8f3ebc6b79d2'],
            ['name' => 'Foldable Dual-Screen Smart Phone', 'cat' => 'smartphones-mobile', 'min' => 699, 'max' => 1199, 'type' => 'variable', 'photo' => '1598327105854-e4ee1e4f2b17'],
            ['name' => 'Rugged All-Weather Waterproof Phone', 'cat' => 'smartphones-mobile', 'min' => 299, 'max' => 499, 'type' => 'simple', 'photo' => '1601784551446-20c326ef1a5d'],
            ['name' => 'Pro Camera Phone 108MP Zoom', 'cat' => 'smartphones-mobile', 'min' => 399, 'max' => 699, 'type' => 'variable', 'photo' => '1592899677977-9c10002761d5'],

            // Fashion
            ['name' => 'Premium Organic Cotton Crewneck Tee', 'cat' => 'fashion-apparel', 'min' => 18, 'max' => 38, 'type' => 'variable', 'photo' => '1521572163474-6864f9cf17ab'],
            ['name' => 'Classic Vintage Denim Jacket', 'cat' => 'fashion-apparel', 'min' => 59, 'max' => 119, 'type' => 'variable', 'photo' => '1598522105502-a8acdf5e3df4'],
            ['name' => 'Slim Fit Chino Trousers', 'cat' => 'fashion-apparel', 'min' => 35, 'max' => 69, 'type' => 'variable', 'photo' => '1473966968600-fa801b869a1a'],
            ['name' => 'Fleece Zip-Up Hoodie Jacket', 'cat' => 'fashion-apparel', 'min' => 42, 'max' => 85, 'type' => 'variable', 'photo' => '1556821840-3a63f8550908'],
            ['name' => 'High Waist Stretch Skinny Jeans', 'cat' => 'fashion-apparel', 'min' => 39, 'max' => 79, 'type' => 'variable', 'photo' => '1542272604-787c3835535d'],
            ['name' => 'Casual Linen Button-Down Shirt', 'cat' => 'fashion-apparel', 'min' => 32, 'max' => 65, 'type' => 'variable', 'photo' => '1604644401890-0bd678c83788'],
            ['name' => 'Breathable Piqué Polo Shirt', 'cat' => 'fashion-apparel', 'min' => 24, 'max' => 49, 'type' => 'variable', 'photo' => '1598033129183-c4f50c736f10'],

            // Footwear
            ['name' => 'Lightweight Performance Running Shoes', 'cat' => 'footwear-shoes', 'min' => 49, 'max' => 119, 'type' => 'variable', 'photo' => '1542291026-7eec264c27ff'],
            ['name' => 'Classic Genuine Leather Oxford Shoes', 'cat' => 'footwear-shoes', 'min' => 79, 'max' => 159, 'type' => 'variable', 'photo' => '1449505278894-297fdb3edbc1'],
            ['name' => 'Urban Canvas Low-Top Sneakers', 'cat' => 'footwear-shoes', 'min' => 29, 'max' => 59, 'type' => 'variable', 'photo' => '1542291026-7eec264c27ff'],
            ['name' => 'Handcrafted Woven Leather Sandals', 'cat' => 'footwear-shoes', 'min' => 35, 'max' => 75, 'type' => 'variable', 'photo' => '1603487742131-4160ec999306'],

            // Home & Kitchen
            ['name' => 'Aromatherapy Essential Oil Diffuser', 'cat' => 'home-kitchen', 'min' => 22, 'max' => 48, 'type' => 'simple', 'photo' => '1507473885765-e6ed057f782c'],
            ['name' => 'Stainless Steel Electric Pour-Over Kettle', 'cat' => 'home-kitchen', 'min' => 39, 'max' => 79, 'type' => 'simple', 'photo' => '1543512214-318c7553f230'],
            ['name' => 'Non-Stick Ceramic Frying Pan Set', 'cat' => 'home-kitchen', 'min' => 45, 'max' => 95, 'type' => 'simple', 'photo' => '1585386959984-a4155224a1ad'],
            ['name' => 'Memory Foam Ergonomic Sleep Pillow', 'cat' => 'home-kitchen', 'min' => 29, 'max' => 59, 'type' => 'variable', 'photo' => '1576871337622-98d48d1cf531'],
            ['name' => 'Minimalist Wooden Wall Clock', 'cat' => 'home-kitchen', 'min' => 19, 'max' => 42, 'type' => 'simple', 'photo' => '1507473885765-e6ed057f782c'],

            // Health & Beauty
            ['name' => 'Hydrating Vitamin C Face Serum', 'cat' => 'health-beauty', 'min' => 15, 'max' => 38, 'type' => 'simple', 'photo' => '1622445275576-721325763afe'],
            ['name' => 'Nourishing Organic Argan Hair Oil', 'cat' => 'health-beauty', 'min' => 18, 'max' => 35, 'type' => 'simple', 'photo' => '1622445275576-721325763afe'],
            ['name' => 'Matte Lip Gloss Set 6 Shades', 'cat' => 'health-beauty', 'min' => 12, 'max' => 28, 'type' => 'variable', 'photo' => '1622445275576-721325763afe'],
            ['name' => 'Sonic Facial Cleansing Brush', 'cat' => 'health-beauty', 'min' => 29, 'max' => 59, 'type' => 'simple', 'photo' => '1507473885765-e6ed057f782c'],

            // Sports & Fitness
            ['name' => 'Non-Slip Thick Exercise Yoga Mat', 'cat' => 'sports-outdoors', 'min' => 25, 'max' => 55, 'type' => 'variable', 'photo' => '1506629082955-511b1aa562c8'],
            ['name' => 'Heavy Duty Resistance Workout Bands', 'cat' => 'sports-outdoors', 'min' => 15, 'max' => 35, 'type' => 'simple', 'photo' => '1539185441755-9109504b204a'],
            ['name' => 'Insulated Stainless Steel Water Bottle 1L', 'cat' => 'sports-outdoors', 'min' => 18, 'max' => 38, 'type' => 'variable', 'photo' => '1539185441755-9109504b204a'],
            ['name' => 'Outdoor Waterproof Camping Backpack 45L', 'cat' => 'sports-outdoors', 'min' => 49, 'max' => 99, 'type' => 'variable', 'photo' => '1544816565-aa8c1166648f'],

            // Accessories
            ['name' => 'Polarized UV400 Designer Sunglasses', 'cat' => 'accessories-jewelry', 'min' => 25, 'max' => 69, 'type' => 'variable', 'photo' => '1585386959984-a4155224a1ad'],
            ['name' => 'Full-Grain Genuine Leather Wallet', 'cat' => 'accessories-jewelry', 'min' => 29, 'max' => 59, 'type' => 'variable', 'photo' => '1585386959984-a4155224a1ad'],
            ['name' => 'Canvas Everyday Messenger Bag', 'cat' => 'accessories-jewelry', 'min' => 35, 'max' => 75, 'type' => 'variable', 'photo' => '1544816565-aa8c1166648f'],
        ];

        $createdCount = 0;
        $categoriesBySlug = collect($categories)->keyBy('slug');

        for ($i = 1; $i <= $count; $i++) {
            // Select a template cyclically and append index to ensure uniqueness
            $templateIndex = ($i - 1) % count($productTemplates);
            $template      = $productTemplates[$templateIndex];

            // Variations in name for uniqueness when creating 100 items
            $suffixNumber  = ceil($i / count($productTemplates));
            $productName   = $template['name'] . ($suffixNumber > 1 ? " - Edition {$suffixNumber}" : "");
            $productSlug   = Str::slug($productName) . '-' . Str::random(5);

            $categorySlug  = $template['cat'];
            $categoryObj   = $categoriesBySlug->get($categorySlug) ?? $categories[array_rand($categories)];
            $brandObj      = $brands[array_rand($brands)];

            $photoId   = $template['photo'];
            $imageUrl  = "https://images.unsplash.com/photo-{$photoId}?w=600&h=600&fit=crop&auto=format&q=75";

            DB::beginTransaction();
            try {
                // Create Product
                $product = Product::create([
                    'shop_id'           => $shop->id,
                    'vendor_id'         => $vendor->id,
                    'category_id'       => $categoryObj->id,
                    'brand_id'          => $brandObj->id,
                    'name'              => $productName,
                    'slug'              => $productSlug,
                    'description'       => "High quality {$productName}. Designed for performance, reliability, and modern aesthetic.",
                    'short_description' => "Premium quality {$productName} with fast shipping and warranty.",
                    'tags'              => Str::slug($categoryObj->name) . ',' . Str::slug($brandObj->name) . ',seeded',
                    'product_type'      => $template['type'],
                    'status'            => 1,
                ]);

                // Create Product Image
                $product->images()->create([
                    'name'      => $productSlug . '.jpg',
                    'image_url' => $imageUrl,
                    'type'      => 'thumb',
                ]);

                // Create Variants
                if ($template['type'] === 'variable') {
                    // Seed 2 sizes x 2 colors = 4 variants
                    $sampleSizes  = $sizeValues->random(min(2, $sizeValues->count()));
                    $sampleColors = $colorValues->random(min(2, $colorValues->count()));
                    $isPrimary    = true;
                    $variantAttrBatch = [];

                    foreach ($sampleSizes as $size) {
                        foreach ($sampleColors as $color) {
                            $price         = rand($template['min'], $template['max']);
                            $discountPrice = rand(0, 100) > 30 ? round($price * rand(70, 90) / 100) : null;

                            $variant = $product->variants()->create([
                                'variant_slug'   => Str::slug("{$productName} {$size->value}-{$color->value}") . '-' . Str::random(5),
                                'name'           => "{$size->value} / {$color->value}",
                                'price'          => $price,
                                'discount_price' => $discountPrice,
                                'stock'          => rand(10, 150),
                                'SKU'            => strtoupper(Str::random(3)) . rand(1000, 9999),
                                'weight'         => round(rand(2, 30) / 10, 1),
                                'dimensions'     => rand(10, 30) . 'x' . rand(10, 30) . 'x' . rand(2, 10) . ' cm',
                                'is_primary'     => (bool) $isPrimary,
                            ]);

                            $isPrimary = false;

                            foreach ([$size->id, $color->id] as $attrValId) {
                                $variantAttrBatch[] = [
                                    'product_id'         => $product->id,
                                    'product_variant_id' => $variant->id,
                                    'attribute_value_id' => $attrValId,
                                    'created_at'         => now(),
                                    'updated_at'         => now(),
                                ];
                            }
                        }
                    }

                    if (!empty($variantAttrBatch)) {
                        DB::table('product_variant_attribute_values')->insert($variantAttrBatch);
                    }

                    // Attach attribute values to product once
                    $prodAttrBatch = [];
                    foreach ($sampleSizes->pluck('id')->merge($sampleColors->pluck('id'))->unique() as $attrValId) {
                        $prodAttrBatch[] = [
                            'product_id'         => $product->id,
                            'attribute_value_id' => $attrValId,
                        ];
                    }
                    if (!empty($prodAttrBatch)) {
                        DB::table('product_attribute_values')->insertOrIgnore($prodAttrBatch);
                    }
                } else {
                    // Simple product single primary variant
                    $price         = rand($template['min'], $template['max']);
                    $discountPrice = rand(0, 100) > 30 ? round($price * rand(75, 90) / 100) : null;

                    $product->variants()->create([
                        'variant_slug'   => $productSlug . '-default',
                        'name'           => 'Default',
                        'price'          => $price,
                        'discount_price' => $discountPrice,
                        'stock'          => rand(20, 200),
                        'SKU'            => strtoupper(Str::random(3)) . rand(1000, 9999),
                        'weight'         => round(rand(2, 30) / 10, 1),
                        'dimensions'     => rand(10, 30) . 'x' . rand(10, 30) . 'x' . rand(2, 10) . ' cm',
                        'is_primary'     => true,
                    ]);
                }

                DB::commit();
                $createdCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $createdCount;
    }
}
