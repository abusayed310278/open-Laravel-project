<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseLocationRequest;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;

class WarehouseLocationController extends Controller
{
    public function store(StoreWarehouseLocationRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->locations()->create($request->validated());

        return back()->with('status', 'Slot added.');
    }

    public function destroy(Warehouse $warehouse, WarehouseLocation $location): RedirectResponse
    {
        abort_if($location->is_occupied, 422, 'This slot is currently occupied.');

        $location->delete();

        return back()->with('status', 'Slot removed.');
    }
}
