<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\StoreManualPaymentSubmissionRequest;
use App\Models\VendorOrder;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ManualPaymentSubmissionController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function store(StoreManualPaymentSubmissionRequest $request, VendorOrder $vendorOrder): RedirectResponse
    {
        abort_unless($vendorOrder->order->customer_id === Auth::id(), 403);
        abort_unless($vendorOrder->payment_method === PaymentMethod::ManualBank, 422, 'This vendor order is not paid by bank transfer.');
        abort_if($vendorOrder->manualPaymentSubmission && $vendorOrder->manualPaymentSubmission->status->value === 'verified', 422, 'Payment already verified.');

        $this->payments->submitManualPayment(
            $vendorOrder,
            $request->file('proof'),
            $request->string('reference')->value() ?: null,
            $request->string('bank_name')->value() ?: null,
        );

        return back()->with('status', 'Payment proof submitted — we will verify it shortly.');
    }
}
