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
    public function purchase(User $user, SubscriptionPlan $plan, ?string $paymentMethod = 'stripe', ?array $paymentDetails = []): Subscription
    {
        return DB::transaction(function () use ($user, $plan, $paymentMethod, $paymentDetails) {
            $endsAt = match ($plan->billing_cycle) {
                BillingCycle::OneTime => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                BillingCycle::Monthly => now()->addMonth(),
                BillingCycle::Yearly => now()->addYear(),
            };

            $status = in_array($paymentMethod, ['stripe', 'paypal', 'manual_bank', 'bank_transfer'], true) && (float) $plan->price > 0
                ? SubscriptionStatus::Pending
                : SubscriptionStatus::Active;

            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'status' => $status,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'billing_cycle' => $plan->billing_cycle,
                'auto_renew' => $plan->billing_cycle !== BillingCycle::OneTime,
                'next_billing_at' => $plan->billing_cycle === BillingCycle::OneTime ? null : $endsAt,
            ]);

            if ($status === SubscriptionStatus::Active) {
                $this->grantActiveSubscriptionBenefits($subscription, $user, $plan, $endsAt);
            }

            ActivityLog::record('subscription.purchased', $subscription, [
                'plan' => $plan->name,
                'payment_method' => $paymentMethod,
                'reference' => $paymentDetails['reference'] ?? null,
            ]);

            return $subscription;
        });
    }

    /**
     * Activate a pending subscription after payment confirmation.
     */
    public function activate(Subscription $subscription, ?string $paymentMethod = null, ?array $paymentDetails = []): Subscription
    {
        return DB::transaction(function () use ($subscription, $paymentMethod, $paymentDetails) {
            $user = $subscription->user;
            $plan = $subscription->plan;

            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'starts_at' => now(),
                'ends_at' => match ($plan->billing_cycle) {
                    BillingCycle::OneTime => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                    BillingCycle::Monthly => now()->addMonth(),
                    BillingCycle::Yearly => now()->addYear(),
                },
            ]);

            $this->grantActiveSubscriptionBenefits($subscription, $user, $plan, $subscription->ends_at);

            ActivityLog::record('subscription.activated', $subscription, [
                'plan' => $plan->name,
                'payment_method' => $paymentMethod,
                'reference' => $paymentDetails['reference'] ?? null,
            ]);

            return $subscription;
        });
    }

    private function grantActiveSubscriptionBenefits(Subscription $subscription, User $user, SubscriptionPlan $plan, ?\Carbon\Carbon $endsAt): void
    {
        if ($plan->type === SubscriptionType::Saler || $user->role === UserRole::Saler) {
            $creditsCount = $plan->listing_credits ?: 50;
            $subscription->credits()->updateOrCreate(
                ['subscription_id' => $subscription->id],
                [
                    'user_id' => $user->id,
                    'total_credits' => $creditsCount,
                    'used_credits' => 0,
                    'remaining_credits' => $creditsCount,
                    'expires_at' => $endsAt,
                ]
            );
        }

        if ($user->salerProfile) {
            $user->salerProfile->update(['is_store_active' => true]);
        }
        if ($user->businessProfile) {
            $user->businessProfile->update(['is_store_active' => true]);
        }

        $user->notify(new SubscriptionActivated($subscription));
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
            $subscription = $seller->activeSubscription;
            if (! $subscription) {
                return false;
            }

            $credits = $subscription->credits;
            if ($credits) {
                return (bool) $credits->hasCredits();
            }

            return true;
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
