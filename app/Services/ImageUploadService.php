<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Upload an image file (UploadedFile or local file path) to ImgBB cloud storage,
     * falling back to local public storage disk if ImgBB is not configured or fails.
     *
     * @param UploadedFile|string $file
     * @param string $fallbackFolder
     * @param string|null $customName
     * @return string Public URL (if ImgBB) or relative storage path (if local)
     */
    public static function upload(UploadedFile|string $file, string $fallbackFolder = 'uploads', ?string $customName = null): string
    {
        // If already a remote URL, return as-is
        if (is_string($file) && self::isRemoteUrl($file)) {
            return $file;
        }

        $apiKey = config('services.imgbb.key') ?? env('IMGBB_API_KEY');

        // Resolve binary contents and filename
        $content = null;
        $originalName = 'image.jpg';

        if ($file instanceof UploadedFile) {
            $realPath = $file->getRealPath();
            $content = ($realPath && file_exists($realPath)) ? file_get_contents($realPath) : null;
            $originalName = $file->getClientOriginalName() ?: 'upload.jpg';
        } elseif (is_string($file)) {
            $resolvedPath = self::resolveLocalPath($file);
            if ($resolvedPath && file_exists($resolvedPath)) {
                $content = file_get_contents($resolvedPath);
                $originalName = basename($resolvedPath);
            }
        }

        $name = $customName ?: pathinfo($originalName, PATHINFO_FILENAME);

        // Upload to ImgBB if API key and file content are valid
        if (!empty($apiKey) && !empty($content)) {
            try {
                $response = Http::timeout(30)
                    ->asForm()
                    ->post('https://api.imgbb.com/1/upload?key=' . urlencode($apiKey), [
                        'image' => base64_encode($content),
                        'name'  => $name,
                    ]);

                if ($response->successful() && $response->json('success')) {
                    $url = $response->json('data.url') ?? $response->json('data.display_url');
                    if (!empty($url)) {
                        Log::info('Image successfully uploaded to ImgBB: ' . $url);
                        return $url;
                    }
                }

                Log::warning('ImgBB upload returned unsuccessful response', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::error('ImgBB upload exception: ' . $e->getMessage(), [
                    'file' => $originalName,
                ]);
            }
        }

        // Local storage fallback
        if ($file instanceof UploadedFile) {
            return $file->store($fallbackFolder, 'public');
        }

        // If string file was provided, return relative path inside storage/public if possible
        if (is_string($file)) {
            return self::toRelativeStoragePath($file);
        }

        return '';
    }

    /**
     * Check whether a given path or URL is an external web link.
     */
    public static function isRemoteUrl(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return Str::startsWith($path, ['http://', 'https://', '//']);
    }

    /**
     * Check whether a given URL is hosted on ImgBB CDN.
     */
    public static function isImgbbUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return str_contains($url, 'ibb.co') || str_contains($url, 'imgbb.com');
    }

    /**
     * Resolve any relative storage or public path to an absolute filesystem path.
     */
    public static function resolveLocalPath(string $path): ?string
    {
        if (file_exists($path)) {
            return $path;
        }

        // storage/app/public/...
        $storagePublic = storage_path('app/public/' . ltrim(str_replace('storage/', '', $path), '/'));
        if (file_exists($storagePublic)) {
            return $storagePublic;
        }

        // public/storage/...
        $publicStorage = public_path('storage/' . ltrim(str_replace('storage/', '', $path), '/'));
        if (file_exists($publicStorage)) {
            return $publicStorage;
        }

        // public/...
        $publicDirect = public_path(ltrim($path, '/'));
        if (file_exists($publicDirect)) {
            return $publicDirect;
        }

        return null;
    }

    /**
     * Convert absolute or full path to storage relative path for fallback.
     */
    protected static function toRelativeStoragePath(string $path): string
    {
        $clean = str_replace([
            storage_path('app/public/'),
            public_path('storage/'),
            public_path('/'),
        ], '', $path);

        return ltrim($clean, '/');
    }
}
