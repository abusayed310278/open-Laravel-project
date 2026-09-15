<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifierProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'assigned_location_id',
        'specializations',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(VerificationLocation::class, 'assigned_location_id');
    }
}
