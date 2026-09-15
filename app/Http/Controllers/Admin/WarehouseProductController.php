<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StorageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReceiveWarehouseProductRequest;
use App\Http\Requests\Admin\ReleaseWarehouseProductRequest;
use App\Models\WarehouseLocation;
use App\Models\WarehouseProduct;
use App\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseProductController extends Controller
{
    public function __construct(private readonly WarehouseService $warehouse) {}

    public function index(Request $request): View
    {
        return view('admin.warehouse-products.index', [
            'entries' => WarehouseProduct::query()
                ->with(['product', 'warehouse', 'seller', 'location'])
                ->when($request->filled('status'), fn ($q) => $q->where('storage_status', $request->string('status')))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'statuses' => StorageStatus::cases(),
        ]);
    }

    public function receive(WarehouseProduct $warehouseProduct): View
    {
        return view('admin.warehouse-products.receive', [
            'entry' => $warehouseProduct->load(['product', 'warehouse', 'seller']),
            'availableLocations' => WarehouseLocation::query()
                ->where('warehouse_id', $warehouseProduct->warehouse_id)
                ->where('is_occupied', false)
                ->orderBy('zone')
                ->get(),
        ]);
    }

    public function storeReceive(ReceiveWarehouseProductRequest $request, WarehouseProduct $warehouseProduct): RedirectResponse
    {
        $location = WarehouseLocation::query()->findOrFail($request->integer('warehouse_location_id'));

        $this->warehouse->receive(
            $warehouseProduct,
            $request->user(),
            $location,
            $request->string('condition_at_receipt')->value() ?: null,
            $request->string('condition_notes')->value() ?: null,
        );

        return redirect()->route('admin.warehouse-products.index')->with('status', 'Product received and stored.');
    }

    public function release(ReleaseWarehouseProductRequest $request, WarehouseProduct $warehouseProduct): RedirectResponse
    {
        $this->warehouse->release($warehouseProduct, $request->user(), $request->string('reason')->value());

        return back()->with('status', 'Product released from storage.');
    }
}
