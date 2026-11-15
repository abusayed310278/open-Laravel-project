<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{
    private const MAX_IMAGE_DIMENSION = 2000;
    private const WEBP_QUALITY = 82;

    public static function store(UploadedFile $file, string $directory, ?string $disk = null): string
    {
        $activeDisk = config('filesystems.default', 'public');
        if ($activeDisk === 'local') {
            $activeDisk = 'public';
        }

        $targetDisk = (! empty($disk) && $disk !== 'local') ? $disk : $activeDisk;

        $realPath = $file->getRealPath();
        $sourcePath = ($realPath && file_exists($realPath)) ? $realPath : $file->getPathname();
        $mime = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'png'));

        // Check if image can be optimized to WebP
        $isConvertibleImage = in_array($extension, ['jpg', 'jpeg', 'png', 'bmp'], true) 
            || str_starts_with($mime, 'image/') && !in_array($extension, ['gif', 'svg', 'ico'], true);

        if ($isConvertibleImage && function_exists('imagewebp')) {
            $converted = self::convertToWebP($sourcePath);
            if ($converted !== null) {
                $filename = Str::random(40) . '.webp';
                $targetPath = rtrim($directory, '/') . '/' . $filename;
                Storage::disk($targetDisk)->put($targetPath, $converted);

                return self::formatResultUrl($targetDisk, $targetPath, 'webp');
            }
        }

        // Default direct file storage
        $filename = Str::random(40) . '.' . $extension;
        $targetPath = rtrim($directory, '/') . '/' . $filename;

        Storage::disk($targetDisk)->put($targetPath, file_get_contents($sourcePath));

        return self::formatResultUrl($targetDisk, $targetPath, $extension);
    }

    /**
     * Converts and compresses an image to WebP format with optional downscaling.
     */
    private static function convertToWebP(string $sourcePath): ?string
    {
        try {
            $raw = @file_get_contents($sourcePath);
            if ($raw === false) {
                return null;
            }

            $img = @imagecreatefromstring($raw);
            if (!$img) {
                return null;
            }

            $width = imagesx($img);
            $height = imagesy($img);

            // Resize if exceeds max dimension
            if ($width > self::MAX_IMAGE_DIMENSION || $height > self::MAX_IMAGE_DIMENSION) {
                $ratio = min(self::MAX_IMAGE_DIMENSION / $width, self::MAX_IMAGE_DIMENSION / $height);
                $newWidth = (int) round($width * $ratio);
                $newHeight = (int) round($height * $ratio);

                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($img);
                $img = $resized;
            } else {
                imagealphablending($img, false);
                imagesavealpha($img, true);
            }

            ob_start();
            imagewebp($img, null, self::WEBP_QUALITY);
            $webpData = ob_get_clean();
            imagedestroy($img);

            return $webpData ?: null;
        } catch (\Throwable $e) {
            Log::warning('FileUploadHelper WebP conversion failed: ' . $e->getMessage());
            return null;
        }
    }

    private static function formatResultUrl(string $targetDisk, string $targetPath, string $extension): string
    {
        if (in_array($targetDisk, ['cloudinary', 'r2'], true)) {
            $url = Storage::disk($targetDisk)->url($targetPath);
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                if ($targetDisk === 'cloudinary' && in_array(strtolower($extension), ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi', 'mkv'], true)) {
                    $url = str_replace('/image/upload/', '/video/upload/', $url);
                }
                return $url;
            }
        }

        return $targetPath;
    }
}
