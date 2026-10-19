<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('search'));

        $warehouses = Warehouse::query()
            ->withCount('warehouseProducts')
            ->with('manager')
            ->when($request->filled('search'), function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('manager', fn ($mq) => $mq->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.warehouses.index', [
            'warehouses' => $warehouses,
            'admins' => User::query()->where('role', UserRole::Admin)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        Warehouse::query()->create($request->validated());

        return back()->with('status', 'Warehouse added.');
    }

    public function show(Warehouse $warehouse): View
    {
        return view('admin.warehouses.show', [
            'warehouse' => $warehouse->load('locations'),
        ]);
    }

    public function update(StoreWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update($request->validated());

        return back()->with('status', 'Warehouse updated.');
    }

    public function toggleActive(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update(['is_active' => ! $warehouse->is_active]);

        $status = $warehouse->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Warehouse {$status}.");
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse removed.');
    }
}
