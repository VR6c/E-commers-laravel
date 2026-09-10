<?php

namespace App\Services\Shared;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * Upload an image, compress and generate a WebP version, and return stored path.
     */
    public function uploadImage(UploadedFile $image, string $folder): string
    {
        $path = $image->store($folder, 'public');

        // Automatically create WebP optimized version
        $fullPath = Storage::disk('public')->path($path);
        $this->convertToWebp($fullPath);

        return $path;
    }

    /**
     * Convert an image file to WebP format.
     */
    public function convertToWebp(string $sourcePath, ?string $destinationPath = null, int $quality = 82): ?string
    {
        if (! file_exists($sourcePath) || ! function_exists('imagewebp')) {
            return null;
        }

        $destinationPath = $destinationPath ?: preg_replace('/\.(png|jpe?g)$/i', '.webp', $sourcePath);
        if ($destinationPath === $sourcePath) {
            return null;
        }

        $info = @getimagesize($sourcePath);
        if (! $info) {
            return null;
        }

        $mime = $info['mime'] ?? '';
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default      => null,
        };

        if (! $image) {
            return null;
        }

        // Handle transparency for PNG
        if ($mime === 'image/png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $success = imagewebp($image, $destinationPath, $quality);
        imagedestroy($image);

        return $success ? $destinationPath : null;
    }

    /**
     * Delete an image and its WebP counterpart from the public disk.
     * Accepts either a raw storage path or a URL containing 'storage/'.
     */
    public function deleteImage(string $imageUrl): bool
    {
        $imagePath = str_replace('storage/', '', $imageUrl);

        // Also clean up any WebP sibling
        $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $imagePath);
        if ($webpPath !== $imagePath && Storage::disk('public')->exists($webpPath)) {
            Storage::disk('public')->delete($webpPath);
        }

        return Storage::disk('public')->delete($imagePath);
    }
}
