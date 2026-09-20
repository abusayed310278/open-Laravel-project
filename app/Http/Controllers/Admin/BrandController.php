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
            $data['logo'] = $this->storeUploadedFile($request->file('logo'), 'brands', 'public');
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
                Storage::disk('public')->delete($brand->logo);
            }
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $data['logo'] = $this->storeUploadedFile($request->file('logo'), 'brands', 'public');
        }

        $brand->update($data);

        $prefix = $this->getRoutePrefix($request);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', 'Brand updated.');
        }

        return redirect()->route($prefix . 'brands.index')->with('status', 'Brand updated.');
    }

    /**
     * Store an uploaded file safely, handling PHP 8.4 / Windows temp file paths.
     */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $directory = 'brands', string $disk = 'public'): string
    {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $filename = \Illuminate\Support\Str::random(40) . '.' . strtolower($extension);
        $targetPath = trim($directory, '/') . '/' . $filename;

        $sourcePath = $file->getRealPath() ?: $file->getPathname();

        if (!empty($sourcePath) && file_exists($sourcePath)) {
            $stream = @fopen($sourcePath, 'r');
            if ($stream !== false) {
                try {
                    Storage::disk($disk)->put($targetPath, $stream);
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

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        $prefix = $this->getRoutePrefix($request);

        return redirect()->route($prefix . 'brands.index')->with('status', 'Brand deleted.');
    }

    public function removeLogo(Request $request, Brand $brand): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Sellers and store owners cannot modify existing brands.');

        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
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
