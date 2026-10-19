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

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file) {
                    $uploadedImages[] = \App\Helpers\FileUploadHelper::store($file, 'banners');
                }
            }
        }

        if ($request->hasFile('image')) {
            $uploadedImages[] = \App\Helpers\FileUploadHelper::store($request->file('image'), 'banners');
        }

        $videoPath = $request->hasFile('video') ? \App\Helpers\FileUploadHelper::store($request->file('video'), 'banners') : null;

        Banner::create([
            ...$request->safe()->except(['image', 'images', 'video']),
            'image' => !empty($uploadedImages) ? $uploadedImages[0] : null,
            'images' => !empty($uploadedImages) ? array_values(array_unique($uploadedImages)) : null,
            'video' => $videoPath,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.banners.index')->with('status', 'Banner created successfully.');
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', [
            'banner' => $banner,
        ]);
    }

    public function update(StoreBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = [
            ...$request->safe()->except(['image', 'images', 'remove_image', 'remove_images', 'video', 'remove_video']),
            'is_active' => $request->boolean('is_active', false),
            'sort_order' => $request->integer('sort_order', 0),
        ];

        // Gather existing images
        $currentImages = is_array($banner->images) ? $banner->images : ($banner->image ? [$banner->image] : []);

        // Handle specific images removal
        if ($request->filled('remove_images')) {
            $removeList = (array) $request->input('remove_images');
            foreach ($removeList as $removePath) {
                \App\Support\MediaUrl::delete($removePath);
                $currentImages = array_values(array_filter($currentImages, fn ($img) => $img !== $removePath));
            }
        }

        // Handle single remove_image flag
        if ($request->boolean('remove_image')) {
            if ($banner->image) {
                \App\Support\MediaUrl::delete($banner->image);
            }
            $currentImages = array_values(array_filter($currentImages, fn ($img) => $img !== $banner->image));
        }

        // Upload new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file) {
                    $currentImages[] = \App\Helpers\FileUploadHelper::store($file, 'banners');
                }
            }
        }

        if ($request->hasFile('image')) {
            $currentImages[] = \App\Helpers\FileUploadHelper::store($request->file('image'), 'banners');
        }

        $currentImages = array_values(array_unique($currentImages));
        $data['images'] = !empty($currentImages) ? $currentImages : null;
        $data['image'] = !empty($currentImages) ? $currentImages[0] : null;

        // Handle video
        if ($request->boolean('remove_video')) {
            if ($banner->video) {
                \App\Support\MediaUrl::delete($banner->video);
            }
            $data['video'] = null;
        } elseif ($request->hasFile('video')) {
            if ($banner->video) {
                \App\Support\MediaUrl::delete($banner->video);
            }
            $data['video'] = \App\Helpers\FileUploadHelper::store($request->file('video'), 'banners');
        }

        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('status', 'Banner updated successfully.');
    }

    public function toggleActive(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        $status = $banner->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Banner {$status}.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if (!empty($banner->images) && is_array($banner->images)) {
            foreach ($banner->images as $img) {
                \App\Support\MediaUrl::delete($img);
            }
        } elseif ($banner->image) {
            \App\Support\MediaUrl::delete($banner->image);
        }

        if ($banner->video) {
            \App\Support\MediaUrl::delete($banner->video);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', 'Banner deleted.');
    }
}
