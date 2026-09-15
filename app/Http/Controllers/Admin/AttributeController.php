<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttributeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function index(Request $request): View
    {
        $attributes = Attribute::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('group_id'), fn ($q) => $q->where('attribute_group_id', $request->integer('group_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->string('status')->toString() === 'active') {
                    $q->where('is_active', true);
                } elseif ($request->string('status')->toString() === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->with('attributeGroup')
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $attributeGroups = AttributeGroup::ordered()->get();

        return view('admin.attributes.index', [
            'attributes'      => $attributes,
            'attributeGroups' => $attributeGroups,
            'search'          => $request->string('search')->toString(),
            'groupId'         => $request->string('group_id')->toString(),
            'type'            => $request->string('type')->toString(),
            'status'          => $request->string('status')->toString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.attributes.create', [
            'attribute'       => new Attribute(['is_active' => true, 'sort_order' => 0]),
            'attributeGroups' => AttributeGroup::active()->ordered()->get(),
        ]);
    }

    public function store(StoreAttributeRequest $request): RedirectResponse
    {
        $attribute = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $values = $request->input('values', []);
            $assignToCategoryId = $request->input('assign_to_category_id');

            unset($data['values'], $data['assign_to_category_id'], $data['redirect_to']);

            $data['is_active'] = $request->boolean('is_active', true);
            $data['is_filterable'] = $request->boolean('is_filterable', false);
            $data['is_required'] = $request->boolean('is_required', false);
            $data['is_variant'] = $request->boolean('is_variant', false);
            $data['sort_order'] = $request->integer('sort_order', 0);

            $attribute = Attribute::create($data);

            if (! empty($values) && is_array($values)) {
                foreach ($values as $index => $val) {
                    if (is_string($val) && trim($val) !== '') {
                        $attribute->values()->create([
                            'value'      => trim($val),
                            'sort_order' => $index,
                            'is_active'  => true,
                        ]);
                    }
                }
            }

            if ($assignToCategoryId) {
                $category = Category::find($assignToCategoryId);
                if ($category && ! $category->attributes()->where('attribute_id', $attribute->id)->exists()) {
                    $category->attributes()->attach($attribute->id, [
                        'is_required'   => $attribute->is_required,
                        'is_filterable' => $attribute->is_filterable,
                        'is_variant'    => $attribute->is_variant,
                        'sort_order'    => $attribute->sort_order,
                    ]);
                }
            }

            return $attribute;
        });

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            $msg = $request->filled('assign_to_category_id')
                ? "Attribute '{$attribute->name}' created and assigned to category."
                : "Attribute '{$attribute->name}' created.";

            return redirect($request->string('redirect_to'))->with('status', $msg);
        }

        return redirect()->route('admin.attributes.index')->with('status', "Attribute '{$attribute->name}' created.");
    }

    public function show(Attribute $attribute): View
    {
        return view('admin.attributes.show', [
            'attribute'       => $attribute->load(['attributeGroup', 'values']),
            'attributeGroups' => AttributeGroup::ordered()->get(),
        ]);
    }

    public function edit(Attribute $attribute): View
    {
        return $this->show($attribute);
    }

    public function update(StoreAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $data = $request->validated();
        unset($data['values'], $data['assign_to_category_id'], $data['redirect_to']);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_filterable'] = $request->boolean('is_filterable', false);
        $data['is_required'] = $request->boolean('is_required', false);
        $data['is_variant'] = $request->boolean('is_variant', false);
        $data['sort_order'] = $request->integer('sort_order', 0);

        $attribute->update($data);

        if ($request->filled('redirect_to') && Str::startsWith($request->string('redirect_to'), [url('/'), '/'])) {
            return redirect($request->string('redirect_to'))->with('status', "Attribute '{$attribute->name}' updated.");
        }

        return back()->with('status', 'Attribute updated.');
    }

    public function toggleActive(Attribute $attribute): RedirectResponse
    {
        $attribute->update(['is_active' => ! $attribute->is_active]);

        $status = $attribute->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Attribute {$status}.");
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $attribute->values()->delete();
        $attribute->categories()->detach();
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('status', 'Attribute deleted.');
    }
}
