<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutAddressController extends Controller
{
    public const SESSION_KEY = 'guest_checkout.address';

    public function __construct(private readonly CartService $carts) {}

    /**
     * Address step shown to guests when they press "Proceed to Checkout".
     */
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('checkout');
        }

        $cart = $this->carts->forGuest($this->carts->sessionId());
        if ($cart->items()->doesntExist()) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        return view('checkout.address', [
            'address' => session(self::SESSION_KEY, []),
            'groups' => $this->carts->groupedBySeller($cart),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        session([self::SESSION_KEY => $data]);

        // Next step is decided separately; for now continue to the normal (authenticated) checkout.
        session(['url.intended' => route('checkout')]);

        return redirect()->route('login')->with('status', 'Address saved. Log in or create an account to finish your order.');
    }
}
