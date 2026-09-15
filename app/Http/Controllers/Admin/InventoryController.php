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
        return view('admin.inventory.index', [
            'movements' => InventoryMovement::query()
                ->with(['product', 'createdBy'])
                ->when($request->filled('product_id'), fn ($query) => $query->where('product_id', $request->integer('product_id')))
                ->latest()
                ->paginate(30)
                ->withQueryString(),
        ]);
    }
}
