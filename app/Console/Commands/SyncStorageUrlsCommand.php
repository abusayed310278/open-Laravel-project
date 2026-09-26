<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ProductImage;
use App\Models\UserProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncStorageUrlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync-urls {--disk= : Specify target disk (cloudinary, r2, public). Defaults to active disk.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload local relative images to Cloudinary/R2 and update database records with full storage URLs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDisk = $this->option('disk') ?: config('filesystems.default', 'public');
        if ($targetDisk === 'local') {
            $targetDisk = 'public';
        }
        $this->info("Starting Storage Sync to disk: [{$targetDisk}]...");

        $isRemote = in_array($targetDisk, ['cloudinary', 'r2'], true);

        // 1. Sync Product Images
        $productImages = ProductImage::all();
        $this->info("Processing {$productImages->count()} product images...");
        $pCount = 0;

        foreach ($productImages as $img) {
            $cleanPath = $this->extractCleanPath($img->path);
            if (! $cleanPath) {
                continue;
            }

            if ($isRemote && Storage::disk('public')->exists($cleanPath)) {
                try {
                    $contents = Storage::disk('public')->get($cleanPath);
                    Storage::disk($targetDisk)->put($cleanPath, $contents);
                } catch (\Throwable $e) {
                    $this->warn("Failed uploading {$cleanPath} to {$targetDisk}: " . $e->getMessage());
                }
            }

            $newPath = $this->formatPathToSave($cleanPath, $targetDisk, $isRemote);
            $img->update(['path' => $newPath]);
            $pCount++;
        }
        $this->info("Updated {$pCount} product image DB records.");

        // 2. Sync Brands
        $brands = Brand::all();
        $bCount = 0;
        foreach ($brands as $brand) {
            $cleanPath = $this->extractCleanPath($brand->logo);
            if (! $cleanPath) {
                continue;
            }

            if ($isRemote && Storage::disk('public')->exists($cleanPath)) {
                try {
                    Storage::disk($targetDisk)->put($cleanPath, Storage::disk('public')->get($cleanPath));
                } catch (\Throwable) {}
            }

            $newPath = $this->formatPathToSave($cleanPath, $targetDisk, $isRemote);
            $brand->update(['logo' => $newPath]);
            $bCount++;
        }
        $this->info("Updated {$bCount} brand logo DB records.");

        // 3. Sync Banners
        $banners = Banner::all();
        $bnCount = 0;
        foreach ($banners as $banner) {
            $cleanPath = $this->extractCleanPath($banner->image);
            if ($cleanPath) {
                if ($isRemote && Storage::disk('public')->exists($cleanPath)) {
                    try {
                        Storage::disk($targetDisk)->put($cleanPath, Storage::disk('public')->get($cleanPath));
                    } catch (\Throwable) {}
                }
                $banner->update(['image' => $this->formatPathToSave($cleanPath, $targetDisk, $isRemote)]);
                $bnCount++;
            }
        }
        $this->info("Updated {$bnCount} banner image DB records.");

        // 4. Sync Categories
        $categories = Category::all();
        $cCount = 0;
        foreach ($categories as $cat) {
            $cleanPath = $this->extractCleanPath($cat->image);
            if ($cleanPath) {
                if ($isRemote && Storage::disk('public')->exists($cleanPath)) {
                    try {
                        Storage::disk($targetDisk)->put($cleanPath, Storage::disk('public')->get($cleanPath));
                    } catch (\Throwable) {}
                }
                $cat->update(['image' => $this->formatPathToSave($cleanPath, $targetDisk, $isRemote)]);
                $cCount++;
            }
        }
        $this->info("Updated {$cCount} category image DB records.");

        // 5. Sync User Avatars
        $profiles = UserProfile::all();
        $uCount = 0;
        foreach ($profiles as $prof) {
            $cleanPath = $this->extractCleanPath($prof->avatar);
            if ($cleanPath) {
                if ($isRemote && Storage::disk('public')->exists($cleanPath)) {
                    try {
                        Storage::disk($targetDisk)->put($cleanPath, Storage::disk('public')->get($cleanPath));
                    } catch (\Throwable) {}
                }
                $prof->update(['avatar' => $this->formatPathToSave($cleanPath, $targetDisk, $isRemote)]);
                $uCount++;
            }
        }
        $this->info("Updated {$uCount} user avatar DB records.");

        $this->info("Storage URL sync completed successfully!");

        return Command::SUCCESS;
    }

    private function extractCleanPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (preg_match('#/storage/(.+)$#i', $path, $matches)) {
                return ltrim($matches[1], '/');
            } elseif (str_contains($path, 'cloudinary.com')) {
                $relative = preg_replace('#^https?://[^/]+/(?:[^/]+/)*upload/(?:v\d+/)?#i', '', $path);
                $relative = preg_replace('#^https?://[^/]+/#i', '', $relative ?: $path);
                return ltrim($relative, '/');
            } else {
                // External link (placeholders, etc.)
                return null;
            }
        }

        return ltrim(preg_replace('#^storage/#i', '', ltrim($path, '/')), '/');
    }

    private function formatPathToSave(string $cleanPath, string $targetDisk, bool $isRemote): string
    {
        if ($isRemote) {
            try {
                $url = Storage::disk($targetDisk)->url($cleanPath);
                if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                    return $url;
                }
            } catch (\Throwable) {}
        }

        return $cleanPath;
    }
}
