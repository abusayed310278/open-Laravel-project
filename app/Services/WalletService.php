<?php

namespace App\Services;

use App\Enums\SellerPayoutMethod;
use App\Enums\SellerPayoutStatus;
use App\Enums\SellerTransactionSource;
use App\Enums\SellerTransactionType;
use App\Models\ActivityLog;
use App\Models\SellerPayout;
use App\Models\SellerWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletService
{
    public function walletFor(User $seller): SellerWallet
    {
        return SellerWallet::query()->firstOrCreate(['user_id' => $seller->id]);
    }

    public function creditPending(User $seller, float $amount, SellerTransactionSource $source, ?Model $reference = null, ?string $notes = null): void
    {
        $wallet = $this->walletFor($seller);

        $wallet->increment('pending_balance', $amount);
        $wallet->increment('total_earned', $amount);

        $this->logTransaction($wallet, $seller, SellerTransactionType::Credit, $source, $amount, $reference, $notes);
    }

    /**
     * Move already-earned funds from pending to available — no change to
     * total_earned since that was counted when the sale was credited.
     */
    public function releasePendingToAvailable(User $seller, float $amount): void
    {
        $wallet = $this->walletFor($seller);

        $wallet->decrement('pending_balance', min($amount, (float) $wallet->pending_balance));
        $wallet->increment('available_balance', $amount);
    }

    /**
     * Reverse a seller's earnings for a refunded item — pulls from
     * whichever balance the funds currently sit in.
     */
    public function reverseForRefund(User $seller, float $amount, bool $wasAvailable, ?Model $reference = null): void
    {
        $wallet = $this->walletFor($seller);

        if ($wasAvailable) {
            $wallet->decrement('available_balance', min($amount, (float) $wallet->available_balance));
        } else {
            $wallet->decrement('pending_balance', min($amount, (float) $wallet->pending_balance));
        }

        $wallet->decrement('total_earned', min($amount, (float) $wallet->total_earned));

        $this->logTransaction($wallet, $seller, SellerTransactionType::Debit, SellerTransactionSource::Refund, $amount, $reference);
    }

    public function requestPayout(User $seller, float $amount, SellerPayoutMethod $method): SellerPayout
    {
        $wallet = $this->walletFor($seller);

        abort_if($amount <= 0, 422, 'Enter a valid payout amount.');
        abort_if($amount > (float) $wallet->available_balance, 422, 'Payout amount exceeds your available balance.');

        $wallet->decrement('available_balance', $amount);

        $payout = SellerPayout::create([
            'payout_number' => 'PO-'.now()->format('Y').'-'.Str::upper(Str::random(8)),
            'user_id' => $seller->id,
            'amount' => $amount,
            'method' => $method,
            'status' => SellerPayoutStatus::Requested,
        ]);

        ActivityLog::record('payout.requested', $payout);

        return $payout;
    }

    public function approvePayout(SellerPayout $payout, User $approver): SellerPayout
    {
        $payout->update(['status' => SellerPayoutStatus::Approved, 'approved_by' => $approver->id]);

        ActivityLog::record('payout.approved', $payout);

        return $payout;
    }

    public function markProcessing(SellerPayout $payout): SellerPayout
    {
        $payout->update(['status' => SellerPayoutStatus::Processing]);

        return $payout;
    }

    public function completePayout(SellerPayout $payout): SellerPayout
    {
        $payout->update(['status' => SellerPayoutStatus::Completed, 'processed_at' => now()]);

        $wallet = $this->walletFor($payout->user);
        $wallet->increment('total_withdrawn', $payout->amount);

        $this->logTransaction($wallet, $payout->user, SellerTransactionType::Debit, SellerTransactionSource::Payout, (float) $payout->amount, $payout);

        ActivityLog::record('payout.completed', $payout);

        return $payout;
    }

    public function rejectPayout(SellerPayout $payout, User $approver, string $notes): SellerPayout
    {
        $wallet = $this->walletFor($payout->user);
        $wallet->increment('available_balance', $payout->amount);

        $payout->update([
            'status' => SellerPayoutStatus::Rejected,
            'approved_by' => $approver->id,
            'notes' => $notes,
            'processed_at' => now(),
        ]);

        ActivityLog::record('payout.rejected', $payout, ['notes' => $notes]);

        return $payout;
    }

    private function logTransaction(SellerWallet $wallet, User $seller, SellerTransactionType $type, SellerTransactionSource $source, float $amount, ?Model $reference, ?string $notes = null): void
    {
        $wallet->transactions()->create([
            'user_id' => $seller->id,
            'type' => $type,
            'source' => $source,
            'amount' => $amount,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
        ]);
    }
}
