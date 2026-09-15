<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view('admin.warehouses.index', [
            'warehouses' => Warehouse::query()->withCount('warehouseProducts')->with('manager')->orderBy('name')->get(),
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

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->delete();

        return redirect()->route('admin.warehouses.index')->with('status', 'Warehouse removed.');
    }
}
