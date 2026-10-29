<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionType;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\SettingsService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly SettingsService $settings
    ) {}

    public function index(): View
    {
        $user = Auth::user();
        $type = $user->isBusiness() ? SubscriptionType::Business : SubscriptionType::Saler;

        $adminPaymentMethods = $this->subscriptions->adminPaymentMethods();

        return view('seller.subscriptions.index', [
            'plans' => $this->subscriptions->plansFor($type),
            'current' => $user->activeSubscription()->with('plan', 'credits')->first(),
            'history' => $user->subscriptions()->with('plan')->latest()->get(),
            'adminPaymentMethods' => $adminPaymentMethods,
            'adminBankDetails' => $this->settings->get('bank_details'),
        ]);
    }

    public function store(Request $request, SubscriptionPlan $plan): RedirectResponse
    {
        $user = Auth::user();

        // Enforce role-based plan eligibility (Admins can test any plan)
        if (! $user->isAdmin()) {
            if ($user->isBusiness() && $plan->type !== SubscriptionType::Business) {
                return back()->withErrors([
                    'plan' => 'Store Owners can only subscribe to Store Owner packages.',
                ]);
            }

            if (! $user->isBusiness() && $plan->type !== SubscriptionType::Saler) {
                return back()->withErrors([
                    'plan' => 'Individual Sellers can only subscribe to Individual Seller packages.',
                ]);
            }
        }

        // Validate admin-activated payment method
        $activePaymentMethods = $this->subscriptions->adminPaymentMethods();
        $allowedMethods = array_keys(array_filter($activePaymentMethods));

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:stripe,paypal,manual_bank,cod'],
            'bank_reference' => ['nullable', 'string', 'max:255'],
        ]);

        if (! in_array($validated['payment_method'], $allowedMethods, true)) {
            return back()->withErrors([
                'payment_method' => 'The selected payment method is currently disabled by the administrator.',
            ]);
        }

        $subscription = $this->subscriptions->purchase(
            $user,
            $plan,
            $validated['payment_method'],
            ['reference' => $validated['bank_reference'] ?? null]
        );

        $cancelRoute = $user->isAdmin()
            ? route('admin.subscriptions.plans.index')
            : ($user->isBusiness() ? route('business.subscription.index') : route('saler.subscriptions.index'));

        if ($validated['payment_method'] === 'stripe' && $subscription->status === SubscriptionStatus::Pending) {
            return redirect()->route('subscriptions.stripe-portal', $subscription);
        }

        if ($validated['payment_method'] === 'paypal' && $subscription->status === SubscriptionStatus::Pending) {
            return redirect()->route('subscriptions.paypal-portal', $subscription);
        }

        if ($validated['payment_method'] === 'manual_bank' && $subscription->status === SubscriptionStatus::Pending) {
            return redirect()->route('subscriptions.bank-portal', $subscription);
        }

        $indexRoute = $user->isAdmin()
            ? 'admin.subscriptions.plans.index'
            : ($user->isBusiness() ? 'business.subscription.index' : 'saler.subscriptions.index');

        return redirect()->route($indexRoute)->with('status', "Subscription for \"{$plan->name}\" processed.");
    }

    public function stripePortal(Subscription $subscription): View
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        return view('seller.subscriptions.stripe-portal', [
            'subscription' => $subscription->load('plan'),
            'stripeKey' => $this->settings->get('stripe_publishable_key') ?: config('services.stripe.key'),
        ]);
    }

    public function stripeConfirm(Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        $this->subscriptions->activate($subscription, 'stripe');

        $route = Auth::user()->isAdmin()
            ? 'admin.subscriptions.plans.index'
            : (Auth::user()->isBusiness() ? 'business.subscription.index' : 'saler.subscriptions.index');

        return redirect()->route($route)->with('status', "Payment completed via Stripe! Your \"{$subscription->plan->name}\" subscription is now active.");
    }

    public function stripeSuccess(Subscription $subscription): RedirectResponse
    {
        return $this->stripeConfirm($subscription);
    }

    public function paypalPortal(Subscription $subscription): View
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        return view('seller.subscriptions.paypal-portal', [
            'subscription' => $subscription->load('plan'),
            'paypalClientId' => $this->settings->get('paypal_client_id') ?: config('services.paypal.client_id'),
        ]);
    }

    public function paypalConfirm(Subscription $subscription): RedirectResponse
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        $this->subscriptions->activate($subscription, 'paypal');

        $route = Auth::user()->isAdmin()
            ? 'admin.subscriptions.plans.index'
            : (Auth::user()->isBusiness() ? 'business.subscription.index' : 'saler.subscriptions.index');

        return redirect()->route($route)->with('status', "Payment completed via PayPal! Your \"{$subscription->plan->name}\" subscription is now active.");
    }

    public function bankPortal(Subscription $subscription): View
    {
        abort_unless($subscription->user_id === Auth::id(), 403);

        return view('seller.subscriptions.bank-portal', [
            'subscription' => $subscription->load('plan'),
            'bankDetails' => $this->settings->get('bank_details'),
        ]);
    }

    public function cancel(): RedirectResponse
    {
        $subscription = Auth::user()->activeSubscription;

        abort_unless($subscription, 404);

        $this->subscriptions->cancel($subscription);

        return back()->with('status', 'Subscription cancelled.');
    }
}

