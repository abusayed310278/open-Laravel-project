<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table: category_attributes — which attributes apply to a category, and how.
 */
class CategoryAttribute extends Model
{
    use HasFactory;

    protected $table = 'category_attributes';

    protected $fillable = [
        'category_id',
        'attribute_id',
        'is_required',
        'is_filterable',
        'is_variant',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required'   => 'boolean',
            'is_filterable' => 'boolean',
            'is_variant'    => 'boolean',
            'sort_order'    => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'attribute_id');
    }
}
