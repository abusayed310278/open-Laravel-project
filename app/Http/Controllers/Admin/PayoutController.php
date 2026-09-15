<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SellerPayoutStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPayoutRequest;
use App\Models\SellerPayout;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayoutController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(Request $request): View
    {
        $payouts = SellerPayout::query()
            ->with('user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payouts.index', [
            'payouts' => $payouts,
            'statuses' => SellerPayoutStatus::cases(),
        ]);
    }

    public function approve(Request $request, SellerPayout $payout): RedirectResponse
    {
        $this->wallets->approvePayout($payout, $request->user());

        return back()->with('status', 'Payout approved.');
    }

    public function markProcessing(SellerPayout $payout): RedirectResponse
    {
        $this->wallets->markProcessing($payout);

        return back()->with('status', 'Payout marked as processing.');
    }

    public function complete(SellerPayout $payout): RedirectResponse
    {
        $this->wallets->completePayout($payout);

        return back()->with('status', 'Payout marked as completed.');
    }

    public function reject(RejectPayoutRequest $request, SellerPayout $payout): RedirectResponse
    {
        $this->wallets->rejectPayout($payout, $request->user(), $request->string('notes')->value());

        return back()->with('status', 'Payout rejected.');
    }
}
