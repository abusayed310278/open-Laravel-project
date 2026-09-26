<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    protected function getRoutePrefix(Request $request): string
    {
        if ($request->routeIs('business.*')) {
            return 'business.';
        }
        if ($request->routeIs('saler.*')) {
            return 'saler.';
        }
        return 'admin.';
    }

    public function index(Request $request): View
    {
        return view('admin.brands.index', [
            'brands' => Brand::query()->orderBy('name')->simplePaginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.brands.create', ['brand' => new Brand]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);

        if ($request->hasFile('logo')) {
            $data['logo'] = \App\Helpers\FileUploadHelper::store($request->file('logo'), 'brands');
        }

        Brand::query()->create($data);

        $prefix = $this->getRoutePrefix($request);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', 'Brand created.');
        }

        return redirect()->route($prefix . 'brands.index')->with('status', 'Brand created.');
    }

    public function edit(Request $request, Brand $brand): View
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        return view('admin.brands.edit', ['brand' => $brand]);
    }

    public function update(StoreBrandRequest $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        $data = $request->safe()->except(['logo', 'remove_logo']);

        if ($request->boolean('remove_logo')) {
            if ($brand->logo) {
                \App\Support\MediaUrl::delete($brand->logo);
            }
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($brand->logo) {
                \App\Support\MediaUrl::delete($brand->logo);
            }

            $data['logo'] = \App\Helpers\FileUploadHelper::store($request->file('logo'), 'brands');
        }

        $brand->update($data);

        $prefix = $this->getRoutePrefix($request);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', 'Brand updated.');
        }

        return redirect()->route($prefix . 'brands.index')->with('status', 'Brand updated.');
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        if ($brand->logo) {
            \App\Support\MediaUrl::delete($brand->logo);
        }

        $brand->delete();

        $prefix = $this->getRoutePrefix($request);

        return redirect()->route($prefix . 'brands.index')->with('status', 'Brand deleted.');
    }

    public function removeLogo(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        if ($brand->logo) {
            \App\Support\MediaUrl::delete($brand->logo);
            $brand->update(['logo' => null]);
        }

        return back()->with('status', 'Brand logo deleted.');
    }

    public function toggleStatus(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        $newStatus = $brand->status === PublishStatus::Active
            ? PublishStatus::Inactive
            : PublishStatus::Active;

        $brand->update(['status' => $newStatus]);

        $statusLabel = $newStatus->label();

        return back()->with('status', "Brand status updated to {$statusLabel}.");
    }
}
