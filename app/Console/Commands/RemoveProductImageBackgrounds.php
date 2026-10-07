<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ImageBackgroundRemover;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RemoveProductImageBackgrounds extends Command
{
    protected $signature = 'products:remove-image-backgrounds {--dry-run : Report which images would be changed without saving anything}';

    protected $description = 'Remove plain studio backgrounds from existing product images and store them as transparent PNGs.';

    public function handle(ImageBackgroundRemover $remover): int
    {
        $disk = config('filesystems.default', 'public');
        if ($disk === 'local') {
            $disk = 'public';
        }

        $dryRun = (bool) $this->option('dry-run');
        $changed = 0;
        $skipped = 0;

        foreach (ProductImage::query()->cursor() as $image) {
            $source = $this->download($image->url());

            if ($source === null) {
                $this->warn("#{$image->id}: could not read {$image->path}");
                $skipped++;

                continue;
            }

            $cutout = $remover->process($source);
            @unlink($source);

            if ($cutout === null) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line("#{$image->id}: would remove background ({$image->path})");
                @unlink($cutout);
                $changed++;

                continue;
            }

            $filename = Str::random(40).'.png';
            $storedPath = Storage::disk($disk)->putFileAs('products', $cutout, $filename);
            @unlink($cutout);

            if (! $storedPath) {
                $this->warn("#{$image->id}: upload failed");
                $skipped++;

                continue;
            }

            $image->update([
                'path' => in_array($disk, ['cloudinary', 'r2'], true)
                    ? Storage::disk($disk)->url($storedPath)
                    : 'products/'.$filename,
            ]);
            $changed++;
        }

        $this->info(($dryRun ? 'Would change' : 'Changed')." {$changed} image(s), skipped {$skipped}.");

        return self::SUCCESS;
    }

    private function download(string $url): ?string
    {
        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'pimg');
        file_put_contents($temp, $response->body());

        return $temp;
    }
}
