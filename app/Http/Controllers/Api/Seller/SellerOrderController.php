<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\VendorOrder;
use App\Services\OrderFulfillmentService;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SellerOrderController extends Controller
{
    public function __construct(
        private readonly OrderFulfillmentService $fulfillment
    ) {}

    /**
     * List vendor orders for authenticated seller with status filtering and search.
     */
    public function index(Request $request): JsonResponse
    {
        $sellerId = Auth::id();

        $query = VendorOrder::query()
            ->where('vendor_id', $sellerId)
            ->with(['order.customer', 'items.product.images', 'invoice']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('vendor_order_number', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                    })
                    ->orWhereHas('items', fn ($iq) => $iq->where('product_title', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate((int) $request->input('per_page', 15));

        // Status counts for badge tabs
        $counts = [
            'all' => VendorOrder::where('vendor_id', $sellerId)->count(),
            'pending' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Pending->value)->count(),
            'confirmed' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Confirmed->value)->count(),
            'processing' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Processing->value)->count(),
            'shipped' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Shipped->value)->count(),
            'delivered' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Delivered->value)->count(),
            'cancelled' => VendorOrder::where('vendor_id', $sellerId)->where('status', OrderStatus::Cancelled->value)->count(),
        ];

        $items = $orders->getCollection()->map(function (VendorOrder $vendorOrder) {
            $firstItem = $vendorOrder->items->first();
            $thumbnail = null;
            if ($firstItem?->product?->images?->isNotEmpty()) {
                $thumbnail = MediaUrl::resolve($firstItem->product->images->first()->image_path);
            }

            return [
                'id' => $vendorOrder->id,
                'order_id' => $vendorOrder->order_id,
                'order_number' => $vendorOrder->vendor_order_number ?: ($vendorOrder->order?->order_number ?? '#' . $vendorOrder->id),
                'status' => $vendorOrder->status->value,
                'status_label' => ucfirst(str_replace('_', ' ', $vendorOrder->status->value)),
                'customer_name' => $vendorOrder->order?->customer?->name ?? 'Guest Customer',
                'customer_phone' => $vendorOrder->order?->customer?->phone,
                'items_count' => $vendorOrder->items->count(),
                'item_summary' => $firstItem ? $firstItem->product_title . ($vendorOrder->items->count() > 1 ? ' +' . ($vendorOrder->items->count() - 1) . ' more' : '') : '',
                'thumbnail' => $thumbnail,
                'subtotal' => (float) $vendorOrder->subtotal,
                'shipping' => (float) $vendorOrder->shipping,
                'tax' => (float) $vendorOrder->tax,
                'total' => (float) $vendorOrder->total,
                'currency' => 'QAR',
                'payment_method' => $vendorOrder->payment_method?->value ?? 'standard',
                'created_at' => $vendorOrder->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'orders' => $items,
            'counts' => $counts,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get single vendor order with full detail.
     */
    public function show(VendorOrder $vendorOrder): JsonResponse
    {
        abort_unless($vendorOrder->vendor_id === Auth::id(), 403, 'Unauthorized');

        $vendorOrder->load([
            'order.customer',
            'order.shippingAddress',
            'items.product.images',
            'invoice',
            'statusHistories.createdBy',
        ]);

        $nextStatuses = array_map(
            fn (OrderStatus $status) => [
                'value' => $status->value,
                'label' => ucfirst(str_replace('_', ' ', $status->value)),
            ],
            $this->fulfillment->nextStatuses($vendorOrder)
        );

        $order = $vendorOrder->order;
        $shippingAddress = $order?->shippingAddress;

        return response()->json([
            'order' => [
                'id' => $vendorOrder->id,
                'order_id' => $vendorOrder->order_id,
                'order_number' => $vendorOrder->vendor_order_number ?: ($order?->order_number ?? '#' . $vendorOrder->id),
                'status' => $vendorOrder->status->value,
                'status_label' => ucfirst(str_replace('_', ' ', $vendorOrder->status->value)),
                'tracking_number' => $vendorOrder->tracking_number,
                'payment_method' => $vendorOrder->payment_method?->value,
                'subtotal' => (float) $vendorOrder->subtotal,
                'shipping' => (float) $vendorOrder->shipping,
                'tax' => (float) $vendorOrder->tax,
                'total' => (float) $vendorOrder->total,
                'currency' => 'QAR',
                'shipped_at' => $vendorOrder->shipped_at?->toIso8601String(),
                'delivered_at' => $vendorOrder->delivered_at?->toIso8601String(),
                'created_at' => $vendorOrder->created_at?->toIso8601String(),
                'customer' => [
                    'name' => $order?->customer?->name ?? 'Customer',
                    'email' => $order?->customer?->email,
                    'phone' => $order?->customer?->phone,
                ],
                'shipping_address' => $shippingAddress ? [
                    'recipient_name' => $shippingAddress->recipient_name ?? $order?->customer?->name,
                    'phone' => $shippingAddress->phone ?? $order?->customer?->phone,
                    'address_line_1' => $shippingAddress->address_line_1,
                    'address_line_2' => $shippingAddress->address_line_2,
                    'city' => $shippingAddress->city,
                    'state' => $shippingAddress->state,
                    'postal_code' => $shippingAddress->postal_code,
                    'country' => $shippingAddress->country ?? 'Qatar',
                ] : null,
                'items' => $vendorOrder->items->map(function ($item) {
                    $thumbnail = null;
                    if ($item->product?->images?->isNotEmpty()) {
                        $thumbnail = MediaUrl::resolve($item->product->images->first()->image_path);
                    }

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'title' => $item->product_title,
                        'thumbnail' => $thumbnail,
                        'unit_price' => (float) $item->unit_price,
                        'quantity' => (int) $item->quantity,
                        'total_price' => (float) $item->total_price,
                    ];
                }),
                'status_history' => $vendorOrder->statusHistories->map(fn ($history) => [
                    'status' => $history->status?->value ?? $history->status,
                    'note' => $history->note,
                    'changed_by' => $history->createdBy?->name,
                    'created_at' => $history->created_at?->toIso8601String(),
                ]),
                'next_statuses' => $nextStatuses,
            ],
        ]);
    }

    /**
     * Update vendor order fulfillment status.
     */
    public function updateStatus(Request $request, VendorOrder $vendorOrder): JsonResponse
    {
        abort_unless($vendorOrder->vendor_id === Auth::id(), 403, 'Unauthorized');

        $request->validate([
            'status' => ['required', 'string'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $targetStatus = OrderStatus::tryFrom($request->string('status')->value());
        if (! $targetStatus) {
            return response()->json([
                'message' => 'Invalid order status provided.',
            ], 422);
        }

        $this->fulfillment->updateStatus(
            $vendorOrder,
            $targetStatus,
            $request->user(),
            $request->filled('tracking_number') ? $request->string('tracking_number')->value() : null,
        );

        return response()->json([
            'message' => 'Order status successfully updated to ' . ucfirst(str_replace('_', ' ', $targetStatus->value)),
            'order' => [
                'id' => $vendorOrder->id,
                'status' => $vendorOrder->fresh()->status->value,
                'tracking_number' => $vendorOrder->fresh()->tracking_number,
            ],
        ]);
    }
}
