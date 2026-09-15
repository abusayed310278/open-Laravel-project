<?php

namespace App\Models;

use App\Enums\StorageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WarehouseProduct extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'warehouse_location_id',
        'seller_id',
        'received_at',
        'condition_at_receipt',
        'quantity',
        'storage_status',
        'released_at',
        'release_reason',
    ];

    protected function casts(): array
    {
        return [
            'storage_status' => StorageStatus::class,
            'received_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(WarehouseReceipt::class);
    }
}
