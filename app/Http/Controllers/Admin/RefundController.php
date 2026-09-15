<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentRoute;
use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RefundController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $refunds = Refund::query()
            ->with(['vendorOrder.vendor', 'order', 'requestedBy'])
            ->whereHas('vendorOrder', fn ($query) => $query->where('payment_route', PaymentRoute::Openbox))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.refunds.index', [
            'refunds' => $refunds,
            'statuses' => RefundStatus::cases(),
        ]);
    }

    public function approve(Request $request, Refund $refund): RedirectResponse
    {
        abort_unless($refund->vendorOrder->payment_route === PaymentRoute::Openbox, 403);

        $this->payments->approveRefund($refund, $request->user());

        return back()->with('status', 'Refund approved.');
    }

    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        abort_unless($refund->vendorOrder->payment_route === PaymentRoute::Openbox, 403);

        $this->payments->rejectRefund($refund, $request->user());

        return back()->with('status', 'Refund rejected.');
    }
}
