<?php

namespace App\Jobs;

use App\Models\Cart;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Weekly sweep: guest carts (no user_id) that haven't been touched in 30
 * days are abandoned — delete them so the table doesn't grow forever from
 * one-off visitors. Logged-in users' carts are never touched here.
 */
class CleanupExpiredCarts implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Cart::query()
            ->whereNull('user_id')
            ->where('updated_at', '<=', now()->subDays(30))
            ->each(fn (Cart $cart) => $cart->delete());
    }
}
