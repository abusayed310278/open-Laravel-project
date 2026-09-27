<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRefundRequestRequest;
use App\Http\Resources\RefundResource;
use App\Models\Refund;
use App\Models\VendorOrder;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function store(StoreRefundRequestRequest $request, VendorOrder $vendorOrder): JsonResponse
    {
        $user = $request->user();

        abort_unless($vendorOrder->order->customer_id === $user->id, 403);
        abort_if($vendorOrder->refunds()->whereIn('status', ['pending', 'approved', 'processing'])->exists(), 422, 'A refund is already in progress for this order.');

        $refund = $this->payments->requestRefund(
            $vendorOrder,
            $user,
            $request->string('reason')->value(),
            (float) $vendorOrder->total
        );

        return response()->json([
            'message' => 'Refund requested successfully',
            'refund' => new RefundResource($refund),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $refunds = Refund::query()
            ->with('vendorOrder')
            ->where('requested_by', $request->user()->id)
            ->latest()
            ->paginate(15);

        return RefundResource::collection($refunds)->response();
    }
}
