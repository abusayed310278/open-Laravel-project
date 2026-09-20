<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'title',
        'group',
        'description',
        'is_enabled',
        'sort_order',
        'meta_data',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
    ];

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
