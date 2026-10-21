<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Services\Auth\RegistrationService;
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

        if ($user && ! $user->canShop()) {
            return redirect()->route('shop')->with('error', 'Only customer and store owner accounts can purchase items on the marketplace.');
        }

        if (! $user) {
            // After signing in from the checkout page, come straight back to it.
            session(['url.intended' => route('checkout')]);
        }

        if ($user && session()->has('checkout.draft')) {
            session()->flashInput(session()->pull('checkout.draft'));
        }

        $cart = $user ? $this->carts->forUser($user) : $this->carts->forGuest($this->carts->sessionId());

        if ($cart->items->isEmpty()) {
            $recoveredOrder = $this->checkout->recoverPendingPaymentCart($user, session('pending_stripe_order_id'));
            if ($recoveredOrder) {
                session()->forget('pending_stripe_order_id');
                session()->flash('info', 'Your payment was cancelled and your cart items have been restored.');
                $cart->load('items.product');
            }
        }

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

        if ($user && ! $user->canShop()) {
            return redirect()->route('shop')->with('error', 'Only customer and store owner accounts can purchase items on the marketplace.');
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
            $validatedAccount = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'phone' => ['nullable', 'string', 'max:30', 'unique:'.User::class],
                'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
                'terms' => ['accepted'],
            ], [
                'terms.accepted' => 'Please accept the Terms of Service to create your account and complete your order.',
            ]);

            $registrationService = app(RegistrationService::class);
            $user = $registrationService->register([
                'account_type' => 'customer',
                'name' => $validatedAccount['name'],
                'email' => $validatedAccount['email'],
                'phone' => $validatedAccount['phone'] ?? null,
                'password' => $validatedAccount['password'],
            ]);

            Auth::login($user);

            $sessionId = $request->session()->get('cart_session_id');
            if ($sessionId) {
                $this->carts->mergeGuestCartIntoUser($sessionId, $user);
            }

            try {
                event(new \Illuminate\Auth\Events\Registered($user));
            } catch (\Throwable $e) {
                report($e);
            }

            $cart = $this->carts->forUser($user);
            $cart->load('items.product');
        }

        // Auto-fill shipping name/phone from account info if left blank
        $shippingInput = $request->input('shipping', []);
        if (empty($shippingInput['name']) && $user?->name) {
            $shippingInput['name'] = $user->name;
        }
        if (empty($shippingInput['phone']) && $user?->phone) {
            $shippingInput['phone'] = $user->phone;
        }
        $request->merge(['shipping' => $shippingInput]);

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
            session(['pending_stripe_order_id' => $order->id]);
            $token = sha1($order->id . $order->order_number . config('app.key'));
            return redirect()->route('checkout.stripe-portal', ['order' => $order, 'token' => $token]);
        }

        return redirect()->route('orders.confirmation', $order)->with('status', 'Order placed successfully! Welcome to Openbox.');
    }

    public function stripePortal(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        return view('checkout.stripe-portal', [
            'order' => $order->load('vendorOrders.items'),
        ]);
    }

    public function stripeCancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        $user = Auth::user() ?? $order->customer;
        $this->checkout->restoreCartFromOrder($order);
        $this->checkout->cancelUnconfirmedOrder($order, $user, 'Stripe payment was cancelled by customer.');
        session()->forget('pending_stripe_order_id');

        return redirect()->route('checkout')->with('info', 'Your payment was cancelled and your cart items have been restored. You can choose another payment method below.');
    }

    public function stripeConfirm(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        foreach ($order->vendorOrders as $vendorOrder) {
            app(PaymentService::class)->confirmPayment($vendorOrder, 'stripe');
        }

        if ($order->customer) {
            $this->carts->forUser($order->customer)->items()->delete();
        }
        session()->forget('pending_stripe_order_id');

        return redirect()->route('orders.confirmation', $order)->with('status', 'Payment completed via Stripe!');
    }

    public function stripeSuccess(Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        foreach ($order->vendorOrders as $vendorOrder) {
            app(PaymentService::class)->confirmPayment($vendorOrder, 'stripe');
        }

        if ($order->customer) {
            $this->carts->forUser($order->customer)->items()->delete();
        }
        session()->forget('pending_stripe_order_id');

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

    private function authorizeOrderAccess(Order $order): void
    {
        $token = request()->query('token');
        $validToken = sha1($order->id . $order->order_number . config('app.key'));
        $hasValidToken = $token && hash_equals($validToken, (string) $token);

        abort_unless($order->customer_id === Auth::id() || $hasValidToken, 403);
    }
}
