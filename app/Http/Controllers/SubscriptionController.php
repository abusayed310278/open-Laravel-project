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

        $adminPaymentMethods = [
            'stripe' => $this->settings->get('stripe_enabled') === '1'
                || (filled($this->settings->get('stripe_publishable_key')) && $this->settings->has('stripe_secret_key'))
                || (filled(config('services.stripe.key')) && filled(config('services.stripe.secret'))),
            'paypal' => $this->settings->get('paypal_enabled') === '1'
                || (filled($this->settings->get('paypal_client_id')) && $this->settings->has('paypal_client_secret'))
                || filled(config('services.paypal.client_id')),
            'manual_bank' => filled($this->settings->get('bank_details')),
            'cod' => true,
        ];

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
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:stripe,paypal,manual_bank,cod'],
            'bank_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $subscription = $this->subscriptions->purchase(
            $user,
            $plan,
            $validated['payment_method'],
            ['reference' => $validated['bank_reference'] ?? null]
        );

        $routePrefix = $user->isBusiness() ? 'business.subscription.' : 'saler.subscriptions.';

        if ($validated['payment_method'] === 'stripe' && $subscription->status === SubscriptionStatus::Pending) {
            $secretKey = $this->settings->getDecrypted('stripe_secret_key') ?: config('services.stripe.secret');

            if (filled($secretKey)) {
                try {
                    $response = Http::withBasicAuth($secretKey, '')
                        ->asForm()
                        ->post('https://api.stripe.com/v1/checkout/sessions', [
                            'payment_method_types' => ['card'],
                            'line_items' => [
                                [
                                    'price_data' => [
                                        'currency' => 'usd',
                                        'product_data' => [
                                            'name' => 'Subscription: ' . $plan->name,
                                        ],
                                        'unit_amount' => (int) round((float) $plan->price * 100),
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'success_url' => route($routePrefix . 'stripe-success', $subscription) . '?session_id={CHECKOUT_SESSION_ID}',
                            'cancel_url' => route($routePrefix . ($user->isBusiness() ? 'index' : 'index')),
                        ]);

                    if ($response->successful() && isset($response->json()['url'])) {
                        return redirect()->away($response->json()['url']);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Stripe API subscription session creation failed: ' . $e->getMessage());
                }
            }

            return redirect()->route($routePrefix . 'stripe-portal', $subscription);
        }

        if ($validated['payment_method'] === 'paypal' && $subscription->status === SubscriptionStatus::Pending) {
            return redirect()->route($routePrefix . 'paypal-portal', $subscription);
        }

        if ($validated['payment_method'] === 'manual_bank' && $subscription->status === SubscriptionStatus::Pending) {
            return redirect()->route($routePrefix . 'bank-portal', $subscription);
        }

        $indexRoute = $routePrefix . ($user->isBusiness() ? 'index' : 'index');

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

        $route = Auth::user()->isBusiness() ? 'business.subscription.index' : 'saler.subscriptions.index';

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

        $route = Auth::user()->isBusiness() ? 'business.subscription.index' : 'saler.subscriptions.index';

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

