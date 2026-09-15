<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSlug;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContentStatus::class,
        ];
    }

    public function slugSourceColumn(): string
    {
        return 'title';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published);
    }
}
