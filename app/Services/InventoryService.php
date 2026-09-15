<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InventoryService
{
    /**
     * Decrement stock for a sale, inside the caller's DB transaction. Locks
     * the row and aborts (rolling back the whole transaction) if there
     * isn't enough stock — never let quantity go negative.
     */
    public function deduct(Product $product, int $quantity, InventoryMovementType $type, ?Model $reference = null, ?User $actor = null, ?string $notes = null): void
    {
        /** @var Product $locked */
        $locked = Product::query()->whereKey($product->id)->lockForUpdate()->first();

        abort_if($locked->quantity < $quantity, 422, "Not enough stock for \"{$locked->title}\" (only {$locked->quantity} left).");

        $locked->decrement('quantity', $quantity);

        $this->logMovement($product, -$quantity, $type, $reference, $actor, $notes);
    }

    /**
     * Restore stock — refunds, cancellations, manual increases.
     */
    public function restore(Product $product, int $quantity, InventoryMovementType $type, ?Model $reference = null, ?User $actor = null, ?string $notes = null): void
    {
        $product->increment('quantity', $quantity);

        $this->logMovement($product, $quantity, $type, $reference, $actor, $notes);
    }

    /**
     * Record a manual stock correction (e.g. from editing the quantity
     * field directly) without touching the quantity itself — the caller
     * already saved the new value.
     */
    public function logAdjustment(Product $product, int $delta, User $actor, ?string $notes = null): void
    {
        if ($delta === 0) {
            return;
        }

        $this->logMovement($product, $delta, InventoryMovementType::Adjustment, null, $actor, $notes);
    }

    private function logMovement(Product $product, int $quantity, InventoryMovementType $type, ?Model $reference, ?User $actor, ?string $notes): void
    {
        $product->inventoryMovements()->create([
            'variant_id' => null,
            'type' => $type,
            'quantity' => $quantity,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
            'created_by' => $actor?->id,
            'created_at' => now(),
        ]);
    }
}
