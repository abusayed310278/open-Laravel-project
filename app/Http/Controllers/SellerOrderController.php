<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\UpdateVendorOrderStatusRequest;
use App\Models\VendorOrder;
use App\Services\OrderFulfillmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerOrderController extends Controller
{
    public function __construct(private readonly OrderFulfillmentService $fulfillment) {}

    public function index(Request $request): View
    {
        $vendorOrders = VendorOrder::query()
            ->where('vendor_id', Auth::id())
            ->with(['order.customer', 'items'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('seller.orders.index', [
            'vendorOrders' => $vendorOrders,
            'statuses' => OrderStatus::cases(),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function show(VendorOrder $vendorOrder): View
    {
        abort_unless($vendorOrder->vendor_id === Auth::id(), 403);

        return view('seller.orders.show', [
            'vendorOrder' => $vendorOrder->load(['order.customer', 'order.shippingAddress', 'items', 'statusHistories.createdBy']),
            'nextStatuses' => $this->fulfillment->nextStatuses($vendorOrder),
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function updateStatus(UpdateVendorOrderStatusRequest $request, VendorOrder $vendorOrder): RedirectResponse
    {
        abort_unless($vendorOrder->vendor_id === Auth::id(), 403);

        $this->fulfillment->updateStatus(
            $vendorOrder,
            OrderStatus::from($request->string('status')->value()),
            $request->user(),
            $request->string('tracking_number')->value() ?: null,
        );

        return back()->with('status', 'Order status updated.');
    }
}
