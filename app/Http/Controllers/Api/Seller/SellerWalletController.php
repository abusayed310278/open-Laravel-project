<?php

namespace App\Http\Controllers\Api\Seller;

use App\Enums\SellerPayoutMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSellerPayoutRequest;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SellerWalletController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets
    ) {}

    /**
     * Get wallet balance, payout methods, recent transactions, and payout history.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $this->wallets->walletFor($user);

        $methods = array_map(fn (SellerPayoutMethod $m) => [
            'value' => $m->value,
            'label' => $m->label(),
        ], SellerPayoutMethod::cases());

        $transactions = $user->sellerTransactions()
            ->latest()
            ->paginate(15);

        $payouts = $user->payouts()
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'payout_number' => $p->payout_number,
                'amount' => (float) $p->amount,
                'method' => $p->method?->value ?? $p->method,
                'status' => $p->status?->value ?? $p->status,
                'status_label' => ucfirst(str_replace('_', ' ', $p->status?->value ?? (string) $p->status)),
                'notes' => $p->notes ?? $p->admin_notes,
                'created_at' => $p->created_at?->toIso8601String(),
            ]);

        $settings = $user->paymentSettings;

        return response()->json([
            'wallet' => [
                'available_balance' => (float) $wallet->available_balance,
                'pending_balance' => (float) $wallet->pending_balance,
                'total_earned' => (float) $wallet->total_earned,
                'total_withdrawn' => (float) $wallet->total_withdrawn,
                'currency' => 'QAR',
            ],
            'payment_settings' => [
                'has_bank' => !empty($settings?->bank_name) && !empty($settings?->account_number),
                'bank_name' => $settings?->bank_name ?? '',
                'account_number' => $settings?->account_number ?? '',
                'has_stripe' => $settings?->hasStripeConnected() ?? false,
                'has_paypal' => $settings?->hasPaypalConnected() ?? false,
            ],
            'payout_methods' => $methods,
            'payouts' => $payouts,
            'transactions' => $transactions->getCollection()->map(fn ($tx) => [
                'id' => $tx->id,
                'type' => $tx->type?->value ?? $tx->type,
                'amount' => (float) $tx->amount,
                'description' => $tx->description,
                'created_at' => $tx->created_at?->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Submit payout request from available balance.
     */
    public function requestPayout(StoreSellerPayoutRequest $request): JsonResponse
    {
        $user = Auth::user();
        $wallet = $this->wallets->walletFor($user);
        $amount = $request->float('amount');

        if ($wallet->available_balance < $amount) {
            return response()->json([
                'message' => 'Insufficient available balance. Your available balance is ' . number_format($wallet->available_balance, 2) . ' QAR.',
            ], 422);
        }

        $method = SellerPayoutMethod::from($request->string('method')->value());
        $payout = $this->wallets->requestPayout($user, $amount, $method);

        return response()->json([
            'message' => 'Payout request for ' . number_format($amount, 2) . ' QAR has been submitted for admin review.',
            'payout' => [
                'id' => $payout->id,
                'amount' => (float) $payout->amount,
                'status' => $payout->status?->value ?? $payout->status,
            ],
            'available_balance' => (float) $wallet->fresh()->available_balance,
        ]);
    }
}
