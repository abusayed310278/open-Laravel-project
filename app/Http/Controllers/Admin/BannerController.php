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
        $imagePath = $request->hasFile('image') ? \App\Helpers\FileUploadHelper::store($request->file('image'), 'banners') : null;
        $videoPath = $request->hasFile('video') ? \App\Helpers\FileUploadHelper::store($request->file('video'), 'banners') : null;

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
            if ($banner->image) {
                \App\Support\MediaUrl::delete($banner->image);
            }
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($banner->image) {
                \App\Support\MediaUrl::delete($banner->image);
            }
            $data['image'] = \App\Helpers\FileUploadHelper::store($request->file('image'), 'banners');
        }

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

        return back()->with('status', 'Banner updated.');
    }

    public function toggleActive(Banner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        $status = $banner->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Banner {$status}.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image) {
            \App\Support\MediaUrl::delete($banner->image);
        }
        if ($banner->video) {
            \App\Support\MediaUrl::delete($banner->video);
        }

        $banner->delete();

        return back()->with('status', 'Banner deleted.');
    }
}
