<?php

namespace App\Services;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Notifications\SubscriptionActivated;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * @return Collection<int, SubscriptionPlan>
     */
    public function plansFor(SubscriptionType $type): Collection
    {
        return SubscriptionPlan::query()->active()->forType($type)->orderBy('sort_order')->get();
    }

    /**
     * Activates the subscription immediately. There's no live payment
     * gateway wired in yet (that's Phase 14) — once Stripe/PayPal exist,
     * this becomes "create a pending subscription + checkout session" and
     * a webhook flips it to active instead of doing so synchronously here.
     */
    public function purchase(User $user, SubscriptionPlan $plan): Subscription
    {
        return DB::transaction(function () use ($user, $plan) {
            $endsAt = match ($plan->billing_cycle) {
                BillingCycle::OneTime => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                BillingCycle::Monthly => now()->addMonth(),
                BillingCycle::Yearly => now()->addYear(),
            };

            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => SubscriptionStatus::Active,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'billing_cycle' => $plan->billing_cycle,
                'auto_renew' => $plan->billing_cycle !== BillingCycle::OneTime,
                'next_billing_at' => $plan->billing_cycle === BillingCycle::OneTime ? null : $endsAt,
            ]);

            if ($plan->type === SubscriptionType::Saler && $plan->listing_credits) {
                $subscription->credits()->create([
                    'user_id' => $user->id,
                    'total_credits' => $plan->listing_credits,
                    'used_credits' => 0,
                    'remaining_credits' => $plan->listing_credits,
                    'expires_at' => $endsAt,
                ]);
            }

            $user->notify(new SubscriptionActivated($subscription));

            ActivityLog::record('subscription.purchased', $subscription, ['plan' => $plan->name]);

            return $subscription;
        });
    }

    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
            'auto_renew' => false,
        ]);

        ActivityLog::record('subscription.cancelled', $subscription);

        return $subscription;
    }

    /**
     * Whether a seller is currently allowed to publish another product —
     * admins are exempt, salers need a listing credit, businesses need an
     * active subscription and room under its product limit.
     */
    public function canPublish(User $seller): bool
    {
        if ($seller->role === UserRole::Admin) {
            return true;
        }

        if ($seller->role === UserRole::Saler) {
            $credits = $seller->activeSubscription?->credits;

            return (bool) $credits?->hasCredits();
        }

        if ($seller->role === UserRole::Business) {
            $subscription = $seller->activeSubscription;

            if (! $subscription?->isActive()) {
                return false;
            }

            if ($subscription->plan->max_products === null) {
                return true;
            }

            $publishedCount = $seller->products()->where('publication_status', 'published')->count();

            return $publishedCount < $subscription->plan->max_products;
        }

        return false;
    }

    /**
     * Consume a saler's listing credit for a newly published product.
     */
    public function recordPublish(User $seller, Product $product): void
    {
        $subscription = $seller->activeSubscription;

        if (! $subscription) {
            return;
        }

        if ($seller->role === UserRole::Saler && $subscription->credits) {
            $subscription->credits->consume();

            $product->update([
                'expires_at' => $subscription->credits->expires_at,
            ]);
        }

        $subscription->items()->create([
            'product_id' => $product->id,
            'published_at' => now(),
            'expires_at' => $product->expires_at,
        ]);
    }
}
