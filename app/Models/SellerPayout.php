<?php

namespace App\Models;

use App\Enums\SellerPayoutMethod;
use App\Enums\SellerPayoutStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayout extends Model
{
    protected $fillable = [
        'payout_number',
        'user_id',
        'amount',
        'method',
        'status',
        'approved_by',
        'processed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'method' => SellerPayoutMethod::class,
            'status' => SellerPayoutStatus::class,
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
