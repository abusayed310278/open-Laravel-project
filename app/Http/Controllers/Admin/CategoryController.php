<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Attribute;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function toggleStatus(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $newStatus = $category->status === PublishStatus::Active
            ? PublishStatus::Inactive
            : PublishStatus::Active;

        $category->update(['status' => $newStatus]);

        return back()->with('status', "Category \"{$category->name}\" status changed to " . strtolower($newStatus->label()) . '.');
    }
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->with('parent')->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category,
            'parents' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUploadedFile($request->file('image'), 'categories', 'public');
        }

        Category::query()->create($data);

        if ($request->filled('redirect_to') && \Illuminate\Support\Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', 'Category created.');
        }

        return redirect()->route($this->getRoutePrefix() . 'categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => Category::query()->where('id', '!=', $category->id)->orderBy('name')->get(),
            'attributes' => Attribute::query()->orderBy('name')->get(),
            'assignedAttributeIds' => $category->attributes()->pluck('attributes.id')->all(),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($request->filled('parent_id')) {
            $parent = Category::query()->findOrFail($request->integer('parent_id'));

            if ($parent->id === $category->id || $parent->isDescendantOf($category)) {
                throw ValidationException::withMessages(['parent_id' => 'A category cannot be nested under itself or its own subcategory.']);
            }
        }

        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->boolean('remove_image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $this->storeUploadedFile($request->file('image'), 'categories', 'public');
        }

        $category->update($data);

        if ($request->filled('redirect_to') && \Illuminate\Support\Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', 'Category updated.');
        }

        return redirect()->route($this->getRoutePrefix() . 'categories.index')->with('status', 'Category updated.');
    }

    /**
     * Store an uploaded file safely, handling PHP 8.4 / Windows temp file paths.
     */
    private function storeUploadedFile(\Illuminate\Http\UploadedFile $file, string $directory = 'categories', string $disk = 'public'): string
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

    public function updateAttributes(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $attributes = collect(request()->input('attributes', []))
            ->mapWithKeys(fn (string $id, int $index) => [
                (int) $id => [
                    'is_required' => request()->boolean("required.{$id}"),
                    'sort_order' => $index,
                ],
            ]);

        $category->attributes()->sync($attributes);

        return back()->with('status', 'Category attributes updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($category->children()->exists()) {
            return back()->withErrors(['category' => 'Remove or reassign its subcategories first.']);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route($this->getRoutePrefix() . 'categories.index')->with('status', 'Category deleted.');
    }

    protected function getRoutePrefix(): string
    {
        if (request()->routeIs('business.*')) {
            return 'business.';
        }
        if (request()->routeIs('saler.*')) {
            return 'saler.';
        }

        return 'admin.';
    }
}
