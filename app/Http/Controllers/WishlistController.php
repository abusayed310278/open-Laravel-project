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
            'wishlist' => $this->wishlists->forUser(Auth::user())->load([
                'items.product.images',
                'items.product.category.attributes',
                'items.product.brand',
                'items.product.user.businessProfile',
                'items.product.user.salerProfile',
                'items.product.attributeValues.attribute',
                'items.product.attributeValues.attributeValue',
            ]),
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

    public function moveToCart(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $user = Auth::user();

        if ($user && ! $user->isCustomer()) {
            $msg = 'Only customer accounts can purchase items on the marketplace.';
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 403);
            }
            return back()->with('error', $msg);
        }

        $this->carts->add($this->carts->forUser($user), $product);
        $this->wishlists->remove($user, $product);

        $msg = "\"{$product->title}\" was moved to your cart.";
        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'cart_count' => $this->carts->forUser($user)->totalCount(),
            ]);
        }

        return back()->with('status', $msg);
    }
}
