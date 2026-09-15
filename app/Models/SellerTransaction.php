<?php

namespace App\Models;

use App\Enums\SellerTransactionSource;
use App\Enums\SellerTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'source',
        'amount',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => SellerTransactionType::class,
            'source' => SellerTransactionSource::class,
            'amount' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(SellerWallet::class, 'wallet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
