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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private string $imgbbKey = '3cd2fcb26177aa50d6e37f81dec037d3';

    /**
     * Uploads the Unsplash image directly to ImgBB and returns the hosted URL.
     */
    private function uploadToImgBB(string $unsplashId, string $productName): string
    {
        $unsplashUrl = "https://images.unsplash.com/photo-{$unsplashId}?w=600&h=600&fit=crop&auto=format&q=75";

        try {
            $response = Http::asForm()->timeout(15)->post('https://api.imgbb.com/1/upload', [
                'key'   => $this->imgbbKey,
                'image' => $unsplashUrl,
                'name'  => Str::slug($productName),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data']['url'])) {
                    return $data['data']['url'];
                }
            }
        } catch (\Throwable $e) {
            // Fall back to placeholder if ImgBB upload fails or times out
        }

        // Fall back to direct Unsplash CDN URL if ImgBB upload fails or times out
        return $unsplashUrl;
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
            'status'   => 'active',
        ]);

        $fashion = Category::firstOrCreate(['slug' => 'fashion'], ['name' => 'Fashion & Apparel', 'status' => 1]);
        $shoes   = Category::firstOrCreate(['slug' => 'shoes'], ['name' => 'Shoes & Footwear', 'status' => 1]);
        $beauty  = Category::firstOrCreate(['slug' => 'beauty'], ['name' => 'Beauty & Skincare', 'status' => 1]);

        $brand = Brand::first();

        $sizeValues  = AttributeValue::where('attribute_id', $sizeAttr->id)->get();
        $colorValues = AttributeValue::where('attribute_id', $colorAttr->id)->get();

        $products = [
            // =========================================================================
            // FASHION (100 Products)
            // =========================================================================
            ['name' => 'Classic Black Hoodie', 'short' => 'Heavyweight fleece hoodie with ribbed cuffs.', 'tags' => 'fashion,hoodie,streetwear', 'category' => $fashion, 'min' => 35, 'max' => 65, 'photo' => '1556905055-8f358a7a47b2'],
            ['name' => 'Grey Crewneck Sweatshirt', 'short' => 'Soft brushed interior casual crewneck.', 'tags' => 'fashion,sweatshirt,casual', 'category' => $fashion, 'min' => 25, 'max' => 50, 'photo' => '1578587018452-892bacefd3f2'],
            ['name' => 'Classic Denim Jacket', 'short' => 'Vintage wash denim jacket with metal button closures.', 'tags' => 'fashion,denim,jacket', 'category' => $fashion, 'min' => 45, 'max' => 85, 'photo' => '1576995853123-5a10305d93c0'],
            ['name' => 'Leather Biker Jacket', 'short' => 'Asymmetric zip moto jacket with snap lapels.', 'tags' => 'fashion,leather,biker', 'category' => $fashion, 'min' => 110, 'max' => 220, 'photo' => '1551028719-00167b16eac5'],
            ['name' => 'Puffer Winter Coat', 'short' => 'Insulated quilted long winter coat.', 'tags' => 'fashion,winter,puffer', 'category' => $fashion, 'min' => 80, 'max' => 160, 'photo' => '1544441893-675973e31985'],
            ['name' => 'Quilted Down Vest', 'short' => 'Sleeveless lightweight warmth layer.', 'tags' => 'fashion,vest,outerwear', 'category' => $fashion, 'min' => 35, 'max' => 70, 'photo' => '1516257984-b1b4d707412e'],
            ['name' => 'Bomber Jacket Olive', 'short' => 'MA-1 military style zip-up bomber.', 'tags' => 'fashion,bomber,military', 'category' => $fashion, 'min' => 50, 'max' => 95, 'photo' => '1591047139829-d91aecb6caea'],
            ['name' => 'Camel Trench Coat', 'short' => 'Double-breasted storm-flap tailored trench.', 'tags' => 'fashion,coat,formal', 'category' => $fashion, 'min' => 95, 'max' => 180, 'photo' => '1539533113208-f6df8cc8b543'],
            ['name' => 'Tailored Black Blazer', 'short' => 'Modern slim cut single-breasted suit blazer.', 'tags' => 'fashion,blazer,formal', 'category' => $fashion, 'min' => 70, 'max' => 140, 'photo' => '1507679799987-c73779587ccf'],
            ['name' => 'Slim Oxford Cotton Shirt', 'short' => 'Breathable pure cotton button-down shirt.', 'tags' => 'fashion,shirt,office', 'category' => $fashion, 'min' => 28, 'max' => 55, 'photo' => '1602810318383-e386cc2a3ccf'],
            ['name' => 'Casual Flannel Plaid Shirt', 'short' => 'Brushed cotton winter flannel button-up.', 'tags' => 'fashion,flannel,casual', 'category' => $fashion, 'min' => 30, 'max' => 60, 'photo' => '1626497764746-6dc36546b388'],
            ['name' => 'Striped Breton Long Sleeve', 'short' => 'Nautical inspired jersey cotton top.', 'tags' => 'fashion,stripes,basics', 'category' => $fashion, 'min' => 20, 'max' => 40, 'photo' => '1523381294911-8d3cead13475'],
            ['name' => 'Organic White Crew Tee', 'short' => 'Pre-shrunk regular fit combed cotton tee.', 'tags' => 'fashion,tshirt,basics', 'category' => $fashion, 'min' => 14, 'max' => 28, 'photo' => '1521572267360-ee0c2909d518'],
            ['name' => 'Vintage Distressed Graphic Tee', 'short' => 'Screen-printed faded retro graphic tee.', 'tags' => 'fashion,graphic,vintage', 'category' => $fashion, 'min' => 18, 'max' => 36, 'photo' => '1503342217505-b0a15ec3261c'],
            ['name' => 'Pique Knit Polo Shirt', 'short' => 'Collared classic tennis polo with ribbed armbands.', 'tags' => 'fashion,polo,preppy', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1625910513413-5a210d56fb6e'],
            ['name' => 'Three-Button Henley Tee', 'short' => 'Textured waffle-knit long sleeve henley.', 'tags' => 'fashion,henley,casual', 'category' => $fashion, 'min' => 22, 'max' => 45, 'photo' => '1521572163474-6864f9cf17ab'],
            ['name' => 'Linen Blend Resort Shirt', 'short' => 'Open camp collar breathable tropical shirt.', 'tags' => 'fashion,linen,summer', 'category' => $fashion, 'min' => 32, 'max' => 64, 'photo' => '1596755094514-f87e34085b2c'],
            ['name' => 'Chunky Cable-Knit Sweater', 'short' => 'Thick wool blend traditional fisherman jumper.', 'tags' => 'fashion,knitwear,winter', 'category' => $fashion, 'min' => 45, 'max' => 90, 'photo' => '1583743814966-8936f5b7be1a'],
            ['name' => 'Merino Wool Turtleneck', 'short' => 'Fine gauge seamless roll-neck pullover.', 'tags' => 'fashion,sweater,merino', 'category' => $fashion, 'min' => 50, 'max' => 100, 'photo' => '1434389677669-e08b4cac3105'],
            ['name' => 'Cashmere Open Front Cardigan', 'short' => 'Luxurious drape-front soft winter cardigan.', 'tags' => 'fashion,cardigan,cashmere', 'category' => $fashion, 'min' => 75, 'max' => 150, 'photo' => '1485968579580-b6d095142e6e'],
            ['name' => 'Performance Dry-Fit Top', 'short' => 'Athletic stretch moisture-wicking training tee.', 'tags' => 'fashion,activewear,gym', 'category' => $fashion, 'min' => 18, 'max' => 35, 'photo' => '1581655353564-df123a1eb820'],
            ['name' => 'Windbreaker Packable Jacket', 'short' => 'Water-repellent hooded lightweight running jacket.', 'tags' => 'fashion,windbreaker,active', 'category' => $fashion, 'min' => 38, 'max' => 75, 'photo' => '1548883354-7622d03aca27'],
            ['name' => 'Fleece Half-Zip Pullover', 'short' => 'Sherpa fleece warm hiking outerwear.', 'tags' => 'fashion,fleece,winter', 'category' => $fashion, 'min' => 36, 'max' => 72, 'photo' => '1515886657613-9f3515b0c78f'],
            ['name' => 'Wool Peacoat Navy', 'short' => 'Traditional double-breasted naval outer coat.', 'tags' => 'fashion,peacoat,outerwear', 'category' => $fashion, 'min' => 90, 'max' => 180, 'photo' => '1539109136881-3be0616acf4b'],
            ['name' => 'Corduroy Overshirt', 'short' => 'Heavy wale textured button-up jacket shirt.', 'tags' => 'fashion,corduroy,layering', 'category' => $fashion, 'min' => 38, 'max' => 75, 'photo' => '1516762689617-e1cffcef479d'],
            ['name' => 'Slim Chino Trousers', 'short' => 'Tailored flat-front cotton stretch casual chinos.', 'tags' => 'fashion,chinos,pants', 'category' => $fashion, 'min' => 32, 'max' => 65, 'photo' => '1473966968600-fa801b869a1a'],
            ['name' => 'High-Rise Skinny Denim', 'short' => 'Stretch sculpting ankle denim jeans.', 'tags' => 'fashion,jeans,denim', 'category' => $fashion, 'min' => 36, 'max' => 75, 'photo' => '1541099649105-f69ad21f3246'],
            ['name' => 'Straight Leg Selvedge Jeans', 'short' => 'Japanese raw indigo cotton heritage denim.', 'tags' => 'fashion,denim,selvedge', 'category' => $fashion, 'min' => 65, 'max' => 130, 'photo' => '1582552938357-32b906dfca0f'],
            ['name' => 'Relaxed Cargo Utility Pants', 'short' => 'Multi-pocket tactical durable ripstop pants.', 'tags' => 'fashion,cargo,streetwear', 'category' => $fashion, 'min' => 40, 'max' => 80, 'photo' => '1517445312882-bc9910d016b7'],
            ['name' => 'Fleece Cuffed Joggers', 'short' => 'Tapered lounge sweatpants with drawstring.', 'tags' => 'fashion,joggers,lounge', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1552902865-b72c031ac5ea'],
            ['name' => 'Compression Running Shorts', 'short' => 'Split leg 5-inch shorts with inner liner.', 'tags' => 'fashion,shorts,running', 'category' => $fashion, 'min' => 20, 'max' => 40, 'photo' => '1591195853828-11db59a44f6b'],
            ['name' => 'Seamless Workout Leggings', 'short' => 'Squat-proof high-waisted gym tights.', 'tags' => 'fashion,leggings,activewear', 'category' => $fashion, 'min' => 26, 'max' => 52, 'photo' => '1506629082955-511b1aa562c8'],
            ['name' => 'Wide-Leg Linen Pants', 'short' => 'Breezy elastic-waist summer vacation trousers.', 'tags' => 'fashion,linen,summer', 'category' => $fashion, 'min' => 34, 'max' => 68, 'photo' => '1509631179647-0177331693ae'],
            ['name' => 'Pleated A-Line Mini Skirt', 'short' => 'Structured woven tennis-style skirt.', 'tags' => 'fashion,skirt,preppy', 'category' => $fashion, 'min' => 22, 'max' => 45, 'photo' => '1583496661160-fb5886a0aaaa'],
            ['name' => 'Faux Leather Midi Skirt', 'short' => 'Glossy fitted pencil skirt with rear split.', 'tags' => 'fashion,skirt,leather', 'category' => $fashion, 'min' => 35, 'max' => 70, 'photo' => '1574201635302-388dd92a4c3f'],
            ['name' => 'Floral Bohemian Maxi Dress', 'short' => 'Tiered flowing silhouette summer dress.', 'tags' => 'fashion,dress,boho', 'category' => $fashion, 'min' => 48, 'max' => 95, 'photo' => '1572804013309-59a88b7e92f1'],
            ['name' => 'Cocktail Slip Dress', 'short' => 'Satin bias-cut evening dress with cowl neckline.', 'tags' => 'fashion,dress,evening', 'category' => $fashion, 'min' => 55, 'max' => 110, 'photo' => '1566174053879-31528523f8ae'],
            ['name' => 'Wrap Front Midi Dress', 'short' => 'Adjustable belted waist polka print dress.', 'tags' => 'fashion,dress,casual', 'category' => $fashion, 'min' => 42, 'max' => 84, 'photo' => '1515372039744-b8f02a3ae446'],
            ['name' => 'Casual Linen Jumpsuit', 'short' => 'Cropped wide-leg utility jumpsuit.', 'tags' => 'fashion,jumpsuit,linen', 'category' => $fashion, 'min' => 46, 'max' => 92, 'photo' => '1529139574466-a303027c1d8b'],
            ['name' => 'Washed Cotton Overalls', 'short' => 'Vintage straight bib dungarees.', 'tags' => 'fashion,overalls,denim', 'category' => $fashion, 'min' => 48, 'max' => 96, 'photo' => '1578932750294-f5075e85f44a'],
            ['name' => 'Heavyweight Canvas Tote', 'short' => 'Reinforced seams utility grocery shoulder bag.', 'tags' => 'fashion,tote,canvas', 'category' => $fashion, 'min' => 16, 'max' => 32, 'photo' => '1544816155-12df9643f363'],
            ['name' => 'Structured Leather Crossbody', 'short' => 'Magnetic clasp pebble-grain shoulder purse.', 'tags' => 'fashion,bag,leather', 'category' => $fashion, 'min' => 60, 'max' => 125, 'photo' => '1590874103328-eac38a683ce7'],
            ['name' => 'Waterproof Commuter Backpack', 'short' => 'Laptop rolltop minimalist daily backpack.', 'tags' => 'fashion,backpack,commute', 'category' => $fashion, 'min' => 45, 'max' => 90, 'photo' => '1553062407-98eeb64c6a62'],
            ['name' => 'Full-Grain Leather Belt', 'short' => 'Handcrafted solid leather brass buckle belt.', 'tags' => 'fashion,belt,leather', 'category' => $fashion, 'min' => 20, 'max' => 40, 'photo' => '1624222247344-550fb60583dc'],
            ['name' => 'Slim RFID Blocking Wallet', 'short' => 'Bifold premium leather pocket cardholder.', 'tags' => 'fashion,wallet,accessories', 'category' => $fashion, 'min' => 22, 'max' => 45, 'photo' => '1627123424574-724758594e93'],
            ['name' => 'Polarized Metal Aviators', 'short' => 'Classic teardrop frame anti-glare sunglasses.', 'tags' => 'fashion,eyewear,sunglasses', 'category' => $fashion, 'min' => 30, 'max' => 65, 'photo' => '1511499767150-a48a237f0083'],
            ['name' => 'Chunky Ribbed Knit Beanie', 'short' => 'Fold-over cuff snug acrylic warm winter cap.', 'tags' => 'fashion,beanie,hat', 'category' => $fashion, 'min' => 12, 'max' => 25, 'photo' => '1576871337622-98d48d1cf531'],
            ['name' => 'Cotton Canvas Bucket Hat', 'short' => 'Wide stitched brim 90s retro sun protection hat.', 'tags' => 'fashion,buckethat,casual', 'category' => $fashion, 'min' => 15, 'max' => 30, 'photo' => '1588850561407-ed78c282e89b'],
            ['name' => 'Soft Cashmere Winter Scarf', 'short' => 'Fringed edge solid color thermal neck scarf.', 'tags' => 'fashion,scarf,cashmere', 'category' => $fashion, 'min' => 35, 'max' => 75, 'photo' => '1608256246200-53e635b5b65f'],
            ['name' => 'Touchscreen Leather Gloves', 'short' => 'Fleece lined conductive smartphone gloves.', 'tags' => 'fashion,gloves,winter', 'category' => $fashion, 'min' => 28, 'max' => 58, 'photo' => '1516914943479-89db7d9ae7f2'],
            ['name' => 'Branded Snapback Cap', 'short' => '6-panel structured flat bill baseball hat.', 'tags' => 'fashion,cap,streetwear', 'category' => $fashion, 'min' => 18, 'max' => 38, 'photo' => '1534215754734-18e55d13e346'],
            ['name' => 'Silk Neck Scarf Bandana', 'short' => 'Square patterned decorative neckerchief.', 'tags' => 'fashion,silk,scarf', 'category' => $fashion, 'min' => 18, 'max' => 38, 'photo' => '1601924994987-69e26d50dc26'],
            ['name' => 'Canvas Weekender Duffle', 'short' => 'Leather trim overnight carry-on luggage bag.', 'tags' => 'fashion,duffle,travel', 'category' => $fashion, 'min' => 55, 'max' => 110, 'photo' => '1544816565-aa8c1166648f'],
            ['name' => 'Slim Chino Shorts', 'short' => 'Clean flat-front knee-length casual shorts.', 'tags' => 'fashion,shorts,summer', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1562157879-847246ce79b7'],
            ['name' => 'Vintage Corduroy Cap', 'short' => 'Low-profile unconstructed curved brim cap.', 'tags' => 'fashion,cap,vintage', 'category' => $fashion, 'min' => 16, 'max' => 32, 'photo' => '1521369909029-2afed882baee'],
            ['name' => 'Denim Trucker Vest', 'short' => 'Sleeveless button-up raw denim waist vest.', 'tags' => 'fashion,vest,denim', 'category' => $fashion, 'min' => 32, 'max' => 64, 'photo' => '1551537482-f2075a1d41f2'],
            ['name' => 'Varsity Bomber Jacket', 'short' => 'Wool body contrast faux leather sleeves coat.', 'tags' => 'fashion,varsity,outerwear', 'category' => $fashion, 'min' => 65, 'max' => 130, 'photo' => '1578632767115-351597cf2477'],
            ['name' => 'Sherpa Lined Trucker', 'short' => 'Warm pile-lined heavy collar denim jacket.', 'tags' => 'fashion,jacket,sherpa', 'category' => $fashion, 'min' => 70, 'max' => 140, 'photo' => '1520975954732-35dd22299614'],
            ['name' => 'High-Neck Ribbed Tank', 'short' => 'Racerback body-hugging cotton basic tank.', 'tags' => 'fashion,tank,basics', 'category' => $fashion, 'min' => 12, 'max' => 24, 'photo' => '1503342452485-86b7f54527ef'],
            ['name' => 'Pleated Dress Trousers', 'short' => 'High-rise tapered tailored formal trousers.', 'tags' => 'fashion,trousers,formal', 'category' => $fashion, 'min' => 44, 'max' => 88, 'photo' => '1509551388413-e18d0ac5d495'],
            ['name' => 'Thermal Waffle Knit Longsleeve', 'short' => 'Base layer cold weather insulating shirt.', 'tags' => 'fashion,thermal,winter', 'category' => $fashion, 'min' => 22, 'max' => 45, 'photo' => '1618354691373-d851c5c3a990'],
            ['name' => 'Double-Knit Track Jacket', 'short' => 'Retro athletic zip jacket with stand collar.', 'tags' => 'fashion,sportswear,track', 'category' => $fashion, 'min' => 38, 'max' => 78, 'photo' => '1516826957135-700dedea698c'],
            ['name' => 'Elastic Waist Biker Shorts', 'short' => 'Seamless stretch high-compression active shorts.', 'tags' => 'fashion,shorts,active', 'category' => $fashion, 'min' => 16, 'max' => 32, 'photo' => '1538805060514-97d9cc17730c'],
            ['name' => 'Tie-Waist Kimono Cardigan', 'short' => 'Loose lightweight draped summer cover-up.', 'tags' => 'fashion,kimono,boho', 'category' => $fashion, 'min' => 28, 'max' => 56, 'photo' => '1584917865442-de89df76afd3'],
            ['name' => 'Paperbag Waist Pants', 'short' => 'Tie belt high-rise relaxed pleated trousers.', 'tags' => 'fashion,pants,chic', 'category' => $fashion, 'min' => 35, 'max' => 70, 'photo' => '1594633312681-425c7b97ccd1'],
            ['name' => 'Button-Front Denim Pinafore', 'short' => 'Layerable overall dress with patch pockets.', 'tags' => 'fashion,dress,denim', 'category' => $fashion, 'min' => 38, 'max' => 76, 'photo' => '1560060141-7b9018741ced'],
            ['name' => 'Striped Poplin Boxer Shorts', 'short' => 'Lounge ready lightweight summer sleep shorts.', 'tags' => 'fashion,shorts,lounge', 'category' => $fashion, 'min' => 14, 'max' => 28, 'photo' => '1586790170083-2f9ceadc7324'],
            ['name' => 'Cropped Zip-Up Hoodie', 'short' => 'Drop-shoulder boxy silhouette fleece sweatshirt.', 'tags' => 'fashion,hoodie,cropped', 'category' => $fashion, 'min' => 30, 'max' => 60, 'photo' => '1554568218-0f1715e72254'],
            ['name' => 'Lace Trim Cami Top', 'short' => 'Adjustable spaghetti strap silky evening camisole.', 'tags' => 'fashion,cami,silk', 'category' => $fashion, 'min' => 20, 'max' => 42, 'photo' => '1518619745347-009d17d5c5f8'],
            ['name' => 'Corduroy Button Mini Skirt', 'short' => 'Front snap closure retro textured skirt.', 'tags' => 'fashion,skirt,corduroy', 'category' => $fashion, 'min' => 26, 'max' => 52, 'photo' => '1582142306909-195724d33ffc'],
            ['name' => 'Classic Leather Bifold', 'short' => 'Eight card slot smooth calfskin pocket wallet.', 'tags' => 'fashion,wallet,accessories', 'category' => $fashion, 'min' => 28, 'max' => 58, 'photo' => '1607604276583-eef5d076aa5f'],
            ['name' => 'Straw Sun Fedora Hat', 'short' => 'Wide-brim breathable summer beach hat.', 'tags' => 'fashion,hat,summer', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1529720317453-c8da503f2051'],
            ['name' => 'Mesh Baseball Jersey', 'short' => 'Button-down athletic breathable streetwear jersey.', 'tags' => 'fashion,jersey,streetwear', 'category' => $fashion, 'min' => 32, 'max' => 64, 'photo' => '1503341455253-b2e723bb3dbb'],
            ['name' => 'Overcoat Wool Blend', 'short' => 'Notch lapel tailored minimalist winter overcoat.', 'tags' => 'fashion,coat,wool', 'category' => $fashion, 'min' => 110, 'max' => 210, 'photo' => '1544923246-77307dd654cb'],
            ['name' => 'Distressed Denim Cutoffs', 'short' => 'Raw hem faded vintage casual jean shorts.', 'tags' => 'fashion,shorts,denim', 'category' => $fashion, 'min' => 26, 'max' => 52, 'photo' => '1584308666744-24d5c474f2ae'],
            ['name' => 'Zip Utility Flight Suit', 'short' => 'One-piece technical ripstop coveralls.', 'tags' => 'fashion,flightsuit,utility', 'category' => $fashion, 'min' => 65, 'max' => 130, 'photo' => '1485230895905-ec40ba36b9bc'],
            ['name' => 'Braided Leather Belt', 'short' => 'Woven genuine leather buckle-anywhere belt.', 'tags' => 'fashion,belt,accessories', 'category' => $fashion, 'min' => 22, 'max' => 44, 'photo' => '1617137984095-74e4e5e3613f'],
            ['name' => 'Casual Chino Blazer', 'short' => 'Unstructured garment-dyed cotton smart blazer.', 'tags' => 'fashion,blazer,smartcasual', 'category' => $fashion, 'min' => 60, 'max' => 120, 'photo' => '1592878904946-b3cd8ae243d0'],
            ['name' => 'Cotton Poplin Pajama Set', 'short' => 'Notch collar buttoned top with drawstring pants.', 'tags' => 'fashion,sleepwear,cotton', 'category' => $fashion, 'min' => 35, 'max' => 70, 'photo' => '1584297091622-af8e5c8e3100'],
            ['name' => 'Retro Cat-Eye Sunglasses', 'short' => 'Acetate thick frame UV400 fashion shades.', 'tags' => 'fashion,eyewear,sunglasses', 'category' => $fashion, 'min' => 22, 'max' => 45, 'photo' => '1508296695146-257a814070b4'],
            ['name' => 'Cropped Knit Cardigan', 'short' => 'Tortoise button rib-trimmed wool blend top.', 'tags' => 'fashion,cardigan,knit', 'category' => $fashion, 'min' => 30, 'max' => 62, 'photo' => '1620799140408-edc6dcb6d633'],
            ['name' => 'Drawstring Cargo Joggers', 'short' => 'Multi-zip pocket stretch cuff casual trousers.', 'tags' => 'fashion,joggers,cargo', 'category' => $fashion, 'min' => 38, 'max' => 75, 'photo' => '1605518216924-959cf042b11c'],
            ['name' => 'Fleece Lined Parka', 'short' => 'Waterproof cold weather coat with storm hood.', 'tags' => 'fashion,parka,winter', 'category' => $fashion, 'min' => 100, 'max' => 195, 'photo' => '1517841905240-472988babdf9'],
            ['name' => 'Smocked Bodice Sundress', 'short' => 'Stretchy bust floral flare skirt summer dress.', 'tags' => 'fashion,dress,summer', 'category' => $fashion, 'min' => 34, 'max' => 68, 'photo' => '1496747611176-843222e1e57c'],
            ['name' => 'Heavyweight Flannel Overshirt', 'short' => 'Quilted lining cold-weather lumberjack jacket.', 'tags' => 'fashion,flannel,jacket', 'category' => $fashion, 'min' => 45, 'max' => 90, 'photo' => '1604644401890-0bd678c83788'],
            ['name' => 'High-Waist Flare Jeans', 'short' => '70s inspired bell bottom stretch denim.', 'tags' => 'fashion,denim,flare', 'category' => $fashion, 'min' => 42, 'max' => 85, 'photo' => '1542272604-787c3835535d'],
            ['name' => 'Leather Fanny Pack', 'short' => 'Adjustable waist sling everyday belt pouch.', 'tags' => 'fashion,bag,fannypack', 'category' => $fashion, 'min' => 28, 'max' => 58, 'photo' => '1548036328-c9fa89d128fa'],
            ['name' => 'Chambray Button Down', 'short' => 'Lightweight indigo textured work shirt.', 'tags' => 'fashion,shirt,chambray', 'category' => $fashion, 'min' => 28, 'max' => 56, 'photo' => '1598033129183-c4f50c736f10'],
            ['name' => 'Plush Bathrobe', 'short' => '100% Turkish cotton shawl collar lounger.', 'tags' => 'fashion,robe,lounge', 'category' => $fashion, 'min' => 48, 'max' => 95, 'photo' => '1584100936595-c0654b55a2e2'],
            ['name' => 'Pleated Tennis Skirt White', 'short' => 'Built-in spandex shorts athletic court skirt.', 'tags' => 'fashion,skirt,activewear', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1591557396347-4ec936477fb8'],
            ['name' => 'Ribbed Knit Midi Dress', 'short' => 'Long sleeve bodycon elegant winter dress.', 'tags' => 'fashion,dress,knit', 'category' => $fashion, 'min' => 40, 'max' => 80, 'photo' => '1539008835657-9e8e9680c956'],
            ['name' => 'Packable Rain Poncho', 'short' => 'Seam-sealed waterproof emergency outdoor poncho.', 'tags' => 'fashion,poncho,rain', 'category' => $fashion, 'min' => 18, 'max' => 36, 'photo' => '1519085360753-af0119f7cbe7'],
            ['name' => 'Washed Cotton Polo Cap', 'short' => 'Classic dad hat with brass tri-glide closure.', 'tags' => 'fashion,cap,accessories', 'category' => $fashion, 'min' => 16, 'max' => 32, 'photo' => '1575428652377-a2d80e2277fc'],
            ['name' => 'Brushed Flannel Lounge Pants', 'short' => 'Plaid drawstring super-soft pajama trousers.', 'tags' => 'fashion,pants,lounge', 'category' => $fashion, 'min' => 22, 'max' => 44, 'photo' => '1584544479605-d57051659aa8'],
            ['name' => 'Cropped Denim Trucker', 'short' => 'Raw cutoff hem light wash denim jacket.', 'tags' => 'fashion,denim,cropped', 'category' => $fashion, 'min' => 38, 'max' => 76, 'photo' => '1598522105502-a8acdf5e3df4'],
            ['name' => 'Silk Satin Pyjama Shirt', 'short' => 'Piped seam luxury button-front sleepwear.', 'tags' => 'fashion,silk,pajamas', 'category' => $fashion, 'min' => 45, 'max' => 90, 'photo' => '1588117305351-2196024921f0'],
            ['name' => 'Quilted Liner Jacket', 'short' => 'Curved onion quilting lightweight military coat.', 'tags' => 'fashion,jacket,layering', 'category' => $fashion, 'min' => 48, 'max' => 96, 'photo' => '1556821840-3a63f8550908'],
            ['name' => 'Oversized Wool Scarf', 'short' => 'Blanket style extra-long soft winter wrap.', 'tags' => 'fashion,scarf,wool', 'category' => $fashion, 'min' => 30, 'max' => 60, 'photo' => '1520903920243-00d872a2d1c9'],
            ['name' => 'Linen Drawstring Shorts', 'short' => 'Natural textured elastic waist summer shorts.', 'tags' => 'fashion,shorts,linen', 'category' => $fashion, 'min' => 24, 'max' => 48, 'photo' => '1591195853828-b80c5c3e5077'],
            ['name' => 'Heavyweight Boxy Tee', 'short' => 'Drop-shoulder thick 240 GSM streetwear tee.', 'tags' => 'fashion,tshirt,streetwear', 'category' => $fashion, 'min' => 20, 'max' => 40, 'photo' => '1521572267360-dfa04e5bf596'],

            // =========================================================================
            // SHOES (100 Products)
            // =========================================================================
            ['name' => 'Minimalist White Leather Sneaker', 'short' => 'Clean low-top Italian leather lace-up shoes.', 'tags' => 'shoes,sneakers,casual', 'category' => $shoes, 'min' => 55, 'max' => 120, 'photo' => '1549298916-b41d501d3772'],
            ['name' => 'Performance Marathon Runner', 'short' => 'Carbon plate energy-return racing trainers.', 'tags' => 'shoes,running,sports', 'category' => $shoes, 'min' => 85, 'max' => 170, 'photo' => '1542291026-7eec264c27ff'],
            ['name' => 'Breathable Mesh Road Runners', 'short' => 'Cushioned daily training lightweight running shoes.', 'tags' => 'shoes,running,mesh', 'category' => $shoes, 'min' => 45, 'max' => 90, 'photo' => '1584735935682-2f2b69dff9d2'],
            ['name' => 'Retro High-Top Sneakers', 'short' => '80s basketball-inspired padded ankle sneakers.', 'tags' => 'shoes,sneakers,retro', 'category' => $shoes, 'min' => 60, 'max' => 125, 'photo' => '1552346154-21d32810aba3'],
            ['name' => 'Classic Canvas Skate Shoes', 'short' => 'Vulcanized rubber waffle sole board feel shoes.', 'tags' => 'shoes,skate,canvas', 'category' => $shoes, 'min' => 35, 'max' => 65, 'photo' => '1525966222134-fcfa99b8ae77'],
            ['name' => 'Chunky Streetwear Dad Trainers', 'short' => 'Thick sculptural sole throwback sneaker.', 'tags' => 'shoes,sneakers,streetwear', 'category' => $shoes, 'min' => 65, 'max' => 135, 'photo' => '1595950653106-6c9ebd614d3a'],
            ['name' => 'Knit Slip-On Walking Shoes', 'short' => 'Stretch knit upper sock-style easy slip sneakers.', 'tags' => 'shoes,slipon,comfort', 'category' => $shoes, 'min' => 30, 'max' => 60, 'photo' => '1560769629-975ec94e6a86'],
            ['name' => 'Waterproof Trail Running Shoes', 'short' => 'Deep lug aggressive traction trail runner.', 'tags' => 'shoes,trail,outdoor', 'category' => $shoes, 'min' => 75, 'max' => 145, 'photo' => '1551107696-a4b085a6d965'],
            ['name' => 'Leather Chelsea Boots', 'short' => 'Elastic side-gusset pull-on ankle dress boots.', 'tags' => 'shoes,boots,chelsea', 'category' => $shoes, 'min' => 70, 'max' => 150, 'photo' => '1638247025967-b4e38f787b76'],
            ['name' => 'Suede Desert Chukka Boots', 'short' => 'Crepe rubber sole 2-eyelet soft suede chukkas.', 'tags' => 'shoes,boots,suede', 'category' => $shoes, 'min' => 60, 'max' => 120, 'photo' => '1520639888713-7851133b1ed0'],
            ['name' => 'Combat Lace-Up Boots', 'short' => 'Lug-sole durable high-top tactical boots.', 'tags' => 'shoes,boots,combat', 'category' => $shoes, 'min' => 65, 'max' => 130, 'photo' => '1579338559194-a162d19bf842'],
            ['name' => 'Rugged All-Terrain Hiking Boots', 'short' => 'Ankle support waterproof mountain hiker.', 'tags' => 'shoes,hiking,outdoor', 'category' => $shoes, 'min' => 85, 'max' => 175, 'photo' => '1584735935682-1b1e2c918c54'],
            ['name' => 'Steel-Toe Work Boots', 'short' => 'Oil-resistant heavy-duty leather safety boot.', 'tags' => 'shoes,work,boots', 'category' => $shoes, 'min' => 80, 'max' => 160, 'photo' => '1518049362265-d5b2a6467637'],
            ['name' => 'Cap-Toe Leather Oxford Shoes', 'short' => 'Closed-lace formal dress business shoes.', 'tags' => 'shoes,oxfords,formal', 'category' => $shoes, 'min' => 75, 'max' => 160, 'photo' => '1614252235316-8c857d38b5f4'],
            ['name' => 'Brown Leather Derby Shoes', 'short' => 'Open-lacing classic dress derby footwear.', 'tags' => 'shoes,derby,formal', 'category' => $shoes, 'min' => 65, 'max' => 135, 'photo' => '1533867617858-e7b97e060509'],
            ['name' => 'Double Monk Strap Shoes', 'short' => 'Polished buckle-strap formal dress footwear.', 'tags' => 'shoes,monkstrap,leather', 'category' => $shoes, 'min' => 80, 'max' => 165, 'photo' => '1595341888016-a392ef81b7de'],
            ['name' => 'Wingtip Leather Brogues', 'short' => 'Perforated detail classic British heritage brogues.', 'tags' => 'shoes,brogues,formal', 'category' => $shoes, 'min' => 70, 'max' => 140, 'photo' => '1515347619252-60a4bf4fff4f'],
            ['name' => 'Classic Penny Loafers', 'short' => 'Slip-on saddle-strap leather moccasin shoes.', 'tags' => 'shoes,loafers,casual', 'category' => $shoes, 'min' => 60, 'max' => 120, 'photo' => '1582588678413-dbf45f4823e9'],
            ['name' => 'Suede Tassel Loafers', 'short' => 'Handcrafted velvet-soft suede decorative loafer.', 'tags' => 'shoes,loafers,suede', 'category' => $shoes, 'min' => 65, 'max' => 130, 'photo' => '1587563871167-1ee9c731aefb'],
            ['name' => 'Driving Moccasin Shoes', 'short' => 'Pebbled rubber sole flexible slip-on loafers.', 'tags' => 'shoes,driving,comfort', 'category' => $shoes, 'min' => 50, 'max' => 100, 'photo' => '1606107557195-0e29a4b5b4aa'],
            ['name' => 'Pointed Stiletto Pumps', 'short' => '4-inch sleek evening patent high heels.', 'tags' => 'shoes,heels,formal', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1543163521-1bf539c55dd2'],
            ['name' => 'Block Heel Sandal Pumps', 'short' => 'Ankle-strap sturdy block heel summer pumps.', 'tags' => 'shoes,heels,sandals', 'category' => $shoes, 'min' => 45, 'max' => 90, 'photo' => '1535043934128-cf0b28d52f95'],
            ['name' => 'Leather Ballet Flats', 'short' => 'Elastic collar round-toe comfortable daily flats.', 'tags' => 'shoes,flats,comfort', 'category' => $shoes, 'min' => 35, 'max' => 70, 'photo' => '1560343090-f0409e92791a'],
            ['name' => 'Woven Leather Slide Sandals', 'short' => 'Cushioned footbed open-toe summer slides.', 'tags' => 'shoes,sandals,slides', 'category' => $shoes, 'min' => 28, 'max' => 55, 'photo' => '1603808033192-082d6919d3e1'],
            ['name' => 'Espadrille Wedge Sandals', 'short' => 'Jute-wrapped wedge heel ankle tie sandals.', 'tags' => 'shoes,espadrilles,summer', 'category' => $shoes, 'min' => 40, 'max' => 80, 'photo' => '1608231387042-66d1773070a5'],
            ['name' => 'Cork Footbed Buckle Slides', 'short' => 'Anatomical contoured arch-support double buckle sandals.', 'tags' => 'shoes,sandals,cork', 'category' => $shoes, 'min' => 32, 'max' => 65, 'photo' => '1600269452121-4f2416e55c28'],
            ['name' => 'Beach Thong Flip Flops', 'short' => 'Waterproof textured rubber cushioned sole flips.', 'tags' => 'shoes,flipflops,beach', 'category' => $shoes, 'min' => 10, 'max' => 22, 'photo' => '1597045566677-8cf032ed6634'],
            ['name' => 'Rubber Rain Boots Wellington', 'short' => 'Waterproof knee-high mud and puddle boots.', 'tags' => 'shoes,boots,rain', 'category' => $shoes, 'min' => 35, 'max' => 70, 'photo' => '1562273138-f46be4eb133c'],
            ['name' => 'Ankle Buckle Booties', 'short' => 'Side zipper stacked low heel casual booties.', 'tags' => 'shoes,boots,booties', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1449505278894-297fdb3edbc1'],
            ['name' => 'Indoor Cozy Suede Slippers', 'short' => 'Plush shearling-lined indoor rubber sole loafers.', 'tags' => 'shoes,slippers,lounge', 'category' => $shoes, 'min' => 25, 'max' => 50, 'photo' => '1512374382149-233c47b5a93c'],
            ['name' => 'Gym Weightlifting Shoes', 'short' => 'Elevated wooden heel rigid strap powerlifting shoes.', 'tags' => 'shoes,gym,fitness', 'category' => $shoes, 'min' => 70, 'max' => 140, 'photo' => '1514989940743-460b4735732c'],
            ['name' => 'Retro Suede Joggers', 'short' => 'Vintage gum-sole nylon and suede runner.', 'tags' => 'shoes,sneakers,retro', 'category' => $shoes, 'min' => 50, 'max' => 95, 'photo' => '1548036328-c9fa89d128fb'],
            ['name' => 'Gladiator Strappy Sandals', 'short' => 'Knee-high crisscross lace summer flats.', 'tags' => 'shoes,sandals,gladiator', 'category' => $shoes, 'min' => 32, 'max' => 64, 'photo' => '1535043934128-cf0b28d52f96'],
            ['name' => 'Patent Tuxedo Opera Pumps', 'short' => 'High-shine formal evening bow dress shoes.', 'tags' => 'shoes,formal,tuxedo', 'category' => $shoes, 'min' => 85, 'max' => 170, 'photo' => '1575537302964-96cd47c06b1b'],
            ['name' => 'Western Embroidered Cowboy Boots', 'short' => 'Pointed toe decorative stitch leather boots.', 'tags' => 'shoes,boots,western', 'category' => $shoes, 'min' => 95, 'max' => 190, 'photo' => '1543163521-1bf539c55dd3'],
            ['name' => 'Pointed Mule Slides', 'short' => 'Backless slip-on leather flat smart mules.', 'tags' => 'shoes,mules,flats', 'category' => $shoes, 'min' => 38, 'max' => 75, 'photo' => '1525966222134-fcfa99b8ae78'],
            ['name' => 'Platform Creeper Shoes', 'short' => 'Ribbed platform thick sole rockabilly shoe.', 'tags' => 'shoes,creepers,punk', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1515955656352-a1fa3ffcd111'],
            ['name' => 'Spikeless Golf Shoes', 'short' => 'Waterproof hybrid grip sole golf sneakers.', 'tags' => 'shoes,golf,sports', 'category' => $shoes, 'min' => 65, 'max' => 130, 'photo' => '1582588678413-dbf45f4823ea'],
            ['name' => 'Cycling Road Clip Shoes', 'short' => 'Rigid nylon sole 3-bolt cleat road bike shoes.', 'tags' => 'shoes,cycling,sports', 'category' => $shoes, 'min' => 75, 'max' => 150, 'photo' => '1560769629-975ec94e6a87'],
            ['name' => 'High-Gloss Rain Chelsea', 'short' => 'Waterproof ankle slip-on puddling boots.', 'tags' => 'shoes,boots,rain', 'category' => $shoes, 'min' => 38, 'max' => 75, 'photo' => '1520639888713-7851133b1ed1'],
            ['name' => 'Cross-Trainer Court Shoes', 'short' => 'Lateral stability padded tennis gym footwear.', 'tags' => 'shoes,training,gym', 'category' => $shoes, 'min' => 50, 'max' => 100, 'photo' => '1595341888016-a392ef81b7df'],
            ['name' => 'Boat Deck Shoes', 'short' => 'Non-marking siped sole genuine leather boaters.', 'tags' => 'shoes,boatshoes,casual', 'category' => $shoes, 'min' => 45, 'max' => 90, 'photo' => '1584735935682-2f2b69dff9d3'],
            ['name' => 'Lace-Up Snow Boots', 'short' => 'Thermal insulated faux-fur lined winter treaders.', 'tags' => 'shoes,boots,winter', 'category' => $shoes, 'min' => 75, 'max' => 150, 'photo' => '1603487742131-4160ec999306'],
            ['name' => 'Suede Venetian Loafers', 'short' => 'Clean plain-vamp slipper loafers.', 'tags' => 'shoes,loafers,suede', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1614252235316-8c857d38b5f5'],
            ['name' => 'Platform Canvas High-Tops', 'short' => 'Double-stacked vulcanized sole casual sneakers.', 'tags' => 'shoes,sneakers,platform', 'category' => $shoes, 'min' => 42, 'max' => 84, 'photo' => '1549298916-b41d501d3773'],
            ['name' => 'Barefoot Zero-Drop Trainers', 'short' => 'Wide toe-box flexible minimalist runner.', 'tags' => 'shoes,barefoot,running', 'category' => $shoes, 'min' => 48, 'max' => 95, 'photo' => '1511556532299-8f662fc26c06'],
            ['name' => 'Knee-High Suede Boots', 'short' => 'Side-zip fitted block-heel tall boots.', 'tags' => 'shoes,boots,tall', 'category' => $shoes, 'min' => 85, 'max' => 175, 'photo' => '1533867617858-e7b97e06050a'],
            ['name' => 'Slingback Kitten Heels', 'short' => 'Pointed-toe low-heel dressy office pumps.', 'tags' => 'shoes,heels,office', 'category' => $shoes, 'min' => 42, 'max' => 85, 'photo' => '1560343090-f0409e92791b'],
            ['name' => 'Athletic Slide Sandals', 'short' => 'Molded EVA foam recovery sports slide.', 'tags' => 'shoes,slides,recovery', 'category' => $shoes, 'min' => 18, 'max' => 36, 'photo' => '1608231387042-66d1773070a6'],
            ['name' => 'Low-Profile Leather Court', 'short' => 'Perforated side panels minimalist heritage sneaker.', 'tags' => 'shoes,sneakers,court', 'category' => $shoes, 'min' => 52, 'max' => 105, 'photo' => '1542291026-7eec264c27fe'],
            ['name' => 'Distressed High-Top Canvas', 'short' => 'Washed vintage-dyed retro skater shoes.', 'tags' => 'shoes,sneakers,vintage', 'category' => $shoes, 'min' => 38, 'max' => 75, 'photo' => '1603808033192-082d6919d3e2'],
            ['name' => 'Equestrian Tall Riding Boots', 'short' => 'Polished calfskin full-length zipper boots.', 'tags' => 'shoes,boots,riding', 'category' => $shoes, 'min' => 110, 'max' => 220, 'photo' => '1638247025967-b4e38f787b77'],
            ['name' => 'Peep-Toe Wedge Sandals', 'short' => 'Cork wedge slingback summer party shoes.', 'tags' => 'shoes,wedges,sandals', 'category' => $shoes, 'min' => 36, 'max' => 72, 'photo' => '1543163521-1bf539c55dd4'],
            ['name' => 'Orthopedic Comfort Walkers', 'short' => 'Max cushioning arch support daily sneakers.', 'tags' => 'shoes,comfort,walking', 'category' => $shoes, 'min' => 48, 'max' => 96, 'photo' => '1552346154-21d32810aba4'],
            ['name' => 'Mesh Slip-On Water Shoes', 'short' => 'Quick-dry barefoot beach swimming shoes.', 'tags' => 'shoes,watershoes,beach', 'category' => $shoes, 'min' => 15, 'max' => 30, 'photo' => '1520639888713-7851133b1ed2'],
            ['name' => 'Split-Toe Blucher Shoes', 'short' => 'Textured pebble leather heavy welt shoe.', 'tags' => 'shoes,blucher,formal', 'category' => $shoes, 'min' => 70, 'max' => 140, 'photo' => '1614252235316-8c857d38b5f6'],
            ['name' => 'Camo Trail Hiking Runners', 'short' => 'Reinforced toe bumper rugged off-road shoes.', 'tags' => 'shoes,hiking,camo', 'category' => $shoes, 'min' => 58, 'max' => 115, 'photo' => '1603808033192-082d6919d3e3'],
            ['name' => 'Ankle Strap D\'Orsay Flats', 'short' => 'Cutout side panels pointed elegant flats.', 'tags' => 'shoes,flats,dorsay', 'category' => $shoes, 'min' => 34, 'max' => 68, 'photo' => '1542291026-7eec264c27fd'],
            ['name' => 'Lace-Up Logger Boots', 'short' => 'High-arch Vibram sole heavy woodcutter boots.', 'tags' => 'shoes,boots,logger', 'category' => $shoes, 'min' => 115, 'max' => 230, 'photo' => '1608256246200-53e635b5b65e'],
            ['name' => 'Platform Oxford Brogues', 'short' => 'Lugged thick sole modern statement brogues.', 'tags' => 'shoes,brogues,platform', 'category' => $shoes, 'min' => 62, 'max' => 125, 'photo' => '1535043934128-cf0b28d52f97'],
            ['name' => 'Chunky Knit Running Shoes', 'short' => 'Responsive boost sole ultra-comfy trainer.', 'tags' => 'shoes,running,knit', 'category' => $shoes, 'min' => 60, 'max' => 120, 'photo' => '1533867617858-e7b97e06050b'],
            ['name' => 'Buckled Monk Ankle Boots', 'short' => 'Dual buckle strap smooth leather fashion boot.', 'tags' => 'shoes,boots,monkstrap', 'category' => $shoes, 'min' => 80, 'max' => 160, 'photo' => '1584735935682-2f2b69dff9d4'],
            ['name' => 'Strappy Lace-Up Heels', 'short' => 'Ankle wrap stiletto open toe party sandals.', 'tags' => 'shoes,heels,party', 'category' => $shoes, 'min' => 45, 'max' => 90, 'photo' => '1638247025967-b4e38f787b78'],
            ['name' => 'Vintage Canvas Low-Tops', 'short' => 'Raw rubber toe-cap traditional tennis shoe.', 'tags' => 'shoes,sneakers,canvas', 'category' => $shoes, 'min' => 28, 'max' => 56, 'photo' => '1614252235316-8c857d38b5f7'],
            ['name' => 'Shearling-Lined Winter Boots', 'short' => 'Waterproof suede upper cozy cold boot.', 'tags' => 'shoes,boots,shearling', 'category' => $shoes, 'min' => 78, 'max' => 155, 'photo' => '1533867617858-e7b97e06050c'],
            ['name' => 'Suede Horsebit Loafers', 'short' => 'Golden metal snaffle front luxury loafer.', 'tags' => 'shoes,loafers,horsebit', 'category' => $shoes, 'min' => 70, 'max' => 140, 'photo' => '1543163521-1bf539c55dd5'],
            ['name' => 'Lightweight Track Cleats', 'short' => 'Spiked outsole sprint racing athletic shoes.', 'tags' => 'shoes,cleats,sports', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1520639888713-7851133b1ed3'],
            ['name' => 'Fisherman Leather Sandals', 'short' => 'Interlocking cage strap buckled leather sandals.', 'tags' => 'shoes,sandals,fisherman', 'category' => $shoes, 'min' => 38, 'max' => 76, 'photo' => '1560343090-f0409e92791c'],
            ['name' => 'Chunky Sole Chelsea', 'short' => 'Lugged combat sole elastic pull-on boot.', 'tags' => 'shoes,boots,chelsea', 'category' => $shoes, 'min' => 68, 'max' => 135, 'photo' => '1525966222134-fcfa99b8ae79'],
            ['name' => 'Kitten Heel Mules', 'short' => 'Square toe low-heel chic slip-on mules.', 'tags' => 'shoes,mules,heels', 'category' => $shoes, 'min' => 36, 'max' => 72, 'photo' => '1582588678413-dbf45f4823eb'],
            ['name' => 'Colorblock Court Sneakers', 'short' => 'Contrast leather panelling 90s aesthetic shoe.', 'tags' => 'shoes,sneakers,retro', 'category' => $shoes, 'min' => 52, 'max' => 105, 'photo' => '1520639888713-7851133b1ed4'],
            ['name' => 'GORE-TEX Mountain Boots', 'short' => 'Heavy crampon-compatible alpine hiking footwear.', 'tags' => 'shoes,boots,alpine', 'category' => $shoes, 'min' => 125, 'max' => 250, 'photo' => '1549298916-b41d501d3774'],
            ['name' => 'Two-Tone Spectator Oxfords', 'short' => 'Vintage jazz era contrasting leather brogues.', 'tags' => 'shoes,oxfords,vintage', 'category' => $shoes, 'min' => 85, 'max' => 170, 'photo' => '1543163521-1bf539c55dd6'],
            ['name' => 'Wooden Platform Clogs', 'short' => 'Studded leather upper natural wood sole clogs.', 'tags' => 'shoes,clogs,vintage', 'category' => $shoes, 'min' => 45, 'max' => 90, 'photo' => '1533867617858-e7b97e06050d'],
            ['name' => 'Reflective Night Runners', 'short' => '3M hi-vis running shoe with air cushion.', 'tags' => 'shoes,running,reflective', 'category' => $shoes, 'min' => 64, 'max' => 128, 'photo' => '1535043934128-cf0b28d52f98'],
            ['name' => 'Pull-On Roper Boots', 'short' => 'Low-heeled round-toe classic ranch work boots.', 'tags' => 'shoes,boots,roper', 'category' => $shoes, 'min' => 75, 'max' => 150, 'photo' => '1614252235316-8c857d38b5f8'],
            ['name' => 'Open-Toe Espadrille Flats', 'short' => 'Braided jute sole ankle buckle summer flats.', 'tags' => 'shoes,espadrilles,flats', 'category' => $shoes, 'min' => 28, 'max' => 56, 'photo' => '1603808033192-082d6919d3e4'],
            ['name' => 'Textured Suede Derby', 'short' => 'Brick red EVA sole smart casual derby shoes.', 'tags' => 'shoes,derby,suede', 'category' => $shoes, 'min' => 58, 'max' => 116, 'photo' => '1582588678413-dbf45f4823ec'],
            ['name' => 'Neon Aerobic Trainers', 'short' => 'High-impact bouncy gym workout cross trainers.', 'tags' => 'shoes,training,neon', 'category' => $shoes, 'min' => 46, 'max' => 92, 'photo' => '1608256246200-53e635b5b65d'],
            ['name' => 'Ankle Buckle Jodhpur Boots', 'short' => 'Wrap-around strap sleek polished equestrian boot.', 'tags' => 'shoes,boots,jodhpur', 'category' => $shoes, 'min' => 82, 'max' => 165, 'photo' => '1525966222134-fcfa99b8ae7a'],
            ['name' => 'Scuff-Resistant Work Oxfords', 'short' => 'Slip-resistant steel shank uniform oxfords.', 'tags' => 'shoes,oxfords,work', 'category' => $shoes, 'min' => 50, 'max' => 100, 'photo' => '1535043934128-cf0b28d52f99'],
            ['name' => 'Casual Knit Boat Shoes', 'short' => 'Breathable flyknit upper lightweight deck shoe.', 'tags' => 'shoes,boatshoes,knit', 'category' => $shoes, 'min' => 36, 'max' => 72, 'photo' => '1533867617858-e7b97e06050e'],
            ['name' => 'Satin Party Heels', 'short' => 'Crystal brooch embellished pointed toe pumps.', 'tags' => 'shoes,heels,party', 'category' => $shoes, 'min' => 60, 'max' => 120, 'photo' => '1549298916-b41d501d3775'],
            ['name' => 'Duck Weather Boots', 'short' => 'Waterproof rubber shell lace-up mud boots.', 'tags' => 'shoes,boots,duck', 'category' => $shoes, 'min' => 55, 'max' => 110, 'photo' => '1551107696-a4b085a6d966'],
            ['name' => 'Woven Mule Flats', 'short' => 'Raffia woven pointed toe summer house mules.', 'tags' => 'shoes,mules,raffia', 'category' => $shoes, 'min' => 30, 'max' => 60, 'photo' => '1584735935682-1b1e2c918c55'],
            ['name' => 'High-Top Skate Sneaker', 'short' => 'Padded tongue reinforced ollie area skate shoes.', 'tags' => 'shoes,skate,sneakers', 'category' => $shoes, 'min' => 48, 'max' => 96, 'photo' => '1595341888016-a392ef81b7e0'],
            ['name' => 'Velvet Evening Slippers', 'short' => 'Gold crest embroidery quilted lining dress loafers.', 'tags' => 'shoes,loafers,velvet', 'category' => $shoes, 'min' => 75, 'max' => 150, 'photo' => '1543163521-1bf539c55dd7'],
            ['name' => 'Barefoot Hiking Boots', 'short' => 'Zero drop flexible wide-foot trail boots.', 'tags' => 'shoes,boots,barefoot', 'category' => $shoes, 'min' => 70, 'max' => 140, 'photo' => '1608231387042-66d1773070a7'],
            ['name' => 'Classic White Court Tennis', 'short' => 'Reinforced toe low-profile clean sneakers.', 'tags' => 'shoes,sneakers,tennis', 'category' => $shoes, 'min' => 44, 'max' => 88, 'photo' => '1582588678413-dbf45f4823ed'],
            ['name' => 'Strappy Stiletto Booties', 'short' => 'Peep toe lace cage high heel party boots.', 'tags' => 'shoes,boots,heels', 'category' => $shoes, 'min' => 58, 'max' => 116, 'photo' => '1638247025967-b4e38f787b79'],
            ['name' => 'Leather Deck Moccasin', 'short' => '360-degree raw hide lacing nautical shoe.', 'tags' => 'shoes,moccasin,leather', 'category' => $shoes, 'min' => 46, 'max' => 92, 'photo' => '1603487742131-4160ec999307'],
            ['name' => 'Chunky Wedge Espadrilles', 'short' => 'Closed round-toe wrap-tie jute summer heel.', 'tags' => 'shoes,espadrilles,wedges', 'category' => $shoes, 'min' => 42, 'max' => 85, 'photo' => '1608231387042-66d1773070a8'],
            ['name' => 'Brogue Ankle Booties', 'short' => 'Medallion wingtip zip-up leather dress boots.', 'tags' => 'shoes,boots,brogue', 'category' => $shoes, 'min' => 72, 'max' => 145, 'photo' => '1520639888713-7851133b1ed5'],
            ['name' => 'Ultra-Cushioned Recovery Slide', 'short' => 'Thick ergonomic arch cloud slide footwear.', 'tags' => 'shoes,slides,recovery', 'category' => $shoes, 'min' => 22, 'max' => 45, 'photo' => '1560343090-f0409e92791d'],
            ['name' => 'Kiltie Fringe Golf Loafers', 'short' => 'Detachable fringe tongue spikeless course shoe.', 'tags' => 'shoes,golf,loafers', 'category' => $shoes, 'min' => 64, 'max' => 128, 'photo' => '1525966222134-fcfa99b8ae7b'],
            ['name' => 'Thermal Snow Traction Boot', 'short' => 'Ice-grip sole fleece-lined high snow boot.', 'tags' => 'shoes,boots,winter', 'category' => $shoes, 'min' => 82, 'max' => 165, 'photo' => '1520639888713-7851133b1ed6'],
            ['name' => 'Minimalist Black Slip-On', 'short' => 'Padded collar vulcanized sole everyday shoe.', 'tags' => 'shoes,sneakers,slipon', 'category' => $shoes, 'min' => 32, 'max' => 65, 'photo' => '1603808033192-082d6919d3e5'],
            ['name' => 'Metallic Block Heel Sandals', 'short' => 'Shimmering open-toe ankle buckle dance shoes.', 'tags' => 'shoes,heels,sandals', 'category' => $shoes, 'min' => 48, 'max' => 96, 'photo' => '1549298916-b41d501d3776'],
            ['name' => 'Full-Brogue Derby Tan', 'short' => 'Country calf leather storm welt dress shoes.', 'tags' => 'shoes,derby,brogue', 'category' => $shoes, 'min' => 76, 'max' => 155, 'photo' => '1608256246200-53e635b5b65c'],
            ['name' => 'Retro Leather Track Runner', 'short' => 'EVA midsole studded rubber sole sneakers.', 'tags' => 'shoes,sneakers,retro', 'category' => $shoes, 'min' => 54, 'max' => 108, 'photo' => '1543163521-1bf539c55dd8'],

            // =========================================================================
            // BEAUTY (100 Products)
            // =========================================================================
            ['name' => 'Vitamin C 20% Brightening Serum', 'short' => 'Ferulic acid antioxidant glow face serum.', 'tags' => 'beauty,skincare,serum', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1620916566398-39f1143ab7be'],
            ['name' => 'Hyaluronic Acid Multi-Depth Plumper', 'short' => 'Triple molecular weight ultra-hydrating serum.', 'tags' => 'beauty,skincare,hydration', 'category' => $beauty, 'min' => 18, 'max' => 38, 'photo' => '1608248597359-07b911be2497'],
            ['name' => 'Retinol 1% Night Repair Serum', 'short' => 'Pure squalane retinol anti-aging formula.', 'tags' => 'beauty,retinol,antiaging', 'category' => $beauty, 'min' => 28, 'max' => 56, 'photo' => '1601049541289-9b1b7bbbfe19'],
            ['name' => 'Niacinamide 10% + Zinc Pore Refiner', 'short' => 'Blemish formula balancing sebum and shine.', 'tags' => 'beauty,skincare,serum', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1616683693504-3ea7e9ad6fec'],
            ['name' => 'Ceramide Barrier Daily Moisturizer', 'short' => 'Lightweight non-comedogenic hydration lotion.', 'tags' => 'beauty,moisturizer,cream', 'category' => $beauty, 'min' => 22, 'max' => 44, 'photo' => '1556228720-195a672e8a03'],
            ['name' => 'Deep Night Recovery Cream', 'short' => 'Rich shea butter replenishing sleeping cream.', 'tags' => 'beauty,nightcream,skincare', 'category' => $beauty, 'min' => 32, 'max' => 65, 'photo' => '1615397349754-cfa2066a298e'],
            ['name' => 'Gentle Foaming Gel Cleanser', 'short' => 'Sulfate-free pH balanced everyday face wash.', 'tags' => 'beauty,cleanser,facewash', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1556228722-d0b7194f06bd'],
            ['name' => 'Hydrating Oat Milk Cleanser', 'short' => 'Calming soothing makeup melt creamy wash.', 'tags' => 'beauty,cleanser,sensitive', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1617897903246-719242758050'],
            ['name' => 'Micellar Cleansing Water', 'short' => 'No-rinse gentle magnetic dirt removal toner.', 'tags' => 'beauty,micellar,makeupremover', 'category' => $beauty, 'min' => 12, 'max' => 24, 'photo' => '1571781926291-c477ebfd024b'],
            ['name' => 'BHA 2% Liquid Exfoliating Toner', 'short' => 'Salicylic acid clarifying blackhead toner.', 'tags' => 'beauty,exfoliant,toner', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1570172619644-dfd03ed5d881'],
            ['name' => 'Organic Damask Rose Water Mist', 'short' => 'Refreshing botanical facial soothing mist.', 'tags' => 'beauty,mist,rosewater', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1598440947619-58bfa9df0e3b'],
            ['name' => 'Invisible SPF 50 Broad Spectrum Sunscreen', 'short' => 'Zero white-cast weightless matte UV fluid.', 'tags' => 'beauty,sunscreen,spf', 'category' => $beauty, 'min' => 20, 'max' => 42, 'photo' => '1598440947619-2c35fc9aa908'],
            ['name' => 'Tinted Mineral Sunscreen SPF 40', 'short' => 'Zinc oxide sheer coverage protective cream.', 'tags' => 'beauty,sunscreen,tinted', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1590156221185-c546e3ea853e'],
            ['name' => 'Caffeine 5% Under-Eye Depuffing Gel', 'short' => 'Targeted cooling rollerball dark circle cream.', 'tags' => 'beauty,eyecream,caffeine', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1608248597359-07b911be2498'],
            ['name' => 'Peptide Multi-Action Eye Cream', 'short' => 'Fine line smoothing collagen peptide balm.', 'tags' => 'beauty,eyecream,antiaging', 'category' => $beauty, 'min' => 26, 'max' => 52, 'photo' => '1601049541289-9b1b7bbbfe1a'],
            ['name' => 'Bentonite Clay Detox Face Mask', 'short' => 'Deep pore purifying volcanic mineral mud.', 'tags' => 'beauty,facemask,clay', 'category' => $beauty, 'min' => 18, 'max' => 38, 'photo' => '1567928805162-f6720f4c0d02'],
            ['name' => 'Centella Soothing Sheet Mask Pack', 'short' => 'Pack of 5 cica calming instant relief sheets.', 'tags' => 'beauty,sheetmask,cica', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1567928805162-f6720f4c0d03'],
            ['name' => 'Berry Lip Sleeping Mask', 'short' => 'Overnight nourishing berry wax lip treatment.', 'tags' => 'beauty,lipcare,mask', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1586495777744-4413f21062fa'],
            ['name' => 'Pure Cold-Pressed Rosehip Seed Oil', 'short' => 'Omega-rich organic regenerative facial oil.', 'tags' => 'beauty,faceoil,organic', 'category' => $beauty, 'min' => 20, 'max' => 40, 'photo' => '1608248597359-07b911be2499'],
            ['name' => 'Plant-Derived Squalane Moisture Oil', 'short' => 'Non-greasy pure sugarcane skin and hair oil.', 'tags' => 'beauty,squalane,faceoil', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1576426863848-c21f53c60b19'],
            ['name' => 'Hydrocolloid Blemish Patches', 'short' => 'Invisible waterproof pimple healing patches.', 'tags' => 'beauty,patches,acne', 'category' => $beauty, 'min' => 8, 'max' => 16, 'photo' => '1567928805162-f6720f4c0d04'],
            ['name' => 'AHA 30% + BHA 2% Peeling Solution', 'short' => 'Exfoliating red chemical facial peel.', 'tags' => 'beauty,peeling,skincare', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1620916566398-39f1143ab7bf'],
            ['name' => 'Galactomyces First Treatment Essence', 'short' => 'Fermented brightening boosting skin essence.', 'tags' => 'beauty,essence,kbeauty', 'category' => $beauty, 'min' => 26, 'max' => 52, 'photo' => '1571781926291-c477ebfd024c'],
            ['name' => 'Nourishing Cleansing Balm', 'short' => 'Sherbet balm-to-oil stubborn makeup remover.', 'tags' => 'beauty,cleansingbalm,makeup', 'category' => $beauty, 'min' => 22, 'max' => 45, 'photo' => '1556228720-195a672e8a04'],
            ['name' => 'Matte Bullet Velvet Lipstick', 'short' => 'Long-wear richly pigmented non-drying red lipstick.', 'tags' => 'beauty,lipstick,makeup', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1589525231707-f2de2428f59c'],
            ['name' => 'Liquid Matte Lip Stain', 'short' => 'Transfer-proof weightless budge-free lip color.', 'tags' => 'beauty,lipstick,liquid', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1504904488824-dbeda3cb530b'],
            ['name' => 'High-Shine Plumping Lip Gloss', 'short' => 'Glass-like reflective hydrating peptide gloss.', 'tags' => 'beauty,lipgloss,plumping', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1629198688000-71f23e745b6e'],
            ['name' => 'Precision Wooden Lip Liner', 'short' => 'Creamy glide smudge-resistant contour pencil.', 'tags' => 'beauty,lipliner,makeup', 'category' => $beauty, 'min' => 10, 'max' => 20, 'photo' => '1586495777744-4413f21062fb'],
            ['name' => 'Full-Coverage Liquid Foundation', 'short' => 'Oil-control 24-hour flawless matte finish.', 'tags' => 'beauty,foundation,base', 'category' => $beauty, 'min' => 28, 'max' => 58, 'photo' => '1522337360788-8b13dee7a37e'],
            ['name' => 'Luminous Dewy Tinted Moisturizer', 'short' => 'Sheer everyday radiant skin tint with SPF.', 'tags' => 'beauty,skintint,foundation', 'category' => $beauty, 'min' => 22, 'max' => 46, 'photo' => '1522337094846-8a818192de1f'],
            ['name' => 'Creamy Wand Concealer', 'short' => 'Crease-proof high pigment brightening concealer.', 'tags' => 'beauty,concealer,makeup', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1522337360788-8b13dee7a37f'],
            ['name' => 'Translucent Baking Setting Powder', 'short' => 'Micro-milled shine-erasing loose face powder.', 'tags' => 'beauty,powder,setting', 'category' => $beauty, 'min' => 20, 'max' => 40, 'photo' => '1522337360788-8b13dee7a380'],
            ['name' => 'Silky Powder Cheek Blush', 'short' => 'Buildable natural flush velvet compact blush.', 'tags' => 'beauty,blush,cheeks', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1512496015851-a90fb38ba796'],
            ['name' => 'Dewy Liquid Blush Dropper', 'short' => 'Seamless melt-in longwear pinch of color blush.', 'tags' => 'beauty,blush,liquid', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1596462502278-27bfdc403348'],
            ['name' => 'Warm Sun Matte Bronzer', 'short' => 'Soft contour natural sun-kissed bronzer.', 'tags' => 'beauty,bronzer,face', 'category' => $beauty, 'min' => 18, 'max' => 38, 'photo' => '1512496015851-a90fb38ba797'],
            ['name' => 'Baked Mineral Powder Highlighter', 'short' => 'Luminescent pearlescent strobing cheek powder.', 'tags' => 'beauty,highlighter,glow', 'category' => $beauty, 'min' => 20, 'max' => 42, 'photo' => '1512496015851-a90fb38ba798'],
            ['name' => 'Neutral 12-Pan Eyeshadow Palette', 'short' => 'Buttery warm mattes and metallic shimmer shades.', 'tags' => 'beauty,eyeshadow,palette', 'category' => $beauty, 'min' => 32, 'max' => 65, 'photo' => '1605813807548-0f9bd3cf043a'],
            ['name' => 'Volumizing Waterproof Mascara', 'short' => 'Hourglass brush clump-free dramatic black lashes.', 'tags' => 'beauty,mascara,eyes', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1512437136892-3d200c4645b9'],
            ['name' => 'Felt-Tip Liquid Eyeliner Pen', 'short' => 'Smudge-proof carbon black waterproof cat-eye pen.', 'tags' => 'beauty,eyeliner,eyes', 'category' => $beauty, 'min' => 12, 'max' => 24, 'photo' => '1522337360788-8b13dee7a381'],
            ['name' => 'Ultra-Slim Micro Eyebrow Pencil', 'short' => 'Hair-like stroke retractable spoolie brow pencil.', 'tags' => 'beauty,brows,eyebrow', 'category' => $beauty, 'min' => 12, 'max' => 24, 'photo' => '1522337360788-8b13dee7a382'],
            ['name' => 'Clear Eyebrow Lamination Gel', 'short' => 'Flaked-free 16-hour extreme hold brow styler.', 'tags' => 'beauty,brows,browgel', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1522337360788-8b13dee7a383'],
            ['name' => 'Longwear Makeup Setting Spray', 'short' => 'Micro-fine mist temperature control seal spray.', 'tags' => 'beauty,settingspray,makeup', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1598440947619-58bfa9df0e3c'],
            ['name' => 'Pore-Minimizing Face Primer', 'short' => 'Blurring velvet silicone smoothing base.', 'tags' => 'beauty,primer,makeup', 'category' => $beauty, 'min' => 18, 'max' => 38, 'photo' => '1598440947619-58bfa9df0e3d'],
            ['name' => 'Argan Oil Deep Repair Hair Serum', 'short' => 'Frizz control heat-protecting shine hair drops.', 'tags' => 'beauty,haircare,serum', 'category' => $beauty, 'min' => 22, 'max' => 45, 'photo' => '1526947425960-945c6e72858f'],
            ['name' => 'Keratin Intensive Hair Mask Tub', 'short' => 'Protein treatment for damaged bleached hair.', 'tags' => 'beauty,hairmask,haircare', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1526947425960-945c6e728590'],
            ['name' => 'Biotin Volumizing Shampoo', 'short' => 'Thickening color-safe root lifting cleanser.', 'tags' => 'beauty,shampoo,haircare', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1535585209827-a15fcdbc4c2d'],
            ['name' => 'Coconut Moisture Conditioner', 'short' => 'Hydrating detangling rich formula cream.', 'tags' => 'beauty,conditioner,haircare', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1535585209827-a15fcdbc4c2e'],
            ['name' => 'Invisible Dry Shampoo Aerosol', 'short' => 'Oil absorbing starch powder volume spray.', 'tags' => 'beauty,dryshampoo,hair', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1535585209827-a15fcdbc4c2f'],
            ['name' => 'Thermal Heat Shield Spray 450°F', 'short' => 'Pre-styling flat iron protective mist.', 'tags' => 'beauty,haircare,spray', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1535585209827-a15fcdbc4c30'],
            ['name' => 'Exfoliating Brown Sugar Body Scrub', 'short' => 'Sweet almond oil smoothing polish jar.', 'tags' => 'beauty,bodyscrub,bath', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1540555700478-4be289fbecef'],
            ['name' => 'Whipped Shea Butter Body Lotion', 'short' => 'Fast-absorbing 48-hour moisture pump lotion.', 'tags' => 'beauty,bodylotion,skincare', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1556228720-195a672e8a05'],
            ['name' => 'Glow Dry Body Oil Bottle', 'short' => 'Golden shimmer shimmering satin dry oil.', 'tags' => 'beauty,bodyoil,glow', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1608248597359-07b911be249a'],
            ['name' => 'French Lavender Bath Salts', 'short' => 'Pure Epsom muscle relaxing bath crystals.', 'tags' => 'beauty,bath,salts', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1540555700478-4be289fbece0'],
            ['name' => 'Bergamot Scented Shower Gel', 'short' => 'Botanical aromatic rich foaming body wash.', 'tags' => 'beauty,bodywash,shower', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1535585209827-a15fcdbc4c31'],
            ['name' => 'Deep Hydration Hand Cream', 'short' => 'Pocket tube non-greasy rough skin remedy.', 'tags' => 'beauty,handcream,skincare', 'category' => $beauty, 'min' => 10, 'max' => 20, 'photo' => '1556228720-195a672e8a06'],
            ['name' => 'Jojoba Cuticle Oil Dropper', 'short' => 'Vitamin E nail bed strengthening essence.', 'tags' => 'beauty,nails,cuticle', 'category' => $beauty, 'min' => 11, 'max' => 22, 'photo' => '1608248597359-07b911be249b'],
            ['name' => 'Longwear Gel Nail Polish', 'short' => 'No UV lamp required chip-resistant glossy polish.', 'tags' => 'beauty,nailpolish,nails', 'category' => $beauty, 'min' => 9, 'max' => 18, 'photo' => '1522337360788-8b13dee7a384'],
            ['name' => 'Natural Jade Facial Roller', 'short' => 'Cooling stone lymph drainage face massager.', 'tags' => 'beauty,tools,jaderoller', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1512496015851-a90fb38ba799'],
            ['name' => 'Rose Quartz Gua Sha Stone', 'short' => 'Sculpting jawline traditional healing crystal.', 'tags' => 'beauty,tools,guasha', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1512496015851-a90fb38ba79a'],
            ['name' => '10-Piece Bamboo Makeup Brush Set', 'short' => 'Cruelty-free soft synthetic hair cosmetic brushes.', 'tags' => 'beauty,tools,brushes', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1516975080664-ed2fc6a32937'],
            ['name' => 'Teardrop Beauty Blender Sponge', 'short' => 'Latex-free seamless streakless makeup sponge.', 'tags' => 'beauty,tools,sponge', 'category' => $beauty, 'min' => 8, 'max' => 16, 'photo' => '1609357605129-26f69abbfdb8'],
            ['name' => 'Rose Gold Eyelash Curler', 'short' => 'Ergonomic silicone pad lift curling tool.', 'tags' => 'beauty,tools,lashes', 'category' => $beauty, 'min' => 10, 'max' => 20, 'photo' => '1522337360788-8b13dee7a385'],
            ['name' => 'Eau de Parfum Santal 50ml', 'short' => 'Woody cardamom sandalwood unisex luxury perfume.', 'tags' => 'beauty,perfume,fragrance', 'category' => $beauty, 'min' => 65, 'max' => 135, 'photo' => '1592945403244-b3fbafd7f539'],
            ['name' => 'Citrus Bloom Cologne 100ml', 'short' => 'Sparkling mandarin neroli fresh summer spray.', 'tags' => 'beauty,fragrance,cologne', 'category' => $beauty, 'min' => 50, 'max' => 105, 'photo' => '1541643600914-78b084683601'],
            ['name' => 'Amber Vanilla Body Mist', 'short' => 'Warm sugary gourmand all-day body fragrance.', 'tags' => 'beauty,bodymist,fragrance', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1624613533305-28d421d70875'],
            ['name' => 'Soy Wax Aromatherapy Candle', 'short' => 'Lavender chamomile clean burning relaxation candle.', 'tags' => 'beauty,candle,spa', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1540555700478-4be289fbece1'],
            ['name' => 'Matcha Green Tea Clay Mask', 'short' => 'Pore clearing detox purifying face cream.', 'tags' => 'beauty,facemask,matcha', 'category' => $beauty, 'min' => 20, 'max' => 40, 'photo' => '1567928805162-f6720f4c0d05'],
            ['name' => 'Propolis Honey Barrier Ampoule', 'short' => 'Immunity boosting glowing royal jelly essence.', 'tags' => 'beauty,ampoule,kbeauty', 'category' => $beauty, 'min' => 24, 'max' => 48, 'photo' => '1620916566398-39f1143ab7c0'],
            ['name' => 'Lip Plumping Collagen Oil', 'short' => 'Peppermint tingling glossy lip repair oil.', 'tags' => 'beauty,lipoil,gloss', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1586495777744-4413f21062fc'],
            ['name' => 'Tinted Brow Wax Pomade', 'short' => 'Sculpting waterproof smudge-proof brow pot.', 'tags' => 'beauty,brows,pomade', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1522337360788-8b13dee7a386'],
            ['name' => 'Illuminating Glow Primer', 'short' => 'Champagne radiance enhancing pre-makeup base.', 'tags' => 'beauty,primer,glow', 'category' => $beauty, 'min' => 20, 'max' => 40, 'photo' => '1598440947619-58bfa9df0e3e'],
            ['name' => 'Sea Salt Wave Spray', 'short' => 'Beach texture matte finish hair styling mist.', 'tags' => 'beauty,haircare,styling', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1535585209827-a15fcdbc4c32'],
            ['name' => 'Purifying Tea Tree Spot Oil', 'short' => 'Organic clarifying redness target treatment.', 'tags' => 'beauty,teatree,skincare', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1608248597359-07b911be249c'],
            ['name' => 'Moisturizing Cream Bath Bomb', 'short' => 'Epsom salt essential oil bubbling bath sphere.', 'tags' => 'beauty,bathbomb,spa', 'category' => $beauty, 'min' => 7, 'max' => 15, 'photo' => '1540555700478-4be289fbece2'],
            ['name' => 'Silk Pillowcase for Skin & Hair', 'short' => '100% Mulberry silk anti-frizz anti-crease case.', 'tags' => 'beauty,silk,accessories', 'category' => $beauty, 'min' => 30, 'max' => 60, 'photo' => '1522337360788-8b13dee7a387'],
            ['name' => 'Volumizing Lash Primer', 'short' => 'White peptide base conditioning mascara prep.', 'tags' => 'beauty,lashes,primer', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1522337360788-8b13dee7a388'],
            ['name' => 'Cream Contour Stick', 'short' => 'Blendable cool-toned jaw sculpt stick.', 'tags' => 'beauty,contour,makeup', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1512496015851-a90fb38ba79b'],
            ['name' => 'Color Correcting Palette', 'short' => 'Green, peach, and lavender cream corrector pots.', 'tags' => 'beauty,corrector,makeup', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1512496015851-a90fb38ba79c'],
            ['name' => 'Silicone Sonic Face Cleanser', 'short' => 'Vibrating waterproof silicone pore brush tool.', 'tags' => 'beauty,tools,skincare', 'category' => $beauty, 'min' => 28, 'max' => 58, 'photo' => '1522337360788-8b13dee7a389'],
            ['name' => 'Arnica Muscle Relief Rub', 'short' => 'Menthol cooling herbal post-workout muscle balm.', 'tags' => 'beauty,balm,bodycare', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1556228720-195a672e8a07'],
            ['name' => 'Nude Nail Polish Trio', 'short' => '3 salon-formula neutral sheer gloss polishes.', 'tags' => 'beauty,nails,polish', 'category' => $beauty, 'min' => 20, 'max' => 40, 'photo' => '1522337360788-8b13dee7a38a'],
            ['name' => 'Collagen Peptides Facial Cream', 'short' => 'Firming smoothing elastic day face cream.', 'tags' => 'beauty,collagen,cream', 'category' => $beauty, 'min' => 26, 'max' => 54, 'photo' => '1556228720-195a672e8a08'],
            ['name' => 'Faux Mink Wispy False Lashes', 'short' => 'Flexible clear band reusable lash pair.', 'tags' => 'beauty,lashes,makeup', 'category' => $beauty, 'min' => 10, 'max' => 20, 'photo' => '1522337360788-8b13dee7a38b'],
            ['name' => 'Botanical Hand Wash Pump', 'short' => 'Cedarwood and citrus essential oil hand soap.', 'tags' => 'beauty,handwash,soap', 'category' => $beauty, 'min' => 14, 'max' => 28, 'photo' => '1535585209827-a15fcdbc4c33'],
            ['name' => 'Shimmer Loose Pigment Pot', 'short' => 'Multi-chrome sparkling eye and body dust.', 'tags' => 'beauty,eyeshadow,pigment', 'category' => $beauty, 'min' => 12, 'max' => 25, 'photo' => '1512496015851-a90fb38ba79d'],
            ['name' => 'Purifying Scalp Scrub', 'short' => 'Sea salt and rosemary buildup-removing paste.', 'tags' => 'beauty,scalp,haircare', 'category' => $beauty, 'min' => 22, 'max' => 44, 'photo' => '1540555700478-4be289fbece3'],
            ['name' => 'Overnight Blemish Drying Lotion', 'short' => 'Calamine sulfur target spot treatment vial.', 'tags' => 'beauty,acne,skincare', 'category' => $beauty, 'min' => 17, 'max' => 34, 'photo' => '1601049541289-9b1b7bbbfe1b'],
            ['name' => 'Velvet Powder Puff Set', 'short' => 'Triangle soft makeup baking sponges.', 'tags' => 'beauty,tools,sponge', 'category' => $beauty, 'min' => 7, 'max' => 14, 'photo' => '1512496015851-a90fb38ba79e'],
            ['name' => 'Charcoal Whitening Tooth Powder', 'short' => 'Enamel safe activated coconut polishing dust.', 'tags' => 'beauty,oralcare,charcoal', 'category' => $beauty, 'min' => 12, 'max' => 24, 'photo' => '1567928805162-f6720f4c0d06'],
            ['name' => 'Hydrating Coconut Milk Bath Soak', 'short' => 'Dead Sea mineral calming bath soak bag.', 'tags' => 'beauty,bath,soak', 'category' => $beauty, 'min' => 16, 'max' => 32, 'photo' => '1540555700478-4be289fbece4'],
            ['name' => 'Longwear Waterproof Gel Eyeliner', 'short' => 'Twist-up high-pigment smudger pencil.', 'tags' => 'beauty,eyeliner,eyes', 'category' => $beauty, 'min' => 13, 'max' => 26, 'photo' => '1522337360788-8b13dee7a38c'],
            ['name' => 'Bamboo Detangling Paddle Brush', 'short' => 'Static-free ball tip anti-breakage hair brush.', 'tags' => 'beauty,hairbrush,tools', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1526947425960-945c6e728591'],
            ['name' => 'Aloe Vera 99% Soothing Gel', 'short' => 'Sunburn cooling multi-use hydrating gel tub.', 'tags' => 'beauty,aloevera,skincare', 'category' => $beauty, 'min' => 10, 'max' => 20, 'photo' => '1556228720-195a672e8a09'],
            ['name' => 'Lip Exfoliating Sugar Scrub', 'short' => 'Vanilla peppermint buffing lip polish pot.', 'tags' => 'beauty,lipcare,scrub', 'category' => $beauty, 'min' => 11, 'max' => 22, 'photo' => '1586495777744-4413f21062fd'],
            ['name' => 'Under-Eye Gold Collagen Patches', 'short' => '30 pairs 24K gold cooling hydrogel pads.', 'tags' => 'beauty,eyepatches,skincare', 'category' => $beauty, 'min' => 18, 'max' => 36, 'photo' => '1601049541289-9b1b7bbbfe1c'],
            ['name' => 'Glass Cuticle Pusher & File', 'short' => 'Sanitary precision tempered glass nail file.', 'tags' => 'beauty,tools,nails', 'category' => $beauty, 'min' => 9, 'max' => 18, 'photo' => '1522337360788-8b13dee7a38d'],
            ['name' => 'Rosemary Mint Scalp Oil', 'short' => 'Biotin-infused root strengthening drops.', 'tags' => 'beauty,hairgrowth,haircare', 'category' => $beauty, 'min' => 15, 'max' => 30, 'photo' => '1608248597359-07b911be249d'],
            ['name' => 'Matte Bronzing Powder Stick', 'short' => 'Cream-to-powder warm sun sculpted stick.', 'tags' => 'beauty,bronzer,makeup', 'category' => $beauty, 'min' => 17, 'max' => 34, 'photo' => '1512496015851-a90fb38ba79f'],
            ['name' => 'Deep Sea Minerals Face Scrub', 'short' => 'Micro-fine polishing exfoliating facial buffer.', 'tags' => 'beauty,facescrub,skincare', 'category' => $beauty, 'min' => 19, 'max' => 38, 'photo' => '1540555700478-4be289fbece5'],
            ['name' => 'Peptide Plumping Lip Balm', 'short' => 'Everyday tube gloss restorative barrier balm.', 'tags' => 'beauty,lipbalm,skincare', 'category' => $beauty, 'min' => 13, 'max' => 26, 'photo' => '1586495777744-4413f21062fe'],
        ];

        foreach ($products as $item) {
            $slug = Str::slug($item['name']);

            // Upload directly to ImgBB and get the public CDN URL
            $hostedUrl = $this->uploadToImgBB($item['photo'], $item['name']);

            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'vendor_id'         => $vendor->id,
                    'name'              => $item['name'],
                    'description'       => $item['short'],
                    'short_description' => $item['short'],
                    'tags'              => $item['tags'],
                    'category_id'       => $item['category']->id,
                    'brand_id'          => $brand?->id,
                    'product_type'      => 'variable',
                    'status'            => 1,
                ]
            );

            if (!$product->wasRecentlyCreated) {
                $product->images()->updateOrCreate(
                    ['type' => 'thumb'],
                    ['name' => $slug . '.jpg', 'image_url' => $hostedUrl]
                );
                continue;
            }

            $product->images()->create([
                'name'      => $slug . '.jpg',
                'image_url' => $hostedUrl,
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

            // Brief delay to remain well within free API rate limits
            usleep(250000); // 0.25 seconds
        }
    }
}