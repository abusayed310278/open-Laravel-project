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

    public function store(Request $request, Product $product): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if (Auth::check() && !Auth::user()->isCustomer()) {
            $msg = 'Only customer accounts can purchase items on the marketplace.';

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 403);
            }

            return back()->with('error', $msg);
        }

        $request->validate(['quantity' => ['nullable', 'integer', 'min:1']]);

        abort_unless($product->publication_status->value === 'published', 404);

        $this->carts->add($this->currentCart(), $product, $request->integer('quantity', 1));

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "\"{$product->title}\" was added to your cart.",
                'cart_count' => $this->currentCart()->totalCount(),
            ]);
        }

        if ($request->boolean('checkout')) {
            return redirect()->route('checkout');
        }

        return back()->with('status', "\"{$product->title}\" was added to your cart.");
    }

    public function update(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeItem($item);

        $request->validate(['quantity' => ['required', 'integer', 'min:0']]);

        $newQty = $request->integer('quantity');
        $this->carts->updateQuantity($item, $newQty);

        $cart = $this->currentCart();
        $cart->load('items.product');

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            $groups = $this->carts->groupedBySeller($cart);
            $grandTotal = collect($groups)->sum(fn ($g) => $g['subtotal'] + $g['shipping']);

            return response()->json([
                'success' => true,
                'cart_item_id' => $item->id,
                'quantity' => $newQty,
                'price' => (float) $item->price,
                'price_formatted' => number_format((float) $item->price, 2),
                'line_total' => $newQty > 0 ? '$' . number_format((float) ($item->fresh()?->lineTotal() ?? 0), 2) : '$0.00',
                'cart_count' => $cart->items()->count(),
                'grand_total' => (float) $grandTotal,
                'grand_total_formatted' => number_format((float) $grandTotal, 2),
                'groups' => collect($groups)->map(fn ($g) => [
                    'seller_id' => $g['seller']->id,
                    'seller_name' => $g['seller_name'] ?? $g['seller']->name,
                    'total' => (float) ($g['subtotal'] + $g['shipping']),
                    'total_formatted' => number_format((float) ($g['subtotal'] + $g['shipping']), 2),
                ])->values(),
                'cart_empty' => $cart->items()->count() === 0,
            ]);
        }

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeItem($item);

        $this->carts->remove($item);

        $cart = $this->currentCart();
        $cart->load('items.product');

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            $groups = $this->carts->groupedBySeller($cart);
            $grandTotal = collect($groups)->sum(fn ($g) => $g['subtotal'] + $g['shipping']);

            return response()->json([
                'success' => true,
                'cart_item_id' => $item->id,
                'cart_count' => $cart->items()->count(),
                'grand_total' => (float) $grandTotal,
                'grand_total_formatted' => number_format((float) $grandTotal, 2),
                'groups' => collect($groups)->map(fn ($g) => [
                    'seller_id' => $g['seller']->id,
                    'seller_name' => $g['seller_name'] ?? $g['seller']->name,
                    'total' => (float) ($g['subtotal'] + $g['shipping']),
                    'total_formatted' => number_format((float) ($g['subtotal'] + $g['shipping']), 2),
                ])->values(),
                'cart_empty' => $cart->items()->count() === 0,
            ]);
        }

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
