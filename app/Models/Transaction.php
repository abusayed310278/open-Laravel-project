<?php

namespace App\Models;

use App\Enums\PaymentRoute;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'order_id',
        'vendor_order_id',
        'user_id',
        'type',
        'payment_route',
        'provider',
        'provider_transaction_id',
        'amount',
        'currency',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'payment_route' => PaymentRoute::class,
            'status' => TransactionStatus::class,
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
