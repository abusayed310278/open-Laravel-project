<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $carts,
        private readonly CheckoutService $checkout,
    ) {}

    public function index(): View
    {
        $user = Auth::user();
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
        $cart = $this->carts->forUser($user);

        $available = $this->checkout->availablePaymentMethods($cart);

        $request->validate([
            'payment_method' => ['required', Rule::in(array_map(fn (PaymentMethod $m) => $m->value, $available))],
        ]);

        $shipping = $this->resolveAddress($request, 'shipping');
        $billing = $request->boolean('same_as_shipping', true) ? $shipping : $this->resolveAddress($request, 'billing');

        $order = $this->checkout->placeOrder($user, $cart, $shipping, $billing, PaymentMethod::from($request->string('payment_method')->value()));

        return redirect()->route('orders.confirmation', $order)->with('status', 'Order placed!');
    }

    public function confirmation(Order $order): View
    {
        abort_unless($order->customer_id === Auth::id(), 403);

        return view('checkout.confirmation', [
            'order' => $order->load('vendorOrders.items'),
        ]);
    }

    private function resolveAddress(Request $request, string $prefix): Address
    {
        if ($request->filled("{$prefix}_address_id")) {
            return Auth::user()->addresses()->findOrFail($request->integer("{$prefix}_address_id"));
        }

        $validated = $request->validate([
            "{$prefix}.name" => ['required', 'string', 'max:255'],
            "{$prefix}.phone" => ['required', 'string', 'max:30'],
            "{$prefix}.line1" => ['required', 'string', 'max:255'],
            "{$prefix}.line2" => ['nullable', 'string', 'max:255'],
            "{$prefix}.city" => ['required', 'string', 'max:120'],
            "{$prefix}.state" => ['nullable', 'string', 'max:120'],
            "{$prefix}.country" => ['required', 'string', 'max:120'],
            "{$prefix}.postal_code" => ['nullable', 'string', 'max:20'],
        ])[$prefix];

        return Auth::user()->addresses()->create($validated);
    }
}
