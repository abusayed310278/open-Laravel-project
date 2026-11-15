<?php

namespace App\Jobs;

use App\Models\ProductImage;
use App\Services\ImageBackgroundRemover;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessProductImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public readonly ProductImage $productImage) {}

    public function handle(ImageBackgroundRemover $remover): void
    {
        $image = $this->productImage;
        if (! $image->exists) {
            return;
        }

        $url = $image->url();
        $sourcePath = $this->download($url);

        if (! $sourcePath || ! file_exists($sourcePath)) {
            Log::warning("ProcessProductImageJob: Could not download image {$image->id} from {$url}");
            return;
        }

        try {
            // 1. Remove plain studio background if applicable
            $cutout = $remover->process($sourcePath);

            $disk = config('filesystems.default', 'public');
            if ($disk === 'local') {
                $disk = 'public';
            }

            if ($cutout !== null && file_exists($cutout)) {
                $filename = Str::random(40) . '.png';
                $storedPath = Storage::disk($disk)->putFileAs('products', $cutout, $filename);
                @unlink($cutout);

                if ($storedPath) {
                    $newPath = in_array($disk, ['cloudinary', 'r2'], true)
                        ? Storage::disk($disk)->url($storedPath)
                        : 'products/' . $filename;

                    $image->update(['path' => $newPath]);
                }
            }

            // 2. Generate optimized WebP version if GD supports it
            if (function_exists('imagewebp')) {
                $currentLocalPath = Storage::disk($disk)->path(
                    preg_replace('#^https?://[^/]+/storage/#', '', $image->path)
                );

                if (file_exists($currentLocalPath)) {
                    $webpTarget = preg_replace('/\.(png|jpe?g)$/i', '.webp', $currentLocalPath);
                    if (! file_exists($webpTarget)) {
                        $raw = @file_get_contents($currentLocalPath);
                        $gdImg = $raw ? @imagecreatefromstring($raw) : false;
                        if ($gdImg) {
                            imagepalettetotruecolor($gdImg);
                            imagealphablending($gdImg, true);
                            imagesavealpha($gdImg, true);
                            imagewebp($gdImg, $webpTarget, 82);
                            imagedestroy($gdImg);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("ProcessProductImageJob failed for image #{$image->id}: " . $e->getMessage());
        } finally {
            if ($sourcePath && file_exists($sourcePath)) {
                @unlink($sourcePath);
            }
        }
    }

    private function download(string $url): ?string
    {
        try {
            if (str_starts_with($url, '/') || ! str_starts_with($url, 'http')) {
                $localPath = public_path(ltrim($url, '/'));
                if (file_exists($localPath)) {
                    $temp = tempnam(sys_get_temp_dir(), 'pimg_');
                    copy($localPath, $temp);
                    return $temp;
                }
            }

            $response = Http::timeout(30)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $temp = tempnam(sys_get_temp_dir(), 'pimg_');
            file_put_contents($temp, $response->body());

            return $temp;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

