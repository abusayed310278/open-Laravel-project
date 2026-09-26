<?php

namespace App\Http\Controllers;

use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditorMediaController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,avif', 'max:10240'],
        ]);

        $file = $request->file('image');
        $extension = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::random(40) . '.' . $extension;

        $disk = config('filesystems.default', 'public');
        if ($disk === 'local') {
            $disk = 'public';
        }

        $storedPath = Storage::disk($disk)->putFileAs('editor', $file, $filename);

        if (in_array($disk, ['cloudinary', 'r2'], true)) {
            $url = Storage::disk($disk)->url($storedPath);
        } else {
            $url = MediaUrl::resolve('editor/' . $filename);
        }

        return response()->json([
            'url' => $url,
            'success' => true,
        ]);
    }
}
