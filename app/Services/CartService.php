<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CartService
{
    public function forUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function forGuest(string $sessionId): Cart
    {
        return Cart::query()->firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    public function add(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        $item = $cart->items()->firstOrNew(['product_id' => $product->id, 'variant_id' => null]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->price = $product->price;
        $item->payment_route = $product->payment_route;
        $item->cart_id = $cart->id;
        $item->save();

        return $item;
    }

    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $quantity]);
    }

    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Merge a guest's session cart into their account cart on login, then
     * drop the now-empty guest cart.
     */
    public function mergeGuestCartIntoUser(string $sessionId, User $user): void
    {
        $guestCart = Cart::query()->where('session_id', $sessionId)->whereNull('user_id')->first();

        if (! $guestCart || $guestCart->items->isEmpty()) {
            return;
        }

        $userCart = $this->forUser($user);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()->where('product_id', $guestItem->product_id)->where('variant_id', $guestItem->variant_id)->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
            } else {
                $guestItem->update(['cart_id' => $userCart->id]);
            }
        }

        $guestCart->delete();
    }

    /**
     * @return array<int, array{seller: User, route: string, items: Collection<int, CartItem>, subtotal: float, shipping: float}>
     */
    public function groupedBySeller(Cart $cart): array
    {
        return $cart->items->load('product.user')
            ->groupBy('product.user_id')
            ->map(function ($items) {
                $seller = $items->first()->product->user;

                return [
                    'seller' => $seller,
                    'route' => $items->first()->payment_route->value,
                    'items' => $items,
                    'subtotal' => (float) $items->sum(fn (CartItem $i) => $i->price * $i->quantity),
                    'shipping' => $this->shippingFor($items),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CartItem>  $items
     */
    private function shippingFor($items): float
    {
        return (float) $items->sum(function (CartItem $item) {
            $product = $item->product;

            return match ($product->shipping_type->value) {
                'flat_rate' => (float) ($product->shipping_flat_rate ?? 0),
                default => 0.0,
            };
        });
    }

    public function sessionId(): string
    {
        return request()->session()->get('cart_session_id') ?? tap(
            (string) Str::uuid(),
            fn (string $id) => request()->session()->put('cart_session_id', $id),
        );
    }
}
