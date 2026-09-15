<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $carts) {}

    public function index(): View
    {
        $cart = $this->currentCart();

        return view('cart.index', [
            'groups' => $this->carts->groupedBySeller($cart),
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        abort_unless($product->publication_status->value === 'published', 404);

        $this->carts->add($this->currentCart(), $product, $request->integer('quantity', 1));

        if ($request->boolean('checkout')) {
            return redirect()->route('checkout');
        }

        return back()->with('status', "\"{$product->title}\" was added to your cart.");
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item);

        $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $this->carts->updateQuantity($item, $request->integer('quantity'));

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item);

        $this->carts->remove($item);

        return back()->with('status', 'Item removed from cart.');
    }

    private function currentCart(): Cart
    {
        return Auth::check()
            ? $this->carts->forUser(Auth::user())
            : $this->carts->forGuest($this->carts->sessionId());
    }

    private function authorizeItem(CartItem $item): void
    {
        abort_unless($item->cart_id === $this->currentCart()->id, 403);
    }
}
