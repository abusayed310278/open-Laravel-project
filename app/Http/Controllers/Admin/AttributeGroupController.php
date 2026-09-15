<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeGroupRequest;
use App\Models\AttributeGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributeGroupController extends Controller
{
    public function index(Request $request): View
    {
        $groups = AttributeGroup::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->string('status')->toString() === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attribute-groups.index', [
            'groups' => $groups,
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.attribute-groups.create', [
            'group' => new AttributeGroup(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(AttributeGroupRequest $request): RedirectResponse
    {
        $name = $request->string('name')->toString();
        $slug = $request->filled('slug')
            ? Str::slug($request->string('slug'))
            : $this->uniqueSlug($name);

        $group = AttributeGroup::create([
            'name'               => $name,
            'slug'               => $slug,
            'description'        => $request->input('description'),
            'sort_order'         => $request->integer('sort_order', 0),
            'is_active'          => $request->boolean('is_active', true),
            'created_by_user_id' => $request->user()?->id,
        ]);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', "Attribute group '{$group->name}' created.");
        }

        return redirect()->route('admin.attribute-groups.index')->with('status', 'Attribute group created.');
    }

    public function edit(AttributeGroup $attributeGroup): View
    {
        return view('admin.attribute-groups.edit', [
            'group' => $attributeGroup->loadCount('attributes'),
        ]);
    }

    public function update(AttributeGroupRequest $request, AttributeGroup $attributeGroup): RedirectResponse
    {
        $name = $request->string('name')->toString();
        $slug = $request->filled('slug')
            ? Str::slug($request->string('slug'))
            : $attributeGroup->slug;

        $attributeGroup->update([
            'name'        => $name,
            'slug'        => $slug,
            'description' => $request->input('description'),
            'sort_order'  => $request->integer('sort_order', 0),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', "Attribute group '{$attributeGroup->name}' updated.");
        }

        return redirect()->route('admin.attribute-groups.index')->with('status', 'Attribute group updated.');
    }

    public function toggleActive(AttributeGroup $attributeGroup): RedirectResponse
    {
        $attributeGroup->update(['is_active' => ! $attributeGroup->is_active]);

        $status = $attributeGroup->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Attribute group {$status}.");
    }

    public function destroy(AttributeGroup $attributeGroup): RedirectResponse
    {
        if ($attributeGroup->attributes()->exists()) {
            return back()->with('error', 'Cannot delete group with assigned attributes. Please reassign or remove them first.');
        }

        $attributeGroup->delete();

        return back()->with('status', 'Attribute group deleted.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (AttributeGroup::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
