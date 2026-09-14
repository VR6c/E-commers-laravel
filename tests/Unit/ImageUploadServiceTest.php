<?php

namespace Tests\Unit;

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.imgbb.key', 'mock_imgbb_key_12345');
    }

    public function test_upload_successful_to_imgbb_returns_remote_url(): void
    {
        Http::fake([
            'https://api.imgbb.com/*' => Http::response([
                'data'    => [
                    'url'         => 'https://i.ibb.co/xyz123/product.jpg',
                    'display_url' => 'https://i.ibb.co/xyz123/product.jpg',
                ],
                'success' => true,
                'status'  => 200,
            ], 200),
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('product.jpg', 200, 200);

        $url = ImageUploadService::upload($file, 'products');

        $this->assertEquals('https://i.ibb.co/xyz123/product.jpg', $url);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.imgbb.com/1/upload')
                && $request['name'] === 'product'
                && !empty($request['image']);
        });
    }

    public function test_upload_falls_back_to_local_storage_when_imgbb_fails(): void
    {
        Http::fake([
            'https://api.imgbb.com/*' => Http::response([
                'status_code' => 400,
                'error'       => ['message' => 'Invalid API key'],
            ], 400),
        ]);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('banner.jpg', 100, 100);

        $storedPath = ImageUploadService::upload($file, 'banners');

        $this->assertStringStartsWith('banners/', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_upload_falls_back_when_api_key_is_missing(): void
    {
        Config::set('services.imgbb.key', null);
        putenv('IMGBB_API_KEY=');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('category.jpg', 100, 100);

        $storedPath = ImageUploadService::upload($file, 'categories');

        $this->assertStringStartsWith('categories/', $storedPath);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_upload_from_local_file_path_string(): void
    {
        Http::fake([
            'https://api.imgbb.com/*' => Http::response([
                'data'    => [
                    'url' => 'https://i.ibb.co/abc789/migrated_file.png',
                ],
                'success' => true,
                'status'  => 200,
            ], 200),
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'imgbb_test_') . '.png';
        file_put_contents($tmpFile, 'fake_png_data_content');

        try {
            $url = ImageUploadService::upload($tmpFile, 'migrated');
            $this->assertEquals('https://i.ibb.co/abc789/migrated_file.png', $url);
        } finally {
            if (file_exists($tmpFile)) {
                @unlink($tmpFile);
            }
        }
    }

    public function test_returns_already_remote_urls_unchanged(): void
    {
        $existingRemote = 'https://i.ibb.co/already/uploaded.jpg';
        $result = ImageUploadService::upload($existingRemote, 'products');

        $this->assertEquals($existingRemote, $result);
    }

    public function test_is_remote_url_and_is_imgbb_url_helpers(): void
    {
        $this->assertTrue(ImageUploadService::isRemoteUrl('https://i.ibb.co/sample/img.jpg'));
        $this->assertTrue(ImageUploadService::isRemoteUrl('http://example.com/logo.png'));
        $this->assertTrue(ImageUploadService::isRemoteUrl('//cdn.example.com/banner.jpg'));
        $this->assertFalse(ImageUploadService::isRemoteUrl('products/sample.jpg'));
        $this->assertFalse(ImageUploadService::isRemoteUrl(null));

        $this->assertTrue(ImageUploadService::isImgbbUrl('https://i.ibb.co/sample/img.jpg'));
        $this->assertTrue(ImageUploadService::isImgbbUrl('https://imgbb.com/xyz'));
        $this->assertFalse(ImageUploadService::isImgbbUrl('https://othercdn.com/sample.jpg'));
        $this->assertFalse(ImageUploadService::isImgbbUrl(null));
    }
}
