<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixDuplicateProductImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_fix_duplicate_images_command_updates_duplicate_thumbnails(): void
    {
        $category = Category::create(['name' => 'Beauty & Skincare', 'slug' => 'beauty-skincare', 'status' => true]);
        $vendor = Vendor::create([
            'name'     => 'Test Vendor',
            'email'    => 'testvendor@example.com',
            'password' => bcrypt('password'),
            'status'   => 'active',
        ]);

        // Create 2 products with identical image URLs (mimicking the seeder bug)
        $p1 = Product::create([
            'vendor_id'         => $vendor->id,
            'category_id'       => $category->id,
            'name'              => 'Silky Powder Cheek Blush',
            'slug'              => 'silky-powder-cheek-blush',
            'description'       => 'Blush description',
            'short_description' => 'Blush short',
            'product_type'      => 'simple',
            'status'            => 1,
        ]);

        $p2 = Product::create([
            'vendor_id'         => $vendor->id,
            'category_id'       => $category->id,
            'name'              => 'Velvet Powder Puff Set',
            'slug'              => 'velvet-powder-puff-set',
            'description'       => 'Puff description',
            'short_description' => 'Puff short',
            'product_type'      => 'simple',
            'status'            => 1,
        ]);

        $sharedDuplicateUrl = 'https://i.ibb.co/chjQFZ0k/silky-powder-cheek-blush.jpg';

        $img1 = ProductImage::create([
            'product_id' => $p1->id,
            'name'       => 'silky-powder-cheek-blush.jpg',
            'image_url'  => $sharedDuplicateUrl,
            'type'       => 'thumb',
        ]);

        $img2 = ProductImage::create([
            'product_id' => $p2->id,
            'name'       => 'velvet-powder-puff-set.jpg',
            'image_url'  => $sharedDuplicateUrl,
            'type'       => 'thumb',
        ]);

        // 1. Dry run should report duplicates without modifying DB
        $this->artisan('products:fix-duplicate-images', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertEquals($sharedDuplicateUrl, $img1->fresh()->image_url);
        $this->assertEquals($sharedDuplicateUrl, $img2->fresh()->image_url);

        // 2. Execute command with confirmation
        $this->artisan('products:fix-duplicate-images')
            ->expectsConfirmation('Proceed with updating 2 product images in the database?', 'yes')
            ->assertSuccessful();

        $freshImg1 = $img1->fresh();
        $freshImg2 = $img2->fresh();

        // Both images must now be distinct
        $this->assertNotEquals($freshImg1->image_url, $freshImg2->image_url);
        $this->assertStringContainsString('images.unsplash.com', $freshImg1->image_url);
        $this->assertStringContainsString('images.unsplash.com', $freshImg2->image_url);
    }

    public function test_api_fix_duplicate_images_endpoint(): void
    {
        $category = Category::create(['name' => 'Beauty & Skincare', 'slug' => 'beauty-skincare', 'status' => true]);
        $vendor = Vendor::create([
            'name'     => 'Test Vendor 2',
            'email'    => 'testvendor2@example.com',
            'password' => bcrypt('password'),
            'status'   => 'active',
        ]);

        $p1 = Product::create([
            'vendor_id'         => $vendor->id,
            'category_id'       => $category->id,
            'name'              => 'Berry Lip Sleeping Mask',
            'slug'              => 'berry-lip-sleeping-mask',
            'description'       => 'Lip mask',
            'short_description' => 'Lip mask',
            'product_type'      => 'simple',
            'status'            => 1,
        ]);

        $p2 = Product::create([
            'vendor_id'         => $vendor->id,
            'category_id'       => $category->id,
            'name'              => 'Peptide Plumping Lip Balm',
            'slug'              => 'peptide-plumping-lip-balm',
            'description'       => 'Lip balm',
            'short_description' => 'Lip balm',
            'product_type'      => 'simple',
            'status'            => 1,
        ]);

        $sharedUrl = 'https://i.ibb.co/ZR5PLkhv/berry-lip-sleeping-mask.jpg';

        $img1 = ProductImage::create([
            'product_id' => $p1->id,
            'name'       => 'berry-lip-sleeping-mask.jpg',
            'image_url'  => $sharedUrl,
            'type'       => 'thumb',
        ]);

        $img2 = ProductImage::create([
            'product_id' => $p2->id,
            'name'       => 'peptide-plumping-lip-balm.jpg',
            'image_url'  => $sharedUrl,
            'type'       => 'thumb',
        ]);

        // Dry run via API
        $dryRunResponse = $this->getJson('/api/fix-duplicate-images?dry_run=1');
        $dryRunResponse->assertStatus(200)
            ->assertJson([
                'status'  => 'preview',
                'dry_run' => true,
            ]);

        // Real run via API
        $response = $this->postJson('/api/fix-duplicate-images');
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertNotEquals($img1->fresh()->image_url, $img2->fresh()->image_url);
    }
}
