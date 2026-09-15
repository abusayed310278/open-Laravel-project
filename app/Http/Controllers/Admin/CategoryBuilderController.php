<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttributeType;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Category Builder — a guided workspace uniting Category, Attribute Group,
 * Attribute, and Category-Attribute assignment screens into one cohesive flow.
 */
class CategoryBuilderController extends Controller
{
    public function categories(Request $request): View
    {
        $categories = Category::with('parent')
            ->withCount(['children', 'products', 'attributes'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.builder.categories', [
            'tree'           => $this->buildCategoryTree($categories),
            'categoryModels' => $categories->keyBy('id'),
            'parents'        => $categories->sortBy('name')->values(),
        ]);
    }

    public function attributeGroups(Request $request): View
    {
        $groups = AttributeGroup::withCount('attributes')
            ->with(['attributes' => fn ($q) => $q->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.builder.attribute-groups', [
            'groups' => $groups,
        ]);
    }

    public function attributes(Request $request): View
    {
        $attributes = Attribute::with(['attributeGroup', 'values' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount('values')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.builder.attributes', [
            'attributes'      => $attributes,
            'attributeGroups' => AttributeGroup::active()->ordered()->get(),
            'types'           => AttributeType::cases(),
        ]);
    }

    public function assign(Request $request): View
    {
        $categories = Category::withCount('attributes')->orderBy('sort_order')->orderBy('name')->get();

        $attributes = Attribute::query()
            ->active()
            ->with(['attributeGroup', 'values' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $groupedAttributes = $attributes
            ->groupBy(fn ($a) => $a->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;

                return [
                    'group_id'   => (int) $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $items->map(fn ($a) => [
                        'id'         => $a->id,
                        'name'       => $a->name,
                        'type'       => $a->type->value ?? (string) $a->type,
                        'unit'       => $a->unit,
                        'values'     => $a->hasOptions() ? $a->values->pluck('value')->values()->all() : [],
                    ])->values()->all(),
                ];
            })
            ->sortBy('sort_order')
            ->values();

        $assignments = DB::table('category_attributes')
            ->select('category_id', 'attribute_id')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => $rows->pluck('attribute_id')->values()->all())
            ->all();

        return view('admin.categories.builder.assign', [
            'tree'               => $this->buildCategoryTree($categories, includeAttributeCount: true),
            'groupedAttributes'  => $groupedAttributes,
            'assignments'        => $assignments,
            'selectedCategoryId' => $request->integer('category') ?: null,
        ]);
    }

    /**
     * Flat, depth-indented category tree list.
     */
    private function buildCategoryTree($categories, bool $includeAttributeCount = false): array
    {
        $grouped = $categories->groupBy('parent_id');
        $options = [];

        $walk = function ($parentId, $depth = 0) use (&$walk, $grouped, &$options, $includeAttributeCount) {
            if (! isset($grouped[$parentId])) {
                return;
            }

            foreach ($grouped[$parentId] as $cat) {
                $childrenCount = $cat->children_count ?? 0;
                $productsCount = $cat->products_count ?? 0;

                $options[] = [
                    'id'               => $cat->id,
                    'name'             => $cat->name,
                    'parent_id'        => $cat->parent_id,
                    'parent_name'      => $cat->parent?->name,
                    'depth'            => $depth,
                    'status'           => $cat->status->value ?? (string) $cat->status,
                    'is_active'        => ($cat->status->value ?? (string) $cat->status) === 'active',
                    'attributes_count' => $includeAttributeCount ? (int) ($cat->attributes_count ?? 0) : null,
                    'children_count'   => $childrenCount,
                    'products_count'   => $productsCount,
                    'can_delete'       => $childrenCount === 0 && $productsCount === 0,
                ];

                $walk($cat->id, $depth + 1);
            }
        };

        $walk(null);

        return $options;
    }
}
