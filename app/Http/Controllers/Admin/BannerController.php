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
            'banners' => Banner::query()->orderBy('position')->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        Banner::create([
            ...$request->safe()->except('image'),
            'image' => $request->file('image')->store('banners', 'public'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return back()->with('status', 'Banner created.');
    }

    public function update(StoreBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = [
            ...$request->safe()->except('image'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);

        return back()->with('status', 'Banner updated.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return back()->with('status', 'Banner deleted.');
    }
}
