<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class HeroSlide extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'home_active_hero_slides';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    protected $fillable = [
        'image',
        'enable_overlay',
        'badge_text',
        'badge_bg_color',
        'badge_text_color',
        'badge_font_size',
        'title_line1',
        'title_line1_color',
        'title_line1_font_size',
        'title_line2',
        'title_line2_color',
        'title_line2_font_size',
        'subtitle',
        'subtitle_color',
        'subtitle_font_size',
        'callout_text',
        'callout_color',
        'callout_accent_color',
        'callout_font_size',
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
            'badge_font_size' => 'integer',
            'title_line1_font_size' => 'integer',
            'title_line2_font_size' => 'integer',
            'subtitle_font_size' => 'integer',
            'callout_font_size' => 'integer',
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
