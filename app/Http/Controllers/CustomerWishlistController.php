<?php

namespace App\Http\Controllers;

use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerWishlistController extends Controller
{
    public function __construct(private readonly WishlistService $wishlists) {}

    public function index(): View
    {
        return view('account.wishlist.index', [
            'wishlist' => $this->wishlists->forUser(Auth::user())->load('items.product.images'),
        ]);
    }
}
