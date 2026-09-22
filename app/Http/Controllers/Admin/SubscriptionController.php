<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionsService) {}

    public function index(Request $request): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::query()
                ->with(['user', 'plan'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statusCounts' => Subscription::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status'),
            'activeRevenue' => Subscription::query()
                ->where('status', SubscriptionStatus::Active)
                ->join('subscription_plans', 'subscriptions.plan_id', '=', 'subscription_plans.id')
                ->sum('subscription_plans.price'),
            'users' => User::query()
                ->whereIn('role', [\App\Enums\UserRole::Saler, \App\Enums\UserRole::Business, \App\Enums\UserRole::Customer])
                ->orderBy('name')
                ->get(),
            'plans' => SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:subscription_plans,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);
        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        $subscription = $this->subscriptionsService->purchase($user, $plan);

        if ($user->salerProfile) {
            $user->salerProfile->update(['is_store_active' => true]);
        }
        if ($user->businessProfile) {
            $user->businessProfile->update(['is_store_active' => true]);
        }

        return back()->with('status', "Subscription '{$plan->name}' granted to {$user->name}. Verified status is now active.");
    }

    public function toggleStatus(Subscription $subscription): RedirectResponse
    {
        if ($subscription->status === SubscriptionStatus::Active) {
            $subscription->update([
                'status' => SubscriptionStatus::Cancelled,
                'cancelled_at' => now(),
                'auto_renew' => false,
            ]);
            $message = "Subscription for {$subscription->user->name} has been deactivated.";
        } else {
            $subscription->update([
                'status' => SubscriptionStatus::Active,
                'starts_at' => now(),
                'cancelled_at' => null,
            ]);

            $user = $subscription->user;
            if ($user->salerProfile) {
                $user->salerProfile->update(['is_store_active' => true]);
            }
            if ($user->businessProfile) {
                $user->businessProfile->update(['is_store_active' => true]);
            }

            if (($subscription->plan->type === \App\Enums\SubscriptionType::Saler || $user->isSaler()) && !$subscription->credits) {
                $creditsCount = $subscription->plan->listing_credits ?: 50;
                $subscription->credits()->create([
                    'user_id' => $user->id,
                    'total_credits' => $creditsCount,
                    'used_credits' => 0,
                    'remaining_credits' => $creditsCount,
                    'expires_at' => $subscription->ends_at,
                ]);
            }

            $message = "Subscription for {$user->name} has been activated and verified status is live.";
        }

        return back()->with('status', $message);
    }
}
