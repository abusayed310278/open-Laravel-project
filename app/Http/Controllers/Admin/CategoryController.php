<?php

namespace App\Http\Controllers\Admin;

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
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::query()->create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => Category::query()->where('id', '!=', $category->id)->orderBy('name')->get(),
            'attributes' => Attribute::query()->orderBy('name')->get(),
            'assignedAttributeIds' => $category->attributes()->pluck('attributes.id')->all(),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        if ($request->filled('parent_id')) {
            $parent = Category::query()->findOrFail($request->integer('parent_id'));

            if ($parent->id === $category->id || $parent->isDescendantOf($category)) {
                throw ValidationException::withMessages(['parent_id' => 'A category cannot be nested under itself or its own subcategory.']);
            }
        }

        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function updateAttributes(Category $category): RedirectResponse
    {
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
        if ($category->children()->exists()) {
            return back()->withErrors(['category' => 'Remove or reassign its subcategories first.']);
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }
}
