<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionType;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    public function index(): View
    {
        $user = Auth::user();
        $type = $user->isBusiness() ? SubscriptionType::Business : SubscriptionType::Saler;

        return view('seller.subscriptions.index', [
            'plans' => $this->subscriptions->plansFor($type),
            'current' => $user->activeSubscription()->with('plan', 'credits')->first(),
            'history' => $user->subscriptions()->with('plan')->latest()->get(),
        ]);
    }

    public function store(SubscriptionPlan $plan): RedirectResponse
    {
        $this->subscriptions->purchase(Auth::user(), $plan);

        return back()->with('status', "You're now subscribed to \"{$plan->name}\".");
    }

    public function cancel(): RedirectResponse
    {
        $subscription = Auth::user()->activeSubscription;

        abort_unless($subscription, 404);

        $this->subscriptions->cancel($subscription);

        return back()->with('status', 'Subscription cancelled.');
    }
}
