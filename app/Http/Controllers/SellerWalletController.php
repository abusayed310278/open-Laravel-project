<?php

namespace App\Http\Controllers;

use App\Enums\SellerPayoutMethod;
use App\Http\Requests\StoreSellerPayoutRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerWalletController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('seller.wallet.index', [
            'wallet' => $this->wallets->walletFor($user),
            'transactions' => $user->sellerTransactions()->latest()->paginate(15),
            'payouts' => $user->payouts()->latest()->get(),
            'methods' => SellerPayoutMethod::cases(),
        ]);
    }

    public function requestPayout(StoreSellerPayoutRequest $request): RedirectResponse
    {
        $this->wallets->requestPayout(
            Auth::user(),
            $request->float('amount'),
            SellerPayoutMethod::from($request->string('method')->value()),
        );

        return back()->with('status', 'Payout requested — we\'ll review it shortly.');
    }
}
