<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VendorOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SellerCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $sellerId = Auth::id();
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'spent_desc')->value();

        // Subquery for seller's customer stats
        $customerStats = VendorOrder::query()
            ->where('vendor_id', $sellerId)
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->select(
                'orders.customer_id',
                DB::raw('COUNT(vendor_orders.id) as orders_count'),
                DB::raw('SUM(vendor_orders.total) as total_spent'),
                DB::raw('MAX(vendor_orders.created_at) as last_order_at')
            )
            ->groupBy('orders.customer_id');

        $customersQuery = User::query()
            ->joinSub($customerStats, 'stats', function ($join) {
                $join->on('users.id', '=', 'stats.customer_id');
            })
            ->select('users.*', 'stats.orders_count', 'stats.total_spent', 'stats.last_order_at')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhere('users.phone', 'like', "%{$search}%");
                });
            });

        match ($sort) {
            'spent_asc' => $customersQuery->orderBy('stats.total_spent', 'asc'),
            'orders_desc' => $customersQuery->orderBy('stats.orders_count', 'desc'),
            'orders_asc' => $customersQuery->orderBy('stats.orders_count', 'asc'),
            'recent' => $customersQuery->orderBy('stats.last_order_at', 'desc'),
            'name_asc' => $customersQuery->orderBy('users.name', 'asc'),
            default => $customersQuery->orderBy('stats.total_spent', 'desc'),
        };

        $customers = $customersQuery->paginate(15)->withQueryString();

        $totalCustomers = VendorOrder::query()
            ->where('vendor_id', $sellerId)
            ->join('orders', 'vendor_orders.order_id', '=', 'orders.id')
            ->distinct('orders.customer_id')
            ->count('orders.customer_id');

        $totalRevenue = VendorOrder::query()
            ->where('vendor_id', $sellerId)
            ->sum('total');

        $totalOrdersCount = VendorOrder::query()->where('vendor_id', $sellerId)->count();
        $avgOrderValue = $totalOrdersCount > 0 ? ($totalRevenue / $totalOrdersCount) : 0;

        return view('seller.customers.index', [
            'customers' => $customers,
            'search' => $search,
            'sort' => $sort,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'avgOrderValue' => $avgOrderValue,
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }

    public function show(User $customer): View
    {
        $sellerId = Auth::id();

        $vendorOrders = VendorOrder::query()
            ->where('vendor_id', $sellerId)
            ->whereHas('order', fn ($q) => $q->where('customer_id', $customer->id))
            ->with(['order.shippingAddress', 'items', 'invoice'])
            ->latest()
            ->get();

        abort_if($vendorOrders->isEmpty(), 404, 'Customer has no purchase history with your store.');

        $totalSpent = $vendorOrders->sum('total');
        $ordersCount = $vendorOrders->count();
        $firstOrderAt = $vendorOrders->last()?->created_at;
        $latestOrderAt = $vendorOrders->first()?->created_at;

        return view('seller.customers.show', [
            'customer' => $customer,
            'vendorOrders' => $vendorOrders,
            'totalSpent' => $totalSpent,
            'ordersCount' => $ordersCount,
            'firstOrderAt' => $firstOrderAt,
            'latestOrderAt' => $latestOrderAt,
            'routePrefix' => Auth::user()->isBusiness() ? 'business.' : 'saler.',
        ]);
    }
}
