<?php

namespace App\Jobs;

use App\Enums\SellerPayoutStatus;
use App\Enums\UserRole;
use App\Models\SellerPayout;
use App\Models\User;
use App\Notifications\PendingPayoutsDigest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

/**
 * Daily digest, not an auto-approval — there's no real payment-gateway
 * payout API wired in yet (same "wire later" stance as Stripe/PayPal), so
 * actually moving money still requires a human. This just makes sure a
 * backlog of `requested` payouts older than a day doesn't go unnoticed.
 */
class ProcessPendingPayouts implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $pending = SellerPayout::query()
            ->where('status', SellerPayoutStatus::Requested)
            ->where('created_at', '<=', now()->subDay())
            ->get();

        if ($pending->isEmpty()) {
            return;
        }

        Notification::send(
            User::query()->where('role', UserRole::Admin)->get(),
            new PendingPayoutsDigest($pending->count(), (float) $pending->sum('amount')),
        );
    }
}
