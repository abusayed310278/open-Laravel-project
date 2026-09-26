<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'video',
        'link',
        'position',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order');
    }

    public function scopePosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    public function imageUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->image);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->imageUrl();
    }

    public function videoUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->video);
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->videoUrl();
    }
}
