<?php

namespace App\Models;

use App\Enums\ReviewableType;
use App\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'reviewer_id',
        'reviewable_type',
        'reviewable_id',
        'order_id',
        'rating',
        'title',
        'body',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reviewable_type' => ReviewableType::class,
            'status' => ReviewStatus::class,
            'rating' => 'integer',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ReviewReply::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class);
    }

    /**
     * The reviewed Product or seller User — reviewable_type is a small
     * fixed enum rather than an Eloquent morph map, so this resolves it
     * manually instead of via morphTo().
     */
    public function reviewable(): Product|User|null
    {
        return match ($this->reviewable_type) {
            ReviewableType::Product => Product::find($this->reviewable_id),
            ReviewableType::Seller => User::find($this->reviewable_id),
        };
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ReviewStatus::Approved);
    }

    public function scopeFor(Builder $query, ReviewableType $type, int $reviewableId): Builder
    {
        return $query->where('reviewable_type', $type)->where('reviewable_id', $reviewableId);
    }
}
