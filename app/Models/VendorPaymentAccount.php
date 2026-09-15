<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPaymentAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'account_reference',
        'account_email',
        'status',
        'is_enabled',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'connected_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
