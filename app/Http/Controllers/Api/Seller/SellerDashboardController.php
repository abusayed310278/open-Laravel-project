<?php

namespace App\Http\Controllers\Api\Seller;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorOrder;
use App\Support\MediaUrl;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    /**
     * Provide comprehensive dashboard analytics and recent activity for the seller app.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $seller */
        $seller = $request->user();
        $now = now();
        $days = (int) $request->input('days', 30);
        if ($days <= 0 || $days > 90) {
            $days = 30;
        }

        $startDate = now()->subDays($days - 1)->startOfDay();
        $prevStartDate = now()->subDays(($days * 2) - 1)->startOfDay();
        $prevEndDate = now()->subDays($days)->endOfDay();

        // 1. Core Metrics
        $vendorOrdersQuery = VendorOrder::query()->where('vendor_id', $seller->id);

        $totalRevenue = (float) (clone $vendorOrdersQuery)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->sum('total');

        $revenueCurrentPeriod = (float) (clone $vendorOrdersQuery)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereBetween('created_at', [$startDate, $now])
            ->sum('total');

        $revenuePrevPeriod = (float) (clone $vendorOrdersQuery)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total');

        $revenueChange = $revenuePrevPeriod > 0
            ? round((($revenueCurrentPeriod - $revenuePrevPeriod) / $revenuePrevPeriod) * 100, 1)
            : ($revenueCurrentPeriod > 0 ? 100.0 : 0.0);

        $totalOrders = (clone $vendorOrdersQuery)->count();
        $ordersCurrentPeriod = (clone $vendorOrdersQuery)->whereBetween('created_at', [$startDate, $now])->count();
        $ordersPrevPeriod = (clone $vendorOrdersQuery)->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();

        $ordersChange = $ordersPrevPeriod > 0
            ? round((($ordersCurrentPeriod - $ordersPrevPeriod) / $ordersPrevPeriod) * 100, 1)
            : ($ordersCurrentPeriod > 0 ? 100.0 : 0.0);

        $activeProductsCount = $seller->products()->where('status', 'active')->count();
        $totalProductsCount = $seller->products()->count();
        $totalViews = (int) $seller->products()->sum('views_count');

        $subscription = $seller->activeSubscription;
        $listingCredit = (int) ($subscription?->credits ?? 0);

        // 2. Daily Sales & Orders Chart Data
        $period = CarbonPeriod::create($startDate, '1 day', $now);
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
            ->whereBetween('created_at', [$startDate, $now])
            ->selectRaw('DATE(created_at) as date, SUM(total) as revenue_total, COUNT(*) as orders_count')
            ->groupBy('date')
            ->get();

        foreach ($dailySalesQuery as $row) {
            if (isset($revenueMap[$row->date])) {
                $revenueMap[$row->date] = (float) $row->revenue_total;
                $ordersMap[$row->date] = (int) $row->orders_count;
            }
        }

        // 3. Top Products for Seller
        $topProductsRaw = OrderItem::query()
            ->whereHas('vendorOrder', fn ($q) => $q->where('vendor_id', $seller->id))
            ->select('product_id', 'product_title')
            ->selectRaw('SUM(quantity) as qty, SUM(total_price) as rev')
            ->groupBy('product_id', 'product_title')
            ->orderByDesc('rev')
            ->limit(5)
            ->get();

        $topProductIds = $topProductsRaw->pluck('product_id')->filter()->all();
        $productsMap = Product::with('images')->whereIn('id', $topProductIds)->get()->keyBy('id');

        $topProducts = $topProductsRaw->map(function ($item) use ($productsMap) {
            $product = $productsMap->get($item->product_id);
            $thumbnail = null;
            if ($product && $product->images->isNotEmpty()) {
                $thumbnail = MediaUrl::resolve($product->images->first()->image_path);
            }

            return [
                'product_id' => $item->product_id,
                'title' => $item->product_title,
                'thumbnail' => $thumbnail,
                'units_sold' => (int) $item->qty,
                'revenue' => (float) $item->rev,
                'currency' => 'QAR',
            ];
        });

        // 4. Recent Vendor Orders
        $recentOrders = VendorOrder::query()
            ->where('vendor_id', $seller->id)
            ->with(['order.customer.profile', 'items'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (VendorOrder $vendorOrder) {
                return [
                    'id' => $vendorOrder->id,
                    'order_number' => $vendorOrder->order?->order_number ?? '#' . $vendorOrder->id,
                    'customer_name' => $vendorOrder->order?->customer?->name ?? 'Customer',
                    'customer_email' => $vendorOrder->order?->customer?->email,
                    'status' => $vendorOrder->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $vendorOrder->status)),
                    'total' => (float) $vendorOrder->total,
                    'currency' => 'QAR',
                    'items_count' => $vendorOrder->items->count(),
                    'created_at' => $vendorOrder->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'period_revenue' => $revenueCurrentPeriod,
                'revenue_change_pct' => $revenueChange,
                'total_orders' => $totalOrders,
                'period_orders' => $ordersCurrentPeriod,
                'orders_change_pct' => $ordersChange,
                'active_products' => $activeProductsCount,
                'total_products' => $totalProductsCount,
                'total_views' => $totalViews,
                'listing_credits' => $listingCredit,
                'currency' => 'QAR',
            ],
            'chart' => [
                'labels' => $chartLabels,
                'revenue' => array_values($revenueMap),
                'orders' => array_values($ordersMap),
            ],
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
            'subscription' => $subscription ? [
                'plan_name' => $subscription->plan?->name ?? 'Standard',
                'status' => $subscription->status?->value ?? 'active',
                'credits' => $subscription->credits,
            ] : null,
        ]);
    }
}
