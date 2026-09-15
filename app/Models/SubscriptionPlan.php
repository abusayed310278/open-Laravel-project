<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionType;
use App\Support\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'billing_cycle',
        'price',
        'listing_credits',
        'duration_days',
        'max_products',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => SubscriptionType::class,
            'billing_cycle' => BillingCycle::class,
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForType(Builder $query, SubscriptionType $type): Builder
    {
        return $query->where('type', $type);
    }
}
