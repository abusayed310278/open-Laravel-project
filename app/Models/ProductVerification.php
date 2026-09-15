<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVerification extends Model
{
    protected $fillable = [
        'product_id',
        'seller_id',
        'verifier_id',
        'location_id',
        'status',
        'requested_at',
        'scheduled_at',
        'inspected_at',
        'verified_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'requested_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'inspected_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verifier_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(VerificationLocation::class, 'location_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(VerificationAppointment::class);
    }

    public function latestAppointment(): HasOne
    {
        return $this->hasOne(VerificationAppointment::class)->latestOfMany();
    }

    public function results(): HasMany
    {
        return $this->hasMany(VerificationResult::class);
    }

    public function gradeAssignment(): HasOne
    {
        return $this->hasOne(ProductGradeAssignment::class);
    }
}
