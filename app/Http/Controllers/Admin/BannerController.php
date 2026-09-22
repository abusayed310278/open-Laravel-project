<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => Banner::query()->orderBy('position')->orderBy('sort_order')->simplePaginate(15),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $imagePath = $request->hasFile('image') ? $this->storeUploadedFile($request->file('image'), 'banners', 'public') : null;
        $videoPath = $request->hasFile('video') ? $this->storeUploadedFile($request->file('video'), 'banners', 'public') : null;

        Banner::create([
            ...$request->safe()->except(['image', 'video']),
            'image' => $imagePath,
            'video' => $videoPath,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return back()->with('status', 'Banner created.');
    }

    public function update(StoreBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = [
            ...$request->safe()->except(['image', 'remove_image', 'video', 'remove_video']),
            'is_active' => $request->boolean('is_active', false),
            'sort_order' => $request->integer('sort_order', 0),
        ];

        if ($request->boolean('remove_image')) {
            if ($banner->image && !str_starts_with($banner->image, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($banner->image && !str_starts_with($banner->image, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $this->storeUploadedFile($request->file('image'), 'banners', 'public');
        }

        if ($request->boolean('remove_video')) {
            if ($banner->video && !str_starts_with($banner->video, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->video);
            }
            $data['video'] = null;
        } elseif ($request->hasFile('video')) {
            if ($banner->video && !str_starts_with($banner->video, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->video);
            }
            $data['video'] = $this->storeUploadedFile($request->file('video'), 'banners', 'public');
        }

        $banner->update($data);

        return back()->with('status', 'Banner updated.');
    }

    /**
     * Store an uploaded file safely, handling PHP 8.4 / Windows temp file paths.
     */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $directory = 'banners', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = \Illuminate\Support\Str::random(40) . '.' . strtolower($extension);
        $targetPath = trim($directory, '/') . '/' . $filename;

        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if (!empty($sourcePath) && file_exists($sourcePath)) {
            $stream = @fopen($sourcePath, 'r');
            if ($stream !== false) {
                try {
                    \Illuminate\Support\Facades\Storage::disk($disk)->put($targetPath, $stream);
                    return $targetPath;
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }
        }

        return $file->store($directory, $disk);
    }

    public function toggleActive(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        $status = $banner->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Banner {$status}.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image && !str_starts_with($banner->image, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->image);
        }
        if ($banner->video && !str_starts_with($banner->video, 'http')) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($banner->video);
        }

        $banner->delete();

        return back()->with('status', 'Banner deleted.');
    }
}
