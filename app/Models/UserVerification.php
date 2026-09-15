<?php

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserVerification extends Model
{
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
