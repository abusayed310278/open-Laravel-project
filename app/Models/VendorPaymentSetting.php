<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPaymentSetting extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_enabled',
        'paypal_enabled',
        'cod_enabled',
        'manual_bank_enabled',
        'bank_details',
    ];

    protected function casts(): array
    {
        return [
            'stripe_enabled' => 'boolean',
            'paypal_enabled' => 'boolean',
            'cod_enabled' => 'boolean',
            'manual_bank_enabled' => 'boolean',
            'bank_details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
