<?php

namespace App\Support\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generates a unique `slug` from the model's name column (or
 * `slugSourceColumn()` override) whenever it's created without one
 * explicitly set. Used by Category, Brand, Attribute, AttributeValue.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    public function slugSourceColumn(): string
    {
        return 'name';
    }

    public function generateUniqueSlug(): string
    {
        $base = Str::slug($this->{$this->slugSourceColumn()});
        $slug = $base;
        $suffix = 1;

        $query = static::query();

        if ($this->slugSourceColumn() === 'value' && $this->attribute_id) {
            $query->where('attribute_id', $this->attribute_id);
        }

        while ((clone $query)->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
