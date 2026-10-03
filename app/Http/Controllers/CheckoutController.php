<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isCustomer()) {
            return redirect()->route('shop')->with('error', 'Only customer accounts can purchase items on the marketplace.');
        }

        $cart = $this->carts->forUser($user);

        return view('checkout.index', [
            'summary' => $this->checkout->summary($cart),
            'addresses' => $user->addresses()->orderByDesc('is_default')->get(),
            'paymentMethods' => $this->checkout->availablePaymentMethods($cart),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isCustomer()) {
            return redirect()->route('shop')->with('error', 'Only customer accounts can purchase items on the marketplace.');
        }

        $cart = $this->carts->forUser($user);

        $available = $this->checkout->availablePaymentMethods($cart);

        $request->validate([
            'payment_method' => ['required', Rule::in(array_map(fn (PaymentMethod $m) => $m->value, $available))],
        ]);

        $shipping = $this->resolveAddress($request, 'shipping');
        $billing = $request->boolean('same_as_shipping', true) ? $shipping : $this->resolveAddress($request, 'billing');

        $order = $this->checkout->placeOrder($user, $cart, $shipping, $billing, PaymentMethod::from($request->string('payment_method')->value()));

        if ($request->string('payment_method')->value() === PaymentMethod::Stripe->value) {
            $secretKey = app(SettingsService::class)->getDecrypted('stripe_secret_key') ?: config('services.stripe.secret');

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
                                            'name' => 'Order #' . $order->order_number,
                                        ],
                                        'unit_amount' => (int) round($order->total * 100),
                                    ],
                                    'quantity' => 1,
                                ],
                            ],
                            'mode' => 'payment',
                            'success_url' => route('checkout.stripe-success', $order) . '?session_id={CHECKOUT_SESSION_ID}',
                            'cancel_url' => route('checkout'),
                        ]);

                    if ($response->successful() && isset($response->json()['url'])) {
                        return redirect()->away($response->json()['url']);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Stripe API session creation failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('checkout.stripe-portal', $order);
        }

        return redirect()->route('orders.confirmation', $order)->with('status', 'Order placed!');
    }

    public function stripePortal(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        return view('checkout.stripe-portal', [
            'order' => $order->load('vendorOrders.items'),
        ]);
    }

    public function stripeConfirm(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        foreach ($order->vendorOrders as $vendorOrder) {
            app(PaymentService::class)->confirmPayment($vendorOrder, 'stripe');
        }

        return redirect()->route('orders.confirmation', $order)->with('status', 'Payment completed via Stripe!');
    }

    public function stripeSuccess(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        foreach ($order->vendorOrders as $vendorOrder) {
            app(PaymentService::class)->confirmPayment($vendorOrder, 'stripe');
        }

        return redirect()->route('orders.confirmation', $order)->with('status', 'Payment completed via Stripe!');
    }

    public function confirmation(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        return view('checkout.confirmation', [
            'order' => $order->load('vendorOrders.items'),
        ]);
    }

    private function resolveAddress(Request $request, string $prefix): Address
    {
        if ($request->filled("{$prefix}_address_id") && is_numeric($request->input("{$prefix}_address_id"))) {
            return Auth::user()->addresses()->findOrFail($request->integer("{$prefix}_address_id"));
        }

        $validated = $request->validate([
            "{$prefix}.label" => ['nullable', 'string', 'max:50'],
            "{$prefix}.name" => ['required', 'string', 'max:255'],
            "{$prefix}.phone" => ['required', 'string', 'max:30'],
            "{$prefix}.line1" => ['required', 'string', 'max:255'],
            "{$prefix}.line2" => ['nullable', 'string', 'max:255'],
            "{$prefix}.city" => ['required', 'string', 'max:120'],
            "{$prefix}.state" => ['nullable', 'string', 'max:120'],
            "{$prefix}.country" => ['required', 'string', 'max:120'],
            "{$prefix}.postal_code" => ['nullable', 'string', 'max:20'],
        ])[$prefix];

        $address = Auth::user()->addresses()->create($validated);

        if ($request->boolean("{$prefix}.is_default") || Auth::user()->addresses()->count() === 1) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }

        return $address;
    }

    private function authorizeOrderAccess(Order $order): void
    {
        $token = request()->query('token');
        $validToken = sha1($order->id . $order->order_number . config('app.key'));
        $hasValidToken = $token && hash_equals($validToken, (string) $token);

        abort_unless($order->customer_id === Auth::id() || $hasValidToken, 403);
    }
}
