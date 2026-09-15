<?php

namespace App\Http\Controllers;

use App\Enums\RefundStatus;
use App\Models\Refund;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerRefundController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $refunds = Refund::query()
            ->with(['vendorOrder', 'order', 'requestedBy'])
            ->whereHas('vendorOrder', fn ($query) => $query->where('vendor_id', Auth::id()))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('seller.refunds.index', [
            'refunds' => $refunds,
            'statuses' => RefundStatus::cases(),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function approve(Request $request, Refund $refund): RedirectResponse
    {
        abort_unless($refund->vendorOrder->vendor_id === Auth::id(), 403);

        $this->payments->approveRefund($refund, $request->user());

        return back()->with('status', 'Refund approved.');
    }

    public function reject(Request $request, Refund $refund): RedirectResponse
    {
        abort_unless($refund->vendorOrder->vendor_id === Auth::id(), 403);

        $this->payments->rejectRefund($refund, $request->user());

        return back()->with('status', 'Refund rejected.');
    }
}
