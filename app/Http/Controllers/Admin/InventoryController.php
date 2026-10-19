<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $movements = InventoryMovement::query()
            ->with(['product', 'variant', 'createdBy'])
            ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($pq) => $pq->where('title', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                        ->orWhereHas('variant', fn ($vq) => $vq->where('sku', 'like', "%{$search}%"))
                        ->orWhereHas('createdBy', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.inventory.index', [
            'movements' => $movements,
        ]);
    }
}
