<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'subtotal' => (float) $this->subtotal,
            'shipping_total' => (float) $this->shipping_total,
            'total' => (float) $this->total,
            'vendor_orders' => VendorOrderResource::collection($this->whenLoaded('vendorOrders')),
            'shipping_address' => $this->whenLoaded('shippingAddress', fn () => [
                'name' => $this->shippingAddress->name,
                'phone' => $this->shippingAddress->phone,
                'address_line_1' => $this->shippingAddress->address_line_1,
                'city' => $this->shippingAddress->city,
                'state' => $this->shippingAddress->state,
                'country' => $this->shippingAddress->country,
                'postal_code' => $this->shippingAddress->postal_code,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
