<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPaymentSetting extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_enabled',
        'stripe_publishable_key',
        'stripe_secret_key',
        'paypal_enabled',
        'paypal_client_id',
        'paypal_client_secret',
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

    public function hasStripeConnected(): bool
    {
        return filled($this->stripe_publishable_key) && filled($this->stripe_secret_key);
    }

    public function hasPaypalConnected(): bool
    {
        return filled($this->paypal_client_id) && filled($this->paypal_client_secret);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
