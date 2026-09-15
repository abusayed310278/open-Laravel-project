<?php

namespace App\Models;

use App\Enums\AttributeType;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'attribute_group_id',
        'name',
        'slug',
        'type',
        'unit',
        'placeholder',
        'is_filterable',
        'is_variant',
        'is_required',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'attribute_group_id' => 'integer',
            'type'               => AttributeType::class,
            'is_filterable'      => 'boolean',
            'is_variant'         => 'boolean',
            'is_required'        => 'boolean',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer',
        ];
    }

    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function group(): BelongsTo
    {
        return $this->attributeGroup();
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_attributes')
            ->withPivot(['is_required', 'is_filterable', 'is_variant', 'sort_order'])
            ->withTimestamps();
    }

    public function categoryAttributes(): HasMany
    {
        return $this->hasMany(CategoryAttribute::class, 'attribute_id');
    }

    public function hasOptions(): bool
    {
        return $this->type->usesValueList();
    }

    public function isMultiSelect(): bool
    {
        return $this->type === AttributeType::MultiSelect;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
