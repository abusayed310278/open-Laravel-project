<?php

namespace App\Services;

use App\Enums\PaymentRoute;
use App\Enums\StorageStatus;
use App\Enums\WarehouseMovementType;
use App\Enums\WarehouseStatus;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseMovement;
use App\Models\WarehouseProduct;
use App\Notifications\WarehouseDepositRequested;
use App\Notifications\WarehouseProductReceived;
use App\Notifications\WarehouseProductReleased;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseService
{
    /**
     * A seller asks to deposit an already-verified product. Nothing moves
     * physically yet — this just opens the pending_delivery record.
     */
    public function requestDeposit(Product $product, User $seller, Warehouse $warehouse): WarehouseProduct
    {
        return DB::transaction(function () use ($product, $seller, $warehouse) {
            $entry = WarehouseProduct::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'seller_id' => $seller->id,
                'quantity' => $product->quantity,
                'storage_status' => StorageStatus::PendingDelivery,
            ]);

            $product->update(['warehouse_status' => WarehouseStatus::Pending]);

            $seller->notify(new WarehouseDepositRequested($entry));

            ActivityLog::record('warehouse.deposit_requested', $entry);

            return $entry;
        });
    }

    /**
     * Warehouse staff confirm physical receipt, assign a storage slot, and
     * record the condition on arrival. This is the point where the product
     * flips to the Openbox payment route.
     */
    public function receive(WarehouseProduct $entry, User $staff, WarehouseLocation $location, ?string $conditionAtReceipt, ?string $conditionNotes): WarehouseProduct
    {
        return DB::transaction(function () use ($entry, $staff, $location, $conditionAtReceipt, $conditionNotes) {
            $entry->update([
                'warehouse_location_id' => $location->id,
                'received_at' => now(),
                'condition_at_receipt' => $conditionAtReceipt,
                'storage_status' => StorageStatus::Stored,
            ]);

            $location->update(['is_occupied' => true]);

            $entry->product->update([
                'warehouse_status' => WarehouseStatus::Stored,
                'payment_route' => PaymentRoute::Openbox,
            ]);

            $entry->receipt()->create([
                'receipt_number' => 'WR-'.now()->format('Y').'-'.Str::padLeft((string) (WarehouseProduct::query()->whereNotNull('received_at')->count()), 6, '0'),
                'received_by' => $staff->id,
                'condition_notes' => $conditionNotes,
            ]);

            WarehouseMovement::createFor($entry->product, $entry->warehouse, WarehouseMovementType::In, null, $location->label(), $staff);

            $entry->seller->notify(new WarehouseProductReceived($entry));

            ActivityLog::record('warehouse.received', $entry);

            return $entry->fresh();
        });
    }

    public function release(WarehouseProduct $entry, User $staff, string $reason): WarehouseProduct
    {
        return DB::transaction(function () use ($entry, $staff, $reason) {
            $previousLocation = $entry->location?->label();

            $entry->update([
                'storage_status' => StorageStatus::Released,
                'released_at' => now(),
                'release_reason' => $reason,
            ]);

            $entry->location?->update(['is_occupied' => false]);

            $entry->product->update(['warehouse_status' => WarehouseStatus::Released]);

            WarehouseMovement::createFor($entry->product, $entry->warehouse, WarehouseMovementType::Out, $previousLocation, null, $staff, $reason);

            $entry->seller->notify(new WarehouseProductReleased($entry, $reason));

            ActivityLog::record('warehouse.released', $entry, ['reason' => $reason]);

            return $entry->fresh();
        });
    }
}
