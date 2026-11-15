<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CommissionRecord;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Models\User;
use App\Models\UserVerification;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $cachedMetrics = Cache::remember('admin_dashboard_metrics', 300, function () {
            $now = now();
            $startOf30Days = now()->subDays(29)->startOfDay();
            $startOfPrevious30Days = now()->subDays(59)->startOfDay();
            $endOfPrevious30Days = now()->subDays(30)->endOfDay();

            // 1. Key Metrics
            $totalRevenue = (float) Order::whereNotIn('status', ['cancelled', 'failed'])->sum('total');
            $revenueLast30Days = (float) Order::whereNotIn('status', ['cancelled', 'failed'])
                ->whereBetween('created_at', [$startOf30Days, $now])
                ->sum('total');
            $revenuePrev30Days = (float) Order::whereNotIn('status', ['cancelled', 'failed'])
                ->whereBetween('created_at', [$startOfPrevious30Days, $endOfPrevious30Days])
                ->sum('total');
            $revenueChange = $revenuePrev30Days > 0
                ? round((($revenueLast30Days - $revenuePrev30Days) / $revenuePrev30Days) * 100, 1)
                : ($revenueLast30Days > 0 ? 100 : 0);

            $totalOrders = Order::count();
            $ordersLast30Days = Order::whereBetween('created_at', [$startOf30Days, $now])->count();
            $ordersPrev30Days = Order::whereBetween('created_at', [$startOfPrevious30Days, $endOfPrevious30Days])->count();
            $ordersChange = $ordersPrev30Days > 0
                ? round((($ordersLast30Days - $ordersPrev30Days) / $ordersPrev30Days) * 100, 1)
                : ($ordersLast30Days > 0 ? 100 : 0);

            $activeProductsCount = Product::where('status', 'active')->count();
            $totalProductsCount = Product::count();

            $pendingKycUsers = UserVerification::whereIn('status', [KycStatus::Submitted, KycStatus::UnderReview])->count();
            $approvedKycUsers = UserVerification::where('status', KycStatus::Approved)->count();
            $rejectedKycUsers = UserVerification::where('status', KycStatus::Rejected)->count();
            $totalKycApplications = UserVerification::count();
            $pendingProductVerifications = ProductVerification::where('status', 'pending')->count();
            $totalPendingVerifications = $pendingKycUsers + $pendingProductVerifications;

            $totalCommission = (float) CommissionRecord::sum('commission_amount');
            $totalUsers = User::count();

            // 2. Role Counts Breakdown
            $roleCounts = [
                'customer' => User::where('role', UserRole::Customer)->count(),
                'saler' => User::where('role', UserRole::Saler)->count(),
                'business' => User::where('role', UserRole::Business)->count(),
                'verifier' => User::where('role', UserRole::Verifier)->count(),
                'admin' => User::where('role', UserRole::Admin)->count(),
            ];

            // 7. Top Selling Products
            $topProducts = OrderItem::query()
                ->select('product_title')
                ->selectRaw('SUM(quantity) as qty, SUM(total_price) as rev')
                ->groupBy('product_title')
                ->orderByDesc('rev')
                ->limit(5)
                ->get()
                ->map(fn ($item) => [
                    'product_title' => (string) $item->product_title,
                    'qty' => (int) $item->qty,
                    'rev' => (float) $item->rev,
                ])
                ->all();

            return [
                'metrics' => [
                    'totalRevenue' => $totalRevenue,
                    'revenueLast30Days' => $revenueLast30Days,
                    'revenueChange' => $revenueChange,
                    'totalOrders' => $totalOrders,
                    'ordersLast30Days' => $ordersLast30Days,
                    'ordersChange' => $ordersChange,
                    'activeProducts' => $activeProductsCount,
                    'totalProducts' => $totalProductsCount,
                    'pendingVerifications' => $totalPendingVerifications,
                    'pendingKycUsers' => $pendingKycUsers,
                    'approvedKycUsers' => $approvedKycUsers,
                    'rejectedKycUsers' => $rejectedKycUsers,
                    'totalKycApplications' => $totalKycApplications,
                    'pendingProducts' => $pendingProductVerifications,
                    'totalCommission' => $totalCommission,
                    'totalUsers' => $totalUsers,
                ],
                'roleCounts' => $roleCounts,
                'topProducts' => $topProducts,
            ];
        });

        // 3. 30-Day Sales & Orders Chart Data + Category Distribution
        $cachedChartData = Cache::remember('admin_dashboard_chart', 600, function () {
            $now = now();
            $startOf30Days = now()->subDays(29)->startOfDay();

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

            $dailySalesQuery = Order::query()
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

            $topCategories = Category::query()
                ->withCount('products')
                ->orderByDesc('products_count')
                ->limit(5)
                ->get();

            return [
                'chartLabels' => $chartLabels,
                'chartRevenue' => array_values($revenueMap),
                'chartOrders' => array_values($ordersMap),
                'categoryLabels' => $topCategories->pluck('name')->all(),
                'categoryData' => $topCategories->pluck('products_count')->all(),
            ];
        });

        // 5. Recent Orders (Latest 5 - Live dynamic)
        $recentOrders = Order::query()
            ->with(['user.profile'])
            ->latest()
            ->limit(5)
            ->get();

        // 6. Recent Pending KYC / Verifications (Latest 5 - Live dynamic)
        $recentKycList = UserVerification::query()
            ->with(['user.profile'])
            ->whereIn('status', [KycStatus::Submitted, KycStatus::UnderReview])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $cachedMetrics['metrics'],
            'roleCounts' => $cachedMetrics['roleCounts'],
            'chartLabels' => $cachedChartData['chartLabels'],
            'chartRevenue' => $cachedChartData['chartRevenue'],
            'chartOrders' => $cachedChartData['chartOrders'],
            'categoryLabels' => $cachedChartData['categoryLabels'],
            'categoryData' => $cachedChartData['categoryData'],
            'recentOrders' => $recentOrders,
            'recentKycList' => $recentKycList,
            'topProducts' => collect($cachedMetrics['topProducts'])->map(fn ($item) => (object) $item),
        ]);
    }
}
