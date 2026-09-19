<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\VendorOrder;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function businessDashboard(): View
    {
        return $this->renderDashboard('business.dashboard');
    }

    public function salerDashboard(): View
    {
        return $this->renderDashboard('saler.dashboard');
    }

    private function renderDashboard(string $view): View
    {
        $seller = Auth::user();
        $now = now();
        $startOf30Days = now()->subDays(29)->startOfDay();
        $startOfPrevious30Days = now()->subDays(59)->startOfDay();
        $endOfPrevious30Days = now()->subDays(30)->endOfDay();

        // 1. Seller Metrics
        $vendorOrdersQuery = VendorOrder::query()->where('vendor_id', $seller->id);

        $totalRevenue = (float) (clone $vendorOrdersQuery)->whereNotIn('status', ['cancelled', 'failed'])->sum('total');
        $revenueLast30Days = (float) (clone $vendorOrdersQuery)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereBetween('created_at', [$startOf30Days, $now])
            ->sum('total');
        $revenuePrev30Days = (float) (clone $vendorOrdersQuery)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereBetween('created_at', [$startOfPrevious30Days, $endOfPrevious30Days])
            ->sum('total');
        $revenueChange = $revenuePrev30Days > 0
            ? round((($revenueLast30Days - $revenuePrev30Days) / $revenuePrev30Days) * 100, 1)
            : ($revenueLast30Days > 0 ? 100 : 0);

        $totalOrders = (clone $vendorOrdersQuery)->count();
        $ordersLast30Days = (clone $vendorOrdersQuery)->whereBetween('created_at', [$startOf30Days, $now])->count();
        $ordersPrev30Days = (clone $vendorOrdersQuery)->whereBetween('created_at', [$startOfPrevious30Days, $endOfPrevious30Days])->count();
        $ordersChange = $ordersPrev30Days > 0
            ? round((($ordersLast30Days - $ordersPrev30Days) / $ordersPrev30Days) * 100, 1)
            : ($ordersLast30Days > 0 ? 100 : 0);

        $activeProductsCount = $seller->products()->where('status', 'active')->count();
        $totalProductsCount = $seller->products()->count();
        $totalViews = (int) $seller->products()->sum('views_count');

        $subscription = $seller->activeSubscription;
        $listingCredit = $subscription?->credits;

        // 2. 30-Day Sales & Orders Chart Data
        $period = CarbonPeriod::create($startOf30Days, '1 day', $now);
        $chartLabels = [];
        $revenueMap = [];
        $ordersMap = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M j');
            $revenueMap[$dateKey] = 0.0;
            $ordersMap[$dateKey] = 0;
        }

        $dailySalesQuery = VendorOrder::query()
            ->where('vendor_id', $seller->id)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereBetween('created_at', [$startOf30Days, $now])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue_total, COUNT(*) as orders_count')
            ->groupBy('date')
            ->get();

        foreach ($dailySalesQuery as $row) {
            if (isset($revenueMap[$row->date])) {
                $revenueMap[$row->date] = (float) $row->revenue_total;
                $ordersMap[$row->date] = (int) $row->orders_count;
            }
        }

        $chartRevenue = array_values($revenueMap);
        $chartOrders = array_values($ordersMap);

        // 3. Top Products for Seller
        $topProducts = OrderItem::query()
            ->whereHas('vendorOrder', fn ($q) => $q->where('vendor_id', $seller->id))
            ->select('product_title')
            ->selectRaw('SUM(quantity) as qty, SUM(total_price) as rev')
            ->groupBy('product_title')
            ->orderByDesc('rev')
            ->limit(5)
            ->get();

        // 4. Recent Vendor Orders
        $recentOrders = VendorOrder::query()
            ->where('vendor_id', $seller->id)
            ->with(['order.customer.profile'])
            ->latest()
            ->limit(5)
            ->get();

        return view($view, [
            'metrics' => [
                'totalRevenue' => $totalRevenue,
                'revenueLast30Days' => $revenueLast30Days,
                'revenueChange' => $revenueChange,
                'totalOrders' => $totalOrders,
                'ordersLast30Days' => $ordersLast30Days,
                'ordersChange' => $ordersChange,
                'activeProducts' => $activeProductsCount,
                'totalProducts' => $totalProductsCount,
                'totalViews' => $totalViews,
                'listingCredit' => $listingCredit,
            ],
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartOrders' => $chartOrders,
            'topProducts' => $topProducts,
            'recentOrders' => $recentOrders,
            'subscription' => $subscription,
        ]);
    }
}
