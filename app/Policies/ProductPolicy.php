<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Owner or an admin/verifier can view the underlying (not-yet-published)
     * record; the live storefront query is separate and doesn't go through
     * this policy.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->id === $product->user_id || $user->isAdmin() || $user->isVerifier();
    }

    public function create(User $user): bool
    {
        return $user->isBusiness() || $user->isSaler() || $user->isAdmin();
    }

    /**
     * Once a product has been physically verified, its core listing fields
     * (title, condition, description, price, images, serial/IMEI) are
     * locked — the seller can no longer edit them. Admins can still update
     * for operational reasons (e.g. fixing a typo after verification).
     */
    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $product->user_id && ! $product->isLocked();
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->id === $product->user_id || $user->isAdmin();
    }
}
