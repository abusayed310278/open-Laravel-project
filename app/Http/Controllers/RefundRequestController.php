<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRefundRequestRequest;
use App\Models\VendorOrder;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RefundRequestController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function store(StoreRefundRequestRequest $request, VendorOrder $vendorOrder): RedirectResponse
    {
        $user = Auth::user();

        abort_unless($vendorOrder->order->customer_id === $user->id, 403);
        abort_if($vendorOrder->refunds()->whereIn('status', ['pending', 'approved', 'processing'])->exists(), 422, 'A refund is already in progress for this order.');

        $this->payments->requestRefund($vendorOrder, $user, $request->string('reason')->value(), (float) $vendorOrder->total);

        return back()->with('status', 'Refund requested — we will review it shortly.');
    }
}
