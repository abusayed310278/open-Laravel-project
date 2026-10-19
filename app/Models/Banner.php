<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'image',
        'images',
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
            'images' => 'array',
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

    /**
     * @return array<string>
     */
    public function getImagesUrlsAttribute(): array
    {
        $urls = [];
        if (!empty($this->images) && is_array($this->images)) {
            foreach ($this->images as $img) {
                if (filled($img)) {
                    $resolved = \App\Support\MediaUrl::resolve($img);
                    if ($resolved) {
                        $urls[] = $resolved;
                    }
                }
            }
        } elseif (filled($this->image)) {
            $resolved = $this->imageUrl();
            if ($resolved) {
                $urls[] = $resolved;
            }
        }

        return $urls;
    }

    /**
     * @return array<array{type: string, url: string}>
     */
    public function getMediaItemsAttribute(): array
    {
        $items = [];
        foreach ($this->images_urls as $imgUrl) {
            $items[] = [
                'type' => 'image',
                'url' => $imgUrl,
            ];
        }

        if ($this->video_url) {
            $items[] = [
                'type' => 'video',
                'url' => $this->video_url,
            ];
        }

        return $items;
    }
}
