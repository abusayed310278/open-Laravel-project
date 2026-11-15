<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Brand extends Model
{
    use HasFactory, HasSlug;

    public const CACHE_KEY = 'active_brands_list';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return Collection<int, Brand>
     */
    public static function cachedActive(): Collection
    {
        $rows = Cache::remember(self::CACHE_KEY, 3600, fn () => self::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (self $brand) => $brand->getAttributes())
            ->all()
        );

        return (new self)->newCollection(
            collect($rows)->map(fn (array $attrs) => (new self)->newFromBuilder($attrs))->all()
        );
    }

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Active);
    }

    public function logoUrl(): ?string
    {
        return \App\Support\MediaUrl::resolve($this->logo);
    }
}
