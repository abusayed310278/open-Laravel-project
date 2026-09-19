<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function __construct(
        private readonly WishlistService $wishlists,
        private readonly CartService $carts,
    ) {}

    public function index(): View
    {
        return view('wishlist.index', [
            'wishlist' => $this->wishlists->forUser(Auth::user())->load('items.product.images'),
        ]);
    }

    public function toggle(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $added = $this->wishlists->toggle(Auth::user(), $product);

        $message = $added ? "\"{$product->title}\" was added to your wishlist." : "\"{$product->title}\" was removed from your wishlist.";

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json(['added' => $added, 'message' => $message]);
        }

        return back()->with('status', $message);
    }

    public function moveToCart(Product $product): RedirectResponse
    {
        $user = Auth::user();

        $this->carts->add($this->carts->forUser($user), $product);
        $this->wishlists->remove($user, $product);

        return back()->with('status', "\"{$product->title}\" was moved to your cart.");
    }
}
