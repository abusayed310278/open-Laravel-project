<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseLocation extends Model
{
    protected $fillable = [
        'warehouse_id',
        'zone',
        'row',
        'shelf',
        'slot',
        'is_occupied',
    ];

    protected function casts(): array
    {
        return [
            'is_occupied' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function label(): string
    {
        return "{$this->zone}-{$this->row}-{$this->shelf}-{$this->slot}";
    }
}
