<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory, HasSlug;

    private const TREE_CACHE_KEY = 'categories.active_root_tree';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'status'     => PublishStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->active()->orderBy('sort_order');
    }

    public function allChildren(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'category_attributes')
            ->withPivot(['is_required', 'is_filterable', 'is_variant', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class, 'category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Active);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Grouped, form-ready attribute definitions for this category
     * (falling back up the parent chain when this category has none directly assigned).
     */
    public function attributesGroupedForForm(array $keepInactiveAttributeIds = []): array
    {
        $applyActiveFilter = function ($query) use ($keepInactiveAttributeIds) {
            return $query->where(function ($q) use ($keepInactiveAttributeIds) {
                $q->where('attributes.is_active', true);
                if (! empty($keepInactiveAttributeIds)) {
                    $q->orWhereIn('attributes.id', $keepInactiveAttributeIds);
                }
            });
        };

        $assignedAttributes = $applyActiveFilter($this->attributes())
            ->with([
                'attributeGroup',
                'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
            ])
            ->get();

        if ($assignedAttributes->isEmpty() && $this->parent_id) {
            $curr = $this;
            while ($assignedAttributes->isEmpty() && $curr->parent_id) {
                $curr = $curr->parent;
                if ($curr) {
                    $assignedAttributes = $applyActiveFilter($curr->attributes())
                        ->with([
                            'attributeGroup',
                            'values' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('value'),
                        ])
                        ->get();
                }
            }
        }

        $groupedAttributes = $assignedAttributes
            ->groupBy(fn ($attr) => $attr->attribute_group_id ?? 0)
            ->map(function ($items, $groupId) {
                $group = $groupId > 0 ? $items->first()->attributeGroup : null;
                $sortedItems = $items->sortBy([
                    ['pivot.sort_order', 'asc'],
                    ['sort_order', 'asc'],
                    ['name', 'asc'],
                ])->values()->map(function ($attr) {
                    return [
                        'id'            => $attr->id,
                        'name'          => $attr->name,
                        'slug'          => $attr->slug,
                        'type'          => $attr->type->value ?? (string) $attr->type,
                        'unit'          => $attr->unit,
                        'placeholder'   => $attr->placeholder,
                        'is_required'   => (bool) ($attr->is_required || !empty($attr->pivot?->is_required)),
                        'is_filterable' => (bool) ($attr->is_filterable || !empty($attr->pivot?->is_filterable)),
                        'is_variant'    => (bool) ($attr->is_variant || !empty($attr->pivot?->is_variant)),
                        'sort_order'    => (int) ($attr->pivot->sort_order ?? $attr->sort_order),
                        'values'        => $attr->values->map(fn ($v) => [
                            'id'        => $v->id,
                            'value'     => $v->value,
                            'slug'      => $v->slug,
                            'color_hex' => $v->color_hex,
                        ])->values()->toArray(),
                    ];
                });

                return [
                    'group_id'   => (int) $groupId,
                    'group_name' => $group?->name ?? 'General / Other Specifications',
                    'sort_order' => $group?->sort_order ?? 9999,
                    'attributes' => $sortedItems,
                ];
            })
            ->sortBy('sort_order')
            ->values();

        return [
            'category_id'   => $this->id,
            'category_name' => $this->name,
            'groups'        => $groupedAttributes,
            'total_count'   => $assignedAttributes->count(),
        ];
    }

    /**
     * Get full breadcrumb path string e.g. "Electronics › Audio › Portable Speakers".
     */
    public function getBreadcrumbPath(): string
    {
        $parts = [$this->name];
        $curr = $this;

        while ($curr->parent_id && $curr->parent) {
            $curr = $curr->parent;
            array_unshift($parts, $curr->name);
        }

        return implode(' › ', $parts);
    }

    /**
     * All descendant category IDs.
     */
    public function descendantIds(): array
    {
        $ids = [];
        $frontier = [$this->id];

        while (! empty($frontier)) {
            $children = static::whereIn('parent_id', $frontier)->pluck('id')->all();
            if (empty($children)) {
                break;
            }
            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }

    /**
     * Flat, indented tree for category selection dropdowns.
     */
    public static function getTreeSelectOptions(): array
    {
        $all = static::withCount('attributes')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $grouped = $all->groupBy('parent_id');
        $options = [];

        $buildTree = function ($parentId, $depth = 0, $path = '') use (&$buildTree, $grouped, &$options) {
            if (! isset($grouped[$parentId])) {
                return;
            }

            foreach ($grouped[$parentId] as $cat) {
                $currentPath = $path ? "{$path} › {$cat->name}" : $cat->name;
                $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';

                $options[] = [
                    'id'               => $cat->id,
                    'name'             => $cat->name,
                    'path'             => $currentPath,
                    'depth'            => $depth,
                    'indent_name'      => $prefix . $cat->name,
                    'attributes_count' => (int) ($cat->attributes_count ?? 0),
                    'has_children'     => isset($grouped[$cat->id]) && $grouped[$cat->id]->isNotEmpty(),
                ];

                $buildTree($cat->id, $depth + 1, $currentPath);
            }
        };

        $buildTree(null, 0, '');

        return $options;
    }

    /**
     * Active root categories with their active children eager-loaded.
     *
     * @return Collection<int, self>
     */
    public static function cachedTree(): Collection
    {
        $rows = Cache::rememberForever(self::TREE_CACHE_KEY, fn () => self::query()
            ->active()
            ->roots()
            ->with('children')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (self $category) => [
                'attributes' => $category->getAttributes(),
                'children' => $category->children->map(fn (self $child) => $child->getAttributes())->all(),
            ])
            ->all());

        return collect($rows)->map(function (array $row) {
            $category = (new self)->newFromBuilder($row['attributes']);
            $category->setRelation('children', collect($row['children'])->map(fn (array $attrs) => (new self)->newFromBuilder($attrs)));

            return $category;
        });
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::TREE_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::TREE_CACHE_KEY));
    }

    /**
     * Walk up the parent chain to prevent a category being re-parented
     * under one of its own descendants.
     */
    public function isDescendantOf(self $possibleAncestor): bool
    {
        $parent = $this->parent;

        while ($parent) {
            if ($parent->id === $possibleAncestor->id) {
                return true;
            }

            $parent = $parent->parent;
        }

        return false;
    }
}
