<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{
    public static function store(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $realPath = $file->getRealPath();
        $sourcePath = ($realPath && file_exists($realPath)) ? $realPath : $file->getPathname();

        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'png');
        $filename = Str::random(40) . '.' . $extension;
        $targetPath = rtrim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($targetPath, file_get_contents($sourcePath));

        return $targetPath;
    }
}
