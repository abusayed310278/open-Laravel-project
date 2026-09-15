<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerInventoryController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $productIds = $user->products()->pluck('id');

        return view('seller.inventory.index', [
            'products' => $user->products()->orderBy('quantity')->paginate(15, ['*'], 'products'),
            'movements' => InventoryMovement::query()
                ->whereIn('product_id', $productIds)
                ->with('product')
                ->latest()
                ->paginate(15, ['*'], 'movements'),
            'routePrefix' => $user->isBusiness() ? 'business.' : 'saler.',
        ]);
    }
}
