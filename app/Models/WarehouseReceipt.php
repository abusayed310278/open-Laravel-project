<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseReceipt extends Model
{
    protected $fillable = [
        'warehouse_product_id',
        'receipt_number',
        'received_by',
        'condition_notes',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
        ];
    }

    public function warehouseProduct(): BelongsTo
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
