<?php

namespace App\Models;

use App\Enums\WarehouseMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseMovement extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'from_location',
        'to_location',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'type' => WarehouseMovementType::class,
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function createFor(
        Product $product,
        Warehouse $warehouse,
        WarehouseMovementType $type,
        ?string $fromLocation,
        ?string $toLocation,
        User $actor,
        ?string $notes = null,
    ): self {
        return self::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'from_location' => $fromLocation,
            'to_location' => $toLocation,
            'type' => $type,
            'quantity' => $product->quantity,
            'notes' => $notes,
            'created_by' => $actor->id,
        ]);
    }
}
