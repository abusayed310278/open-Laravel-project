<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeRefund = $this->refunds ? $this->refunds->first(fn ($r) => in_array($r->status->value, ['pending', 'approved', 'processing'])) : null;

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'vendor_order_number' => $this->vendor_order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_badge_color' => $this->status->badgeColor(),
            'subtotal' => (float) $this->subtotal,
            'shipping' => (float) $this->shipping,
            'total' => (float) $this->total,
            'payment_route' => $this->payment_route->value,
            'payment_method' => $this->payment_method->value,
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'role' => $this->vendor->role->value,
                'role_label' => $this->vendor->role->label(),
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_title' => $item->product_title,
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
                'total' => (float) ($item->price * $item->quantity),
            ])),
            'active_refund' => $activeRefund ? new RefundResource($activeRefund) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
