<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PaymentService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

        if ($user && ! $user->isCustomer()) {
            return redirect()->route('shop')->with('error', 'Only customer accounts can purchase items on the marketplace.');
        }

        if (! $user) {
            // After signing in from the checkout page, come straight back to it.
            session(['url.intended' => route('checkout')]);
        }

        $cart = $user ? $this->carts->forUser($user) : $this->carts->forGuest($this->carts->sessionId());

        return view('checkout.index', [
            'summary' => $this->checkout->summary($cart),
            'addresses' => $user ? $user->addresses()->orderByDesc('is_default')->get() : collect(),
            'paymentMethods' => $this->checkout->availablePaymentMethods($cart),
            'isGuest' => $user === null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user && ! $user->isCustomer()) {
            return redirect()->route('shop')->with('error', 'Only customer accounts can purchase items on the marketplace.');
        }

        $cart = $user ? $this->carts->forUser($user) : $this->carts->forGuest($this->carts->sessionId());

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty. Please add items to your cart before proceeding to checkout.');
        }

        $available = $this->checkout->availablePaymentMethods($cart);

        $request->validate([
            'payment_method' => ['required', Rule::in(array_map(fn (PaymentMethod $m) => $m->value, $available))],
        ]);

        if (! $user) {
            // Everything is validated before an account is created, so a failed form never leaves one behind.
            $guest = $request->validate([
                'guest_email' => ['required', 'email', 'max:255'],
            ]);

            // A guest has no saved addresses, so the shipping form is always filled in; reuse its name and phone.
            $shippingInput = $request->validate($this->addressRules('shipping'))['shipping'];
            $guest['guest_name'] = $shippingInput['name'];
            $guest['guest_phone'] = $shippingInput['phone'];
            if (! $request->boolean('same_as_shipping', true)) {
                $request->validate($this->addressRules('billing'));
            }

            if (User::query()->where('email', $guest['guest_email'])->exists()) {
                return back()->withInput()->withErrors(['guest_email' => 'An account with this email already exists. Please log in to continue.']);
            }

            $user = $this->createGuestAccount($guest);
            $cart = $this->carts->forUser($user);
        }

        try {
            $shipping = $this->resolveAddress($request, 'shipping');
            $billing = $request->boolean('same_as_shipping', true) ? $shipping : $this->resolveAddress($request, 'billing');

            $order = $this->checkout->placeOrder($user, $cart, $shipping, $billing, PaymentMethod::from($request->string('payment_method')->value()));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->withInput()->withErrors(['checkout' => $e->getMessage()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Checkout processing error: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withInput()->withErrors(['checkout' => $e->getMessage() ?: 'Unable to process checkout. Please try again.']);
        }

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

        $validated = $request->validate($this->addressRules($prefix))[$prefix];

        $address = Auth::user()->addresses()->create($validated);

        if ($request->boolean("{$prefix}.is_default") || Auth::user()->addresses()->count() === 1) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        }

        return $address;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function addressRules(string $prefix): array
    {
        return [
            "{$prefix}.label" => ['nullable', 'string', 'max:50'],
            "{$prefix}.name" => ['required', 'string', 'max:255'],
            "{$prefix}.phone" => ['required', 'string', 'max:30'],
            "{$prefix}.line1" => ['required', 'string', 'max:255'],
            "{$prefix}.line2" => ['nullable', 'string', 'max:255'],
            "{$prefix}.city" => ['required', 'string', 'max:120'],
            "{$prefix}.state" => ['nullable', 'string', 'max:120'],
            "{$prefix}.country" => ['required', 'string', 'max:120'],
            "{$prefix}.postal_code" => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Guest checkout: create a customer account from the contact details, sign it in, and
     * carry the guest cart over. A password-setup link is emailed so they can log in later.
     *
     * @param  array{guest_name: string, guest_email: string, guest_phone: string}  $guest
     */
    private function createGuestAccount(array $guest): User
    {
        $cartSessionId = $this->carts->sessionId();

        $user = User::query()->create([
            'name' => Str::title(trim($guest['guest_name'])),
            'email' => $guest['guest_email'],
            'phone' => $guest['guest_phone'],
            'password' => Str::random(40),
            'role' => UserRole::Customer,
            'status' => UserStatus::Active,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        Auth::login($user);
        request()->session()->regenerate();
        $this->carts->mergeGuestCartIntoUser($cartSessionId, $user);

        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            Log::warning('Guest checkout: could not send password setup link: '.$e->getMessage());
        }

        return $user;
    }

    private function authorizeOrderAccess(Order $order): void
    {
        $token = request()->query('token');
        $validToken = sha1($order->id . $order->order_number . config('app.key'));
        $hasValidToken = $token && hash_equals($validToken, (string) $token);

        abort_unless($order->customer_id === Auth::id() || $hasValidToken, 403);
    }
}
