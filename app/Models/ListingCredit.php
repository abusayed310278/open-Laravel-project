<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCredit extends Model
{
    protected $fillable = [
        'subscription_id',
        'user_id',
        'total_credits',
        'used_credits',
        'remaining_credits',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCredits(): bool
    {
        return $this->remaining_credits > 0 && (! $this->expires_at || $this->expires_at->isFuture());
    }

    public function consume(): void
    {
        $this->increment('used_credits');
        $this->decrement('remaining_credits');
    }
}
