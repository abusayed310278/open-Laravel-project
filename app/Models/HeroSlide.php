<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'enable_overlay',
        'badge_text',
        'badge_bg_color',
        'badge_text_color',
        'title_line1',
        'title_line1_color',
        'title_line2',
        'title_line2_color',
        'subtitle',
        'subtitle_color',
        'callout_text',
        'callout_color',
        'callout_accent_color',
        'features',
        'product_id',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enable_overlay' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return MediaUrl::resolve($this->image);
    }
}
