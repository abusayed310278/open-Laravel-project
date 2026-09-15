<?php

namespace App\Models;

use App\Enums\CommissionRecordStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionRecord extends Model
{
    protected $fillable = [
        'order_item_id',
        'seller_id',
        'sale_amount',
        'commission_rate',
        'commission_amount',
        'seller_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommissionRecordStatus::class,
            'sale_amount' => 'decimal:2',
            'commission_rate' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'seller_amount' => 'decimal:2',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
