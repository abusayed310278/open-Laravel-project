<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkCategoryAttributeRequest;
use App\Http\Requests\Admin\CategoryAttributeRequest;
use App\Http\Requests\Admin\SyncCategoryAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryAttributeController extends Controller
{
    public function index(Category $category): View
    {
        $assignedAttributes = $category->attributes()
            ->with(['attributeGroup', 'values' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        $groupedAttributes = $assignedAttributes
            ->groupBy(fn ($attr) => $attr->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;
                $sortedItems = $items->sortBy([
                    ['pivot.sort_order', 'asc'],
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])->values();

                return [
                    'group'      => $group,
                    'group_id'   => (int) $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $sortedItems,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        $assignedIds = $assignedAttributes->pluck('id')->toArray();

        $availableAttributes = Attribute::query()
            ->active()
            ->with(['attributeGroup', 'values'])
            ->whereNotIn('id', $assignedIds)
            ->orderBy('name')
            ->get();

        $attributeGroups = AttributeGroup::query()
            ->active()
            ->ordered()
            ->with(['attributes' => fn ($q) => $q->active()->ordered()])
            ->get();

        return view('admin.categories.attributes', [
            'category'            => $category,
            'assignedAttributes'  => $assignedAttributes,
            'assignedIds'         => $assignedIds,
            'groupedAttributes'   => $groupedAttributes,
            'totalAssignedCount'  => $assignedAttributes->count(),
            'attributeGroups'     => $attributeGroups,
            'availableAttributes' => $availableAttributes,
        ]);
    }

    public function store(CategoryAttributeRequest $request, Category $category): RedirectResponse
    {
        $attributeId = $request->integer('attribute_id');

        if ($category->attributes()->where('attributes.id', $attributeId)->exists()) {
            return back()->with('error', 'This attribute is already assigned to this category.');
        }

        $attribute = Attribute::findOrFail($attributeId);

        $category->attributes()->attach($attributeId, [
            'is_required'   => $request->has('is_required') ? $request->boolean('is_required') : $attribute->is_required,
            'is_filterable' => $request->has('is_filterable') ? $request->boolean('is_filterable') : $attribute->is_filterable,
            'is_variant'    => $request->has('is_variant') ? $request->boolean('is_variant') : $attribute->is_variant,
            'sort_order'    => $request->filled('sort_order') ? $request->integer('sort_order') : $attribute->sort_order,
        ]);

        return back()->with('status', "'{$attribute->name}' assigned to {$category->name}.");
    }

    public function bulkStore(BulkCategoryAttributeRequest $request, Category $category): RedirectResponse
    {
        $attributeIds = $request->input('attribute_ids', []);
        $existingIds = $category->attributes()->pluck('attributes.id')->toArray();
        $newIds = array_diff($attributeIds, $existingIds);

        if (empty($newIds)) {
            return back()->with('info', 'All selected attributes are already assigned to this category.');
        }

        $attributes = Attribute::whereIn('id', $newIds)->get();
        $attachData = [];

        foreach ($attributes as $attr) {
            $attachData[$attr->id] = [
                'is_required'   => $attr->is_required,
                'is_filterable' => $attr->is_filterable,
                'is_variant'    => $attr->is_variant,
                'sort_order'    => $attr->sort_order,
            ];
        }

        $category->attributes()->attach($attachData);

        $count = count($attachData);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', "{$count} attributes assigned to {$category->name}.");
        }

        return back()->with('status', "{$count} attributes assigned to {$category->name}.");
    }

    public function sync(SyncCategoryAttributeRequest $request, Category $category): RedirectResponse
    {
        $requestedIds = collect($request->input('attribute_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $existingIds = $category->attributes()->pluck('attributes.id')->values();

        $newIds = $requestedIds->diff($existingIds);
        $staying = $requestedIds->intersect($existingIds);
        $removedCount = $existingIds->diff($requestedIds)->count();

        $syncPayload = [];

        if ($newIds->isNotEmpty()) {
            Attribute::whereIn('id', $newIds)->get()->each(function (Attribute $attr) use (&$syncPayload) {
                $syncPayload[$attr->id] = [
                    'is_required'   => $attr->is_required,
                    'is_filterable' => $attr->is_filterable,
                    'is_variant'    => $attr->is_variant,
                    'sort_order'    => $attr->sort_order,
                ];
            });
        }

        foreach ($staying as $id) {
            $syncPayload[] = $id;
        }

        $category->attributes()->sync($syncPayload);

        $parts = array_filter([
            $newIds->count() > 0 ? "{$newIds->count()} added" : null,
            $removedCount > 0 ? "{$removedCount} removed" : null,
        ]);

        $message = $parts !== []
            ? 'Attributes updated for ' . $category->name . ': ' . implode(', ', $parts) . '.'
            : "No changes made to {$category->name}.";

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', $message);
        }

        return back()->with('status', $message);
    }

    public function update(CategoryAttributeRequest $request, Category $category, Attribute $attribute): RedirectResponse
    {
        $category->attributes()->updateExistingPivot($attribute->id, [
            'is_required'   => $request->boolean('is_required'),
            'is_filterable' => $request->boolean('is_filterable'),
            'is_variant'    => $request->boolean('is_variant'),
            'sort_order'    => $request->integer('sort_order', 0),
        ]);

        return back()->with('status', "Settings for '{$attribute->name}' updated.");
    }

    public function destroy(Category $category, Attribute $attribute): RedirectResponse
    {
        $category->attributes()->detach($attribute->id);

        return back()->with('status', "Removed '{$attribute->name}' from {$category->name}.");
    }
}
