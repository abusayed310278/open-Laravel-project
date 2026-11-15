<?php

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class UserVerification extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('admin_dashboard_metrics'));
        static::deleted(fn () => Cache::forget('admin_dashboard_metrics'));
    }

    protected $fillable = [
        'user_id',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => KycStatus::class,
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VerificationDocument::class, 'verification_id');
    }
}
