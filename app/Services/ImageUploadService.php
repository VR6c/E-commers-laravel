<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    /**
     * Upload an uploaded file to ImgBB cloud storage,
     * falling back to local public storage disk if ImgBB is not configured or fails.
     *
     * @param UploadedFile $file
     * @param string $fallbackFolder
     * @return string Public URL (if ImgBB) or relative storage path (if local)
     */
    public static function upload(UploadedFile $file, string $fallbackFolder = 'banners'): string
    {
        $apiKey = config('services.imgbb.key') ?? env('IMGBB_API_KEY');

        if (!empty($apiKey)) {
            try {
                $response = Http::timeout(30)
                    ->attach(
                        'image',
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName() ?: 'upload.jpg'
                    )
                    ->post('https://api.imgbb.com/1/upload', [
                        'key' => $apiKey,
                    ]);

                if ($response->successful() && $response->json('success')) {
                    $url = $response->json('data.url');
                    Log::info('Image successfully uploaded to ImgBB: ' . $url);
                    return $url;
                }

                Log::warning('ImgBB upload returned unsuccessful response: ' . $response->body());
            } catch (\Exception $e) {
                Log::error('ImgBB upload exception: ' . $e->getMessage());
            }
        }

        // Local storage fallback (useful when offline or developing locally)
        return $file->store($fallbackFolder, 'public');
    }
}
