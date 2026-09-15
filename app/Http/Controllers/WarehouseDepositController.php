<?php

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Http\Requests\StoreWarehouseDepositRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WarehouseDepositController extends Controller
{
    public function __construct(private readonly WarehouseService $warehouse) {}

    public function index(): View
    {
        $user = Auth::user();

        return view('seller.warehouse.index', [
            'entries' => $user->warehouseDeposits()->with(['product', 'warehouse', 'location'])->latest()->paginate(15),
            'eligibleProducts' => $user->products()
                ->where('verification_status', VerificationStatus::Verified)
                ->where('warehouse_status', 'not_deposited')
                ->get(),
            'warehouses' => Warehouse::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreWarehouseDepositRequest $request): RedirectResponse
    {
        $product = Auth::user()->products()->findOrFail($request->integer('product_id'));
        $warehouse = Warehouse::query()->findOrFail($request->integer('warehouse_id'));

        $this->warehouse->requestDeposit($product, Auth::user(), $warehouse);

        return back()->with('status', 'Deposit request submitted.');
    }
}
