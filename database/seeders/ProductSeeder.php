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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private function downloadImage(string $unsplashId, string $localPath, string $productName): string
    {
        $url = "https://images.unsplash.com/photo-{$unsplashId}?w=600&h=600&fit=crop&auto=format&q=75";
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $data = @file_get_contents($url, false, $ctx);
            if ($data !== false) {
                Storage::disk('public')->put($localPath, $data);
                return $localPath;
            }
        } catch (\Throwable $e) {
        }
        try {
            $fallback = 'https://placehold.co/600x600/EEE/31343C/png?text=' . urlencode($productName);
            $ctx  = stream_context_create(['http' => ['timeout' => 2]]);
            $data = @file_get_contents($fallback, false, $ctx);
            if ($data !== false) {
                Storage::disk('public')->put($localPath, $data);
                return $localPath;
            }
        } catch (\Throwable $e) {
        }
        return $localPath;
    }

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
        $vendor = Vendor::first() ?? Vendor::create([
            'name'     => 'Default Vendor',
            'email'    => 'vendor@example.com',
            'password' => bcrypt('password'),
            'status'   => 1,
        ]);

        $shop = \App\Models\Shop::first() ?? \App\Models\Shop::create([
            'vendor_id'   => $vendor->id,
            'name'        => 'Default Shop',
            'description' => 'The default store shop.',
            'status'      => 'active',
        ]);
        $categories  = Category::whereIn('slug', ['electronics','fashion','smartphones','t-shirts'])->get()->keyBy('slug');
        $electronics = $categories->get('electronics') ?? Category::first();
        $fashion     = $categories->get('fashion')     ?? Category::first();
        $smartphones = $categories->get('smartphones') ?? $electronics;
        $tshirts     = $categories->get('t-shirts')    ?? $fashion;

        $brand = Brand::first();

        $sizeValues  = AttributeValue::where('attribute_id', $sizeAttr->id)->get();
        $colorValues = AttributeValue::where('attribute_id', $colorAttr->id)->get();
            $products = [
                [
                    'name' => 'Wireless Headphones Pro',
                    'short' => 'Noise-cancelling over-ear headphones with 40-hour battery.',
                    'tags' => 'headphones,audio,wireless',
                    'category' => $electronics, 'min' => 49, 'max' => 89,
                    'photo' => '1505740420928-5e560c06d30e',
                ],
                [
                    'name' => 'Bluetooth Speaker Mini',
                    'short' => 'Portable waterproof speaker with 360° surround sound.',
                    'tags' => 'speaker,bluetooth,portable',
                    'category' => $electronics, 'min' => 25, 'max' => 55,
                    'photo' => '1608043152269-423dbba4e7e1',
                ],
                [
                    'name' => 'Smart Watch Series X',
                    'short' => 'Heart-rate monitor, GPS, and 7-day battery life.',
                    'tags' => 'smartwatch,fitness,wearable',
                    'category' => $electronics, 'min' => 79, 'max' => 149,
                    'photo' => '1523275335684-37898b6baf30',
                ],
                [
                    'name' => 'USB-C Hub 7-in-1',
                    'short' => 'Expands your laptop with HDMI, USB-A, SD card and more.',
                    'tags' => 'hub,usb-c,accessories',
                    'category' => $electronics, 'min' => 20, 'max' => 45,
                    'photo' => '1593642632559-0c6d3fc62b89',
                ],
                [
                    'name' => 'Mechanical Gaming Keyboard',
                    'short' => 'RGB backlit keyboard with tactile blue switches.',
                    'tags' => 'keyboard,gaming,rgb',
                    'category' => $electronics, 'min' => 55, 'max' => 99,
                    'photo' => '1587829741301-dc798b83add3',
                ],
                [
                    'name' => 'Wireless Gaming Mouse',
                    'short' => 'Ultra-lightweight 2.4GHz mouse with 16000 DPI sensor.',
                    'tags' => 'mouse,gaming,wireless',
                    'category' => $electronics, 'min' => 35, 'max' => 75,
                    'photo' => '1615663245857-ac93bb7c39e7',
                ],
                [
                    'name' => '27-inch Curved Monitor',
                    'short' => 'QHD 144Hz curved display perfect for gaming and work.',
                    'tags' => 'monitor,display,gaming',
                    'category' => $electronics, 'min' => 199, 'max' => 349,
                    'photo' => '1527443224154-c4a3942d3acf',
                ],
                [
                    'name' => 'Noise-Isolating Earbuds',
                    'short' => 'True wireless earbuds with active noise isolation.',
                    'tags' => 'earbuds,tws,audio',
                    'category' => $electronics, 'min' => 29, 'max' => 69,
                    'photo' => '1590658268037-6bf12165a8df',
                ],
                [
                    'name' => 'Portable Power Bank 20000mAh',
                    'short' => 'Fast-charge power bank with dual USB-A and USB-C output.',
                    'tags' => 'powerbank,charging,portable',
                    'category' => $electronics, 'min' => 22, 'max' => 48,
                    'photo' => '1609091839311-d5365f9ff1c5',
                ],
                [
                    'name' => 'HD Webcam 1080p',
                    'short' => 'Wide-angle webcam with built-in stereo microphone.',
                    'tags' => 'webcam,video,streaming',
                    'category' => $electronics, 'min' => 30, 'max' => 60,
                    'photo' => '1593642634402-b0eb5e2eebc9',
                ],
                [
                    'name' => 'Smart LED Desk Lamp',
                    'short' => 'Dimmable desk lamp with wireless charging base.',
                    'tags' => 'lamp,smart,desk',
                    'category' => $electronics, 'min' => 28, 'max' => 55,
                    'photo' => '1507473885765-e6ed057f782c',
                ],
                [
                    'name' => 'Wireless Charging Pad',
                    'short' => '15W Qi-certified pad compatible with all Qi devices.',
                    'tags' => 'charger,wireless,qi',
                    'category' => $electronics, 'min' => 15, 'max' => 35,
                    'photo' => '1586953208448-b95a79798f07',
                ],
                [
                    'name' => 'Digital Drawing Tablet',
                    'short' => 'A4-sized pen tablet with 8192 levels of pressure sensitivity.',
                    'tags' => 'tablet,drawing,art',
                    'category' => $electronics, 'min' => 60, 'max' => 110,
                    'photo' => '1611532736597-de2d4265fba3',
                ],
                [
                    'name' => 'Portable SSD 1TB',
                    'short' => 'Ultra-fast 1050MB/s external SSD in a rugged aluminium casing.',
                    'tags' => 'ssd,storage,portable',
                    'category' => $electronics, 'min' => 80, 'max' => 130,
                    'photo' => '1597872200969-2b65d56bd16b',
                ],
                [
                    'name' => 'Smart Home Speaker',
                    'short' => 'Voice-controlled speaker with built-in smart-home hub.',
                    'tags' => 'smart-home,speaker,voice',
                    'category' => $electronics, 'min' => 45, 'max' => 90,
                    'photo' => '1543512214-318c7553f230',
                ],
                [
                    'name' => 'Galaxy Lite 5G',
                    'short' => '6.4-inch AMOLED, 5000mAh battery and triple-camera system.',
                    'tags' => 'smartphone,5g,android',
                    'category' => $smartphones, 'min' => 199, 'max' => 299,
                    'photo' => '1510557880182-3d4d3cba35a5',
                ],
                [
                    'name' => 'ProPhone Ultra',
                    'short' => '6.7-inch ProMotion display with 200MP periscope camera.',
                    'tags' => 'smartphone,flagship,camera',
                    'category' => $smartphones, 'min' => 399, 'max' => 599,
                    'photo' => '1574944985070-8f3ebc6b79d2',
                ],
                [
                    'name' => 'Budget Smartphone A20',
                    'short' => 'Solid mid-ranger with 90Hz display and 5000mAh battery.',
                    'tags' => 'smartphone,budget,android',
                    'category' => $smartphones, 'min' => 99, 'max' => 149,
                    'photo' => '1585060544812-6b45742d762f',
                ],
                [
                    'name' => 'Foldable Phone Z3',
                    'short' => 'Compact clamshell foldable with 6.7-inch inner display.',
                    'tags' => 'smartphone,foldable,premium',
                    'category' => $smartphones, 'min' => 599, 'max' => 799,
                    'photo' => '1598327105854-e4ee1e4f2b17',
                ],
                [
                    'name' => 'Rugged Phone X-Pro',
                    'short' => 'IP68-rated rugged phone with thermal imaging camera.',
                    'tags' => 'smartphone,rugged,outdoor',
                    'category' => $smartphones, 'min' => 249, 'max' => 349,
                    'photo' => '1601784551446-20c326ef1a5d',
                ],
                [
                    'name' => 'Gaming Phone Turbo',
                    'short' => '144Hz gaming display with active cooling and 6800mAh battery.',
                    'tags' => 'smartphone,gaming,performance',
                    'category' => $smartphones, 'min' => 299, 'max' => 449,
                    'photo' => '1531297484001-80022131f5a1',
                ],
                [
                    'name' => 'Slim Phone Air',
                    'short' => 'Ultra-slim 5.9mm profile with wireless charging support.',
                    'tags' => 'smartphone,slim,design',
                    'category' => $smartphones, 'min' => 149, 'max' => 249,
                    'photo' => '1567581935884-3349723552ca',
                ],
                [
                    'name' => 'Senior-Friendly Phone',
                    'short' => 'Large icons, loud speaker and SOS button for elder users.',
                    'tags' => 'smartphone,senior,simple',
                    'category' => $smartphones, 'min' => 59, 'max' => 99,
                    'photo' => '1512499617640-c74ae3a79d37',
                ],
                [
                    'name' => 'Camera Phone Snap 12',
                    'short' => '50MP main sensor with optical zoom and pro-mode controls.',
                    'tags' => 'smartphone,camera,photography',
                    'category' => $smartphones, 'min' => 179, 'max' => 279,
                    'photo' => '1592899677977-9c10002761d5',
                ],
                [
                    'name' => 'Dual SIM Business Phone',
                    'short' => 'Dual SIM, long battery, and enterprise-grade security chip.',
                    'tags' => 'smartphone,business,dual-sim',
                    'category' => $smartphones, 'min' => 129, 'max' => 199,
                    'photo' => '1556656793-08538906a9f8',
                ],

                // ── T-Shirts (10) ─────────────────────────────────────────────
                [
                    'name' => 'Classic White Tee',
                    'short' => '100% organic cotton crew-neck t-shirt.',
                    'tags' => 'tshirt,cotton,basics',
                    'category' => $tshirts, 'min' => 12, 'max' => 25,
                    'photo' => '1521572163474-6864f9cf17ab',
                ],
                [
                    'name' => 'Vintage Graphic Tee',
                    'short' => 'Retro-print tee made from heavyweight soft cotton.',
                    'tags' => 'tshirt,graphic,vintage',
                    'category' => $tshirts, 'min' => 15, 'max' => 30,
                    'photo' => '1576566588028-4147f3842f27',
                ],
                [
                    'name' => 'Polo Shirt Essential',
                    'short' => 'Breathable piqué polo perfect for casual and semi-formal wear.',
                    'tags' => 'polo,shirt,casual',
                    'category' => $tshirts, 'min' => 18, 'max' => 35,
                    'photo' => '1598033129183-c4f50c736f10',
                ],
                [
                    'name' => 'Oversized Drop Shoulder Tee',
                    'short' => 'Relaxed-fit oversized tee in premium washed cotton.',
                    'tags' => 'tshirt,oversized,streetwear',
                    'category' => $tshirts, 'min' => 16, 'max' => 32,
                    'photo' => '1618354691373-d851c5c3a990',
                ],
                [
                    'name' => 'Striped Long-Sleeve Shirt',
                    'short' => 'Breton-stripe long-sleeve shirt in soft jersey fabric.',
                    'tags' => 'shirt,stripes,long-sleeve',
                    'category' => $tshirts, 'min' => 18, 'max' => 34,
                    'photo' => '1602810318383-e386cc2a3ccf',
                ],
                [
                    'name' => 'Performance Dry-Fit Tee',
                    'short' => 'Moisture-wicking sports tee with UV protection.',
                    'tags' => 'tshirt,sport,drifit',
                    'category' => $tshirts, 'min' => 14, 'max' => 28,
                    'photo' => '1539710028830-b12871fcce6c',
                ],
                [
                    'name' => 'Linen Blend Summer Shirt',
                    'short' => 'Lightweight linen-blend shirt for hot weather.',
                    'tags' => 'shirt,linen,summer',
                    'category' => $tshirts, 'min' => 20, 'max' => 38,
                    'photo' => '1604644401890-0bd678c83788',
                ],
                [
                    'name' => 'Tie-Dye Boho Tee',
                    'short' => 'Hand-dyed tie-dye tee with a unique pattern every time.',
                    'tags' => 'tshirt,tiedye,boho',
                    'category' => $tshirts, 'min' => 14, 'max' => 26,
                    'photo' => '1622445275576-721325763afe',
                ],
                [
                    'name' => 'Business Slim-Fit Shirt',
                    'short' => 'Non-iron slim-fit shirt for a sharp office look.',
                    'tags' => 'shirt,business,slim-fit',
                    'category' => $tshirts, 'min' => 25, 'max' => 45,
                    'photo' => '1521336575822-6da63fb45455',
                ],
                [
                    'name' => 'Henley Button Tee',
                    'short' => 'Three-button henley tee in a classic slim-fit cut.',
                    'tags' => 'henley,tshirt,casual',
                    'category' => $tshirts, 'min' => 14, 'max' => 28,
                    'photo' => '1583743814966-8936f5b7be1a',
                ],
                [
                    'name' => 'Slim Chino Pants',
                    'short' => 'Stretch-cotton chino pants in a modern slim fit.',
                    'tags' => 'pants,chino,fashion',
                    'category' => $fashion, 'min' => 25, 'max' => 55,
                    'photo' => '1473966968600-fa801b869a1a',
                ],
                [
                    'name' => 'High-Rise Skinny Jeans',
                    'short' => 'High-waist skinny jeans with stretch denim fabric.',
                    'tags' => 'jeans,denim,fashion',
                    'category' => $fashion, 'min' => 30, 'max' => 60,
                    'photo' => '1542272604-787c3835535d',
                ],
                [
                    'name' => 'Hooded Zip-Up Sweatshirt',
                    'short' => 'French terry zip-up hoodie with kangaroo pocket.',
                    'tags' => 'hoodie,sweatshirt,casual',
                    'category' => $fashion, 'min' => 28, 'max' => 55,
                    'photo' => '1556821840-3a63f8550908',
                ],
                [
                    'name' => 'Leather Belt Classic',
                    'short' => 'Full-grain leather belt with antique-brass buckle.',
                    'tags' => 'belt,leather,accessories',
                    'category' => $fashion, 'min' => 15, 'max' => 35,
                    'photo' => '1585386959984-a4155224a1ad',
                ],
                [
                    'name' => 'Canvas Sneakers',
                    'short' => 'Low-top canvas sneakers with rubber vulcanised sole.',
                    'tags' => 'sneakers,shoes,casual',
                    'category' => $fashion, 'min' => 20, 'max' => 45,
                    'photo' => '1542291026-7eec264c27ff',
                ],
                [
                    'name' => 'Leather Oxford Shoes',
                    'short' => 'Hand-stitched genuine leather oxfords for formal occasions.',
                    'tags' => 'shoes,leather,formal',
                    'category' => $fashion, 'min' => 55, 'max' => 110,
                    'photo' => '1449505278894-297fdb3edbc1',
                ],
                [
                    'name' => 'Sport Running Shorts',
                    'short' => 'Lightweight 2-in-1 running shorts with compression liner.',
                    'tags' => 'shorts,sport,running',
                    'category' => $fashion, 'min' => 15, 'max' => 30,
                    'photo' => '1539185441755-9109504b204a',
                ],
                [
                    'name' => 'Bomber Jacket',
                    'short' => 'Classic MA-1 bomber jacket with ribbed cuffs and collar.',
                    'tags' => 'jacket,bomber,outerwear',
                    'category' => $fashion, 'min' => 50, 'max' => 90,
                    'photo' => '1591047139829-d91aecb6caea',
                ],
                [
                    'name' => 'Knit Beanie Hat',
                    'short' => 'Soft-stretch rib-knit beanie in 12 available colours.',
                    'tags' => 'hat,beanie,accessories',
                    'category' => $fashion, 'min' => 8, 'max' => 18,
                    'photo' => '1576871337622-98d48d1cf531',
                ],
                [
                    'name' => 'Canvas Tote Bag',
                    'short' => 'Heavy-duty canvas tote with internal laptop sleeve.',
                    'tags' => 'bag,tote,canvas',
                    'category' => $fashion, 'min' => 12, 'max' => 28,
                    'photo' => '1544816565-aa8c1166648f',
                ],
                [
                    'name' => 'Workout Leggings',
                    'short' => 'High-waist compression leggings with side pocket.',
                    'tags' => 'leggings,sport,fitness',
                    'category' => $fashion, 'min' => 18, 'max' => 38,
                    'photo' => '1506629082955-511b1aa562c8',
                ],
                [
                    'name' => 'Denim Jacket',
                    'short' => 'Classic unlined denim jacket with chest pockets.',
                    'tags' => 'jacket,denim,casual',
                    'category' => $fashion, 'min' => 35, 'max' => 70,
                    'photo' => '1598522105502-a8acdf5e3df4',
                ],
                [
                    'name' => 'Quilted Vest',
                    'short' => 'Lightweight quilted gilet ideal for layering.',
                    'tags' => 'vest,quilted,layering',
                    'category' => $fashion, 'min' => 28, 'max' => 55,
                    'photo' => '1548036328-c9fa89d128fa',
                ],
                [
                    'name' => 'Woven Leather Sandals',
                    'short' => 'Handcrafted woven-strap sandals with cushioned footbed.',
                    'tags' => 'sandals,leather,summer',
                    'category' => $fashion, 'min' => 22, 'max' => 45,
                    'photo' => '1603487742131-4160ec999306',
                ],
                [
                    'name' => 'Puffer Winter Coat',
                    'short' => 'Water-resistant puffer coat with detachable hood.',
                    'tags' => 'coat,winter,outerwear',
                    'category' => $fashion, 'min' => 65, 'max' => 120,
                    'photo' => '1547949003-9792a18a2601',
                ],
            ];
            foreach ($products as $item) {
                $slug = Str::slug($item['name']);
                $existing = Product::where('slug', $slug)->first();

                $imageName = $slug . '.jpg';
                $localPath = 'products/' . $imageName;
                $this->downloadImage($item['photo'], $localPath, $item['name']);

                if ($existing) {
                    $existing->images()->updateOrCreate(
                        ['type' => 'thumb'],
                        ['name' => $imageName, 'image_url' => $localPath]
                    );
                    continue;
                }

                $product = Product::create([
                    'shop_id'           => $shop->id,
                    'vendor_id'         => $vendor->id,
                    'slug'              => $slug,
                    'name'              => $item['name'],
                    'description'       => $item['short'],
                    'short_description' => $item['short'],
                    'tags'              => $item['tags'],
                    'category_id'       => $item['category']->id,
                    'brand_id'          => $brand?->id,
                    'product_type'      => 'variable',
                    'status'            => 1,
                ]);

                $product->images()->create([
                    'name'      => $imageName,
                    'image_url' => $localPath,
                    'type'      => 'thumb',
                ]);
                $sizeSample  = $sizeValues->count() > 0 ? $sizeValues->random(min(2, $sizeValues->count())) : collect();
                $colorSample = $colorValues->count() > 0 ? $colorValues->random(min(2, $colorValues->count())) : collect();
                $isPrimary   = true;

                foreach ($sizeSample as $size) {
                    foreach ($colorSample as $color) {
                        $vPrice    = rand($item['min'], $item['max']);
                        $vDiscount = round($vPrice * rand(60, 90) / 100);

                        $variant = $product->variants()->create([
                            'variant_slug'   => Str::slug("{$item['name']} {$size->value}-{$color->value}") . '-' . Str::random(5),
                            'name'           => "{$size->value} / {$color->value}",
                            'price'          => $vPrice,
                            'discount_price' => $vDiscount,
                            'stock'          => rand(10, 200),
                            'SKU'            => strtoupper(Str::random(2)) . strtoupper(substr($size->value, 0, 1)) . strtoupper(substr($color->value, 0, 2)) . rand(100, 999),
                            'weight'         => round(rand(1, 20) / 10, 1),
                            'dimensions'     => rand(5, 30) . 'x' . rand(5, 30) . 'x' . rand(1, 10) . ' cm',
                            'is_primary'     => $isPrimary ? 1 : 0,
                        ]);

                        $isPrimary = false;

                        foreach ([$size->id, $color->id] as $attrValueId) {
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
            }
    }
}
