<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{
    public static function store(UploadedFile $file, string $directory, ?string $disk = null): string
    {
        $activeDisk = config('filesystems.default', 'public');
        if ($activeDisk === 'local') {
            $activeDisk = 'public';
        }

        $targetDisk = (! empty($disk) && $disk !== 'local') ? $disk : $activeDisk;

        $realPath = $file->getRealPath();
        $sourcePath = ($realPath && file_exists($realPath)) ? $realPath : $file->getPathname();

        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'png');
        $filename = Str::random(40) . '.' . strtolower($extension);
        $targetPath = rtrim($directory, '/') . '/' . $filename;

        Storage::disk($targetDisk)->put($targetPath, file_get_contents($sourcePath));

        if (in_array($targetDisk, ['cloudinary', 'r2'], true)) {
            $url = Storage::disk($targetDisk)->url($targetPath);
            if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
                return $url;
            }
        }

        return $targetPath;
    }
}
