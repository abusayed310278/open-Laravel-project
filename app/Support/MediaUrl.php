<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class MediaUrl
{
    /**
     * Resolve a media file path or URL into a fully qualified browser URL.
     * Continuously supports images across multiple storage drivers (Local, Cloudinary, Cloudflare R2).
     */
    public static function resolve(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);
        $cleanPath = null;
        $isCloudinaryUrl = str_contains($path, 'cloudinary.com');
        $isR2Url = str_contains($path, 'r2.dev') || str_contains($path, 'r2.cloudflarestorage.com');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
                $cleanPath = ltrim($matches[1], '/');
            } elseif ($isCloudinaryUrl) {
                $activeDisk = config('filesystems.default', 'public');
                if ($activeDisk === 'cloudinary') {
                    return $path;
                }
                $relative = preg_replace('#^https?://[^/]+/(?:[^/]+/)*upload/(?:v\d+/)?#i', '', $path);
                $relative = preg_replace('#^https?://[^/]+/#i', '', $relative ?: $path);
                $cleanPath = ltrim($relative, '/');
            } elseif ($isR2Url) {
                $activeDisk = config('filesystems.default', 'public');
                if ($activeDisk === 'r2') {
                    return $path;
                }
                $relative = preg_replace('#^https?://[^/]+/#i', '', $path);
                $cleanPath = ltrim($relative, '/');
            } else {
                // External link (e.g. loremflickr placeholder, external CDNs)
                return $path;
            }
        } else {
            $cleanPath = ltrim(preg_replace('#^storage/#i', '', ltrim($path, '/')), '/');
        }

        $activeDisk = config('filesystems.default', 'public');
        if ($activeDisk === 'local') {
            $activeDisk = 'public';
        }

        $cdnUrl = config('filesystems.disks.public.cdn_url') ?: (function_exists('setting') ? setting('storage_cdn_url') : null);
        $baseUrl = ! empty($cdnUrl) ? rtrim($cdnUrl, '/') . '/storage/' : rtrim(asset('storage'), '/') . '/';

        // 1. If physical file exists on local disk, serve locally regardless of active default disk
        try {
            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return $baseUrl . $cleanPath;
            }
        } catch (\Throwable) {
            // Ignore storage check exceptions
        }

        // 2. If original path is a full Cloudinary or R2 URL, return it directly if no local file override
        if ($isCloudinaryUrl || $isR2Url) {
            return $path;
        }

        // 3. If file is not on local disk, check remote disks (Cloudinary / R2) continuously
        $remoteDisks = array_unique(array_filter([
            in_array($activeDisk, ['cloudinary', 'r2'], true) ? $activeDisk : null,
            ! empty(config('filesystems.disks.cloudinary.cloud_name')) || ! empty(env('CLOUDINARY_URL')) ? 'cloudinary' : null,
            ! empty(config('filesystems.disks.r2.bucket')) || ! empty(env('R2_BUCKET')) ? 'r2' : null,
        ]));

        foreach ($remoteDisks as $disk) {
            if (! $cleanPath) {
                continue;
            }
            try {
                $remoteUrl = Storage::disk($disk)->url($cleanPath);
                if (str_starts_with($remoteUrl, 'http://') || str_starts_with($remoteUrl, 'https://')) {
                    return $remoteUrl;
                }
            } catch (\Throwable) {
            }
        }

        // 4. Default fallback asset resolution
        return $baseUrl . ($cleanPath ?: $path);
    }

    /**
     * Delete a file safely from its respective storage driver (Cloudinary, Cloudflare R2, or Local Public).
     */
    public static function delete(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        try {
            $cleanPath = null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
                    $cleanPath = ltrim($matches[1], '/');
                }
            } else {
                $cleanPath = ltrim(preg_replace('#^storage/#i', '', ltrim($path, '/')), '/');
            }

            if (str_contains($path, 'cloudinary.com')) {
                $relative = preg_replace('#^https?://[^/]+/(?:[^/]+/)*upload/(?:v\d+/)?#i', '', $path);
                $relative = preg_replace('#^https?://[^/]+/#i', '', $relative ?: $path);
                Storage::disk('cloudinary')->delete(ltrim($relative, '/'));
            } elseif (str_contains($path, 'r2.dev') || str_contains($path, 'r2')) {
                $relative = preg_replace('#^https?://[^/]+/#i', '', $path);
                Storage::disk('r2')->delete(ltrim($relative, '/'));
            }

            if (! empty($cleanPath)) {
                Storage::disk('public')->delete($cleanPath);
            } elseif (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
                Storage::disk('public')->delete(ltrim($path, '/'));
            }
        } catch (\Throwable) {
            // Ignore deletion errors gracefully
        }
    }
}
