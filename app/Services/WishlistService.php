<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;

class WishlistService
{
    public function forUser(User $user): Wishlist
    {
        return Wishlist::query()->firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * @return bool True if the product is now on the wishlist, false if it was just removed.
     */
    public function toggle(User $user, Product $product): bool
    {
        $wishlist = $this->forUser($user);
        $existing = $wishlist->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $wishlist->items()->create(['product_id' => $product->id]);

        return true;
    }

    public function remove(User $user, Product $product): void
    {
        $this->forUser($user)->items()->where('product_id', $product->id)->delete();
    }
}
