<?php

namespace App\Jobs;

use App\Models\ListingCredit;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Daily sweep: nudge business sellers whose recurring subscription is about
 * to lapse, and salers whose listing credits are about to expire — both
 * within a 7-day window, once per day it's in that window (not deduplicated
 * beyond that; acceptable for a daily reminder).
 */
class SendSubscriptionExpiryReminders implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->with('user')
            ->each(fn (Subscription $subscription) => $subscription->user->notify(
                new SubscriptionExpiring('subscription', $subscription->ends_at)
            ));

        ListingCredit::query()
            ->where('remaining_credits', '>', 0)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(7)])
            ->with('user')
            ->each(fn (ListingCredit $credit) => $credit->user->notify(
                new SubscriptionExpiring('listing credits', $credit->expires_at)
            ));
    }
}
