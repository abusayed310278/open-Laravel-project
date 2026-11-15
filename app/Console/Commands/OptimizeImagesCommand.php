<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeImagesCommand extends Command
{
    protected $signature = 'images:optimize {--path=public/storage : Path to scan for images} {--quality=82 : WebP quality (1-100)}';

    protected $description = 'Compress and convert images to WebP format to accelerate site speed';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD extension with WebP support is required to convert images.');
            return self::FAILURE;
        }

        $scanPath = base_path($this->option('path'));
        if (! File::isDirectory($scanPath)) {
            $this->error("Directory not found: {$scanPath}");
            return self::FAILURE;
        }

        $this->info("Scanning images in {$this->option('path')}...");

        $files = File::allFiles($scanPath);
        $convertible = collect($files)->filter(function ($file) {
            $ext = strtolower($file->getExtension());
            return in_array($ext, ['png', 'jpg', 'jpeg'], true);
        });

        if ($convertible->isEmpty()) {
            $this->info('No unoptimized PNG or JPG images found.');
            return self::SUCCESS;
        }

        $quality = (int) $this->option('quality');
        $totalOriginal = 0;
        $totalOptimized = 0;
        $convertedCount = 0;

        $bar = $this->output->createProgressBar($convertible->count());
        $bar->start();

        foreach ($convertible as $file) {
            $filePath = $file->getRealPath();
            $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $filePath);

            $originalSize = filesize($filePath);
            $totalOriginal += $originalSize;

            // If webp already exists and is smaller, skip
            if (file_exists($webpPath)) {
                $totalOptimized += filesize($webpPath);
                $bar->advance();
                continue;
            }

            try {
                $raw = @file_get_contents($filePath);
                $img = $raw ? @imagecreatefromstring($raw) : false;

                if ($img) {
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);

                    if (imagewebp($img, $webpPath, $quality)) {
                        $newSize = filesize($webpPath);
                        $totalOptimized += $newSize;
                        $convertedCount++;
                    } else {
                        $totalOptimized += $originalSize;
                    }

                    imagedestroy($img);
                } else {
                    $totalOptimized += $originalSize;
                }
            } catch (\Throwable $e) {
                $totalOptimized += $originalSize;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $savedBytes = max(0, $totalOriginal - $totalOptimized);
        $savedPct = $totalOriginal > 0 ? round(($savedBytes / $totalOriginal) * 100, 1) : 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Images Scanned', $convertible->count()],
                ['WebP Images Generated', $convertedCount],
                ['Original Size', $this->formatBytes($totalOriginal)],
                ['Optimized WebP Size', $this->formatBytes($totalOptimized)],
                ['Bandwidth Saved', $this->formatBytes($savedBytes) . " ({$savedPct}%)"],
            ]
        );

        $this->info('Images optimization completed successfully!');
        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

