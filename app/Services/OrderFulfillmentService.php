<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\User;
use App\Models\VendorOrder;
use App\Notifications\OrderStatusUpdated;

class OrderFulfillmentService
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly InventoryService $inventoryService,
    ) {}

    /**
     * Forward-only fulfillment states a seller can move a vendor order
     * through from its current status. Cancelled is reachable from any
     * pre-shipment state; nothing is reachable once Delivered or Cancelled.
     *
     * @return array<int, OrderStatus>
     */
    public function nextStatuses(VendorOrder $vendorOrder): array
    {
        return match ($vendorOrder->status) {
            OrderStatus::Confirmed => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing => [OrderStatus::Packed, OrderStatus::Cancelled],
            OrderStatus::Packed => [OrderStatus::Shipped, OrderStatus::Cancelled],
            OrderStatus::Shipped => [OrderStatus::OutForDelivery, OrderStatus::Delivered],
            OrderStatus::OutForDelivery => [OrderStatus::Delivered],
            default => [],
        };
    }

    public function updateStatus(VendorOrder $vendorOrder, OrderStatus $status, User $actor, ?string $trackingNumber = null): VendorOrder
    {
        abort_unless(in_array($status, $this->nextStatuses($vendorOrder), true), 422, 'That status transition is not allowed.');

        $attributes = ['status' => $status];

        if ($trackingNumber) {
            $attributes['tracking_number'] = $trackingNumber;
        }

        if ($status === OrderStatus::Shipped) {
            $attributes['shipped_at'] = now();
        }

        if ($status === OrderStatus::Delivered) {
            $attributes['delivered_at'] = now();
        }

        $vendorOrder->update($attributes);

        if ($status === OrderStatus::Delivered) {
            $this->commissionService->releaseForVendorOrder($vendorOrder);
        }

        if ($status === OrderStatus::Cancelled) {
            $vendorOrder->loadMissing('items.product');

            foreach ($vendorOrder->items as $item) {
                if ($item->product) {
                    $this->inventoryService->restore($item->product, $item->quantity, InventoryMovementType::Return, $item, $actor, 'Order cancelled');
                }
            }
        }

        $vendorOrder->statusHistories()->create([
            'order_id' => $vendorOrder->order_id,
            'status' => $status->value,
            'created_by' => $actor->id,
        ]);

        $this->syncOrderStatus($vendorOrder->order);

        $vendorOrder->order->customer->notify(new OrderStatusUpdated($vendorOrder));

        ActivityLog::record('order.status_updated', $vendorOrder, ['status' => $status->value]);

        return $vendorOrder->fresh();
    }

    /**
     * Best-effort aggregation of a multi-vendor order's overall status from
     * its vendor orders' individual statuses — there's no single "true"
     * status when vendors fulfill independently, so this picks the least
     * advanced meaningful state across all of them.
     */
    private function syncOrderStatus(Order $order): void
    {
        $statuses = $order->vendorOrders()->pluck('status');

        $order->update(['status' => match (true) {
            $statuses->every(fn (OrderStatus $s) => $s === OrderStatus::Cancelled) => OrderStatus::Cancelled,
            $statuses->every(fn (OrderStatus $s) => $s === OrderStatus::Delivered) => OrderStatus::Delivered,
            $statuses->contains(OrderStatus::OutForDelivery) => OrderStatus::OutForDelivery,
            $statuses->contains(OrderStatus::Shipped) => OrderStatus::Shipped,
            $statuses->contains(OrderStatus::Packed) => OrderStatus::Packed,
            $statuses->contains(OrderStatus::Processing) => OrderStatus::Processing,
            default => OrderStatus::Confirmed,
        }]);

        $order->statusHistories()->create([
            'status' => $order->fresh()->status->value,
            'created_by' => null,
        ]);
    }
}
