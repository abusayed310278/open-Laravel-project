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
    public function resolveCart(?User $user): Cart
    {
        if ($user) {
            return $this->forUser($user);
        }
        $headerSession = request()->header('X-Session-ID');
        $sessionId = $headerSession ?: $this->sessionId();
        return $this->forGuest($sessionId);
    }

    public function getCart(?User $user): array
    {
        $cart = $this->resolveCart($user);
        $items = $cart->items()->with('product')->get();
        $subtotal = (float) $items->sum(fn ($i) => $i->price * $i->quantity);
        $shipping = $items->isEmpty() ? 0.0 : 25.0;
        $total = $subtotal + $shipping;

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total,
        ];
    }

    public function forUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function forGuest(string $sessionId): Cart
    {
        return Cart::query()->firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    public function add($userOrCart, Product $product, int $quantity = 1): CartItem
    {
        $cart = ($userOrCart instanceof Cart)
            ? $userOrCart
            : $this->resolveCart($userOrCart instanceof User ? $userOrCart : null);

        $item = $cart->items()->firstOrNew(['product_id' => $product->id, 'variant_id' => null]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $quantity;
        $item->price = $product->price;
        $item->payment_route = $product->payment_route ?? 'cod';
        $item->cart_id = $cart->id;
        $item->save();

        return $item;
    }

    public function updateQuantity($userOrItem, $productOrQuantity, ?int $quantity = null): void
    {
        if ($userOrItem instanceof CartItem) {
            $item = $userOrItem;
            $qty = (int) $productOrQuantity;
        } else {
            $user = $userOrItem instanceof User ? $userOrItem : null;
            $product = $productOrQuantity;
            $qty = $quantity ?? 0;
            $cart = $this->resolveCart($user);
            $item = $cart->items()->where('product_id', $product->id)->first();
        }

        if (! $item) {
            return;
        }

        if ($qty <= 0) {
            $item->delete();

            return;
        }

        $item->update(['quantity' => $qty]);
    }

    public function remove($userOrItem, ?Product $product = null): void
    {
        if ($userOrItem instanceof CartItem) {
            $userOrItem->delete();

            return;
        }

        $user = $userOrItem instanceof User ? $userOrItem : null;
        $cart = $this->resolveCart($user);
        if ($product) {
            $cart->items()->where('product_id', $product->id)->delete();
        }
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
                    'route' => $items->first()->payment_route->value ?? 'cod',
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

            $shippingType = is_object($product->shipping_type) ? ($product->shipping_type->value ?? 'free') : $product->shipping_type;

            return match ($shippingType) {
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