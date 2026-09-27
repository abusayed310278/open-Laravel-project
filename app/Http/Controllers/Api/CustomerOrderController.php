<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\VendorOrder;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->with(['vendorOrders.items', 'vendorOrders.vendor', 'vendorOrders.refunds', 'shippingAddress'])
            ->latest()
            ->paginate(10);

        return OrderResource::collection($orders)->response();
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_unless($order->customer_id === $request->user()->id, 403);

        $order->load(['vendorOrders.items', 'vendorOrders.vendor', 'vendorOrders.refunds', 'shippingAddress']);

        return (new OrderResource($order))->response();
    }

    public function submitPaymentProof(Request $request, VendorOrder $vendorOrder): JsonResponse
    {
        abort_unless($vendorOrder->order->customer_id === $request->user()->id, 403);

        $request->validate([
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('proof');
        $path = $file->store('payment-proofs', 'public');

        $submission = $this->payments->submitManualProof(
            $vendorOrder,
            $path,
            $request->input('notes')
        );

        return response()->json([
            'message' => 'Payment proof submitted successfully',
            'submission_id' => $submission->id,
            'status' => $submission->status->value,
        ]);
    }
}
