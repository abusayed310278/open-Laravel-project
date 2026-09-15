<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationLocation extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'working_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'working_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function verifiers(): HasMany
    {
        return $this->hasMany(VerifierProfile::class, 'assigned_location_id');
    }

    public function productVerifications(): HasMany
    {
        return $this->hasMany(ProductVerification::class, 'location_id');
    }
}
