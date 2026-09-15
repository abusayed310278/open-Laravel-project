<?php

namespace App\Services;

use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\CommissionRecord;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Models\SellerPayout;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\VendorOrder;
use App\Models\WarehouseProduct;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * @return array{
     *     totalRevenue: float, totalOrders: int, totalCommission: float, activeSellers: int,
     *     dailySales: array<int, array{date: string, total: float}>,
     *     topProducts: array<int, array{title: string, quantity: int, revenue: float}>,
     *     topSellers: array<int, array{name: string, orders: int, revenue: float}>,
     *     statusBreakdown: array<int, array{status: string, label: string, color: string, count: int}>,
     * }
     */
    public function summary(Carbon $from, Carbon $to): array
    {
        $orders = Order::query()->whereBetween('created_at', [$from, $to->copy()->endOfDay()]);

        $totalRevenue = (float) (clone $orders)->whereNotIn('status', ['cancelled', 'failed'])->sum('total');
        $totalOrders = (clone $orders)->count();

        $totalCommission = (float) CommissionRecord::query()
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('commission_amount');

        $activeSellers = VendorOrder::query()
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->distinct()
            ->count('vendor_id');

        $dailySales = (clone $orders)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'total' => (float) $row->total])
            ->all();

        $topProducts = OrderItem::query()
            ->whereBetween('order_items.created_at', [$from, $to->copy()->endOfDay()])
            ->select('product_title')
            ->selectRaw('SUM(quantity) as quantity, SUM(total_price) as revenue')
            ->groupBy('product_title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['title' => $row->product_title, 'quantity' => (int) $row->quantity, 'revenue' => (float) $row->revenue])
            ->all();

        $topSellers = VendorOrder::query()
            ->whereBetween('vendor_orders.created_at', [$from, $to->copy()->endOfDay()])
            ->join('users', 'users.id', '=', 'vendor_orders.vendor_id')
            ->select('users.name')
            ->selectRaw('COUNT(*) as orders, SUM(vendor_orders.total) as revenue')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['name' => $row->name, 'orders' => (int) $row->orders, 'revenue' => (float) $row->revenue])
            ->all();

        $statusBreakdown = (clone $orders)
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(function ($row) {
                $status = $row->status instanceof OrderStatus ? $row->status : OrderStatus::from($row->status);

                return ['status' => $status->value, 'label' => $status->label(), 'color' => $status->badgeColor(), 'count' => (int) $row->count];
            })
            ->all();

        return compact('totalRevenue', 'totalOrders', 'totalCommission', 'activeSellers', 'dailySales', 'topProducts', 'topSellers', 'statusBreakdown');
    }

    /**
     * @return array{
     *     totalRevenue: float, totalOrders: int, uniqueCustomers: int,
     *     dailySales: array<int, array{date: string, total: float}>,
     *     topProducts: array<int, array{title: string, quantity: int, revenue: float}>,
     *     activeListings: int, totalViews: int,
     * }
     */
    public function sellerSummary(User $seller, Carbon $from, Carbon $to): array
    {
        $vendorOrders = VendorOrder::query()
            ->where('vendor_id', $seller->id)
            ->whereBetween('vendor_orders.created_at', [$from, $to->copy()->endOfDay()]);

        $totalRevenue = (float) (clone $vendorOrders)->whereNotIn('status', ['cancelled', 'failed'])->sum('total');
        $totalOrders = (clone $vendorOrders)->count();

        $uniqueCustomers = (clone $vendorOrders)
            ->join('orders', 'orders.id', '=', 'vendor_orders.order_id')
            ->distinct()
            ->count('orders.customer_id');

        $dailySales = (clone $vendorOrders)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->selectRaw('DATE(vendor_orders.created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'total' => (float) $row->total])
            ->all();

        $topProducts = OrderItem::query()
            ->whereHas('vendorOrder', fn ($q) => $q->where('vendor_id', $seller->id))
            ->whereBetween('order_items.created_at', [$from, $to->copy()->endOfDay()])
            ->select('product_title')
            ->selectRaw('SUM(quantity) as quantity, SUM(total_price) as revenue')
            ->groupBy('product_title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['title' => $row->product_title, 'quantity' => (int) $row->quantity, 'revenue' => (float) $row->revenue])
            ->all();

        $activeListings = $seller->products()->where('publication_status', 'published')->count();
        $totalViews = (int) $seller->products()->sum('views_count');

        return compact('totalRevenue', 'totalOrders', 'uniqueCustomers', 'dailySales', 'topProducts', 'activeListings', 'totalViews');
    }

    /**
     * @return array{total: int, byRole: array<int, array{role: string, count: int}>, byKycStatus: array<int, array{status: string, count: int}>}
     */
    public function userReport(Carbon $from, Carbon $to): array
    {
        $users = User::query()->whereBetween('created_at', [$from, $to->copy()->endOfDay()]);

        $byRole = (clone $users)->select('role')->selectRaw('COUNT(*) as count')->groupBy('role')
            ->get()->map(fn ($row) => ['role' => ($row->role instanceof UserRole ? $row->role : UserRole::from($row->role))->label(), 'count' => (int) $row->count])->all();

        $byKycStatus = UserVerification::query()
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->select('status')->selectRaw('COUNT(*) as count')->groupBy('status')
            ->get()->map(fn ($row) => ['status' => ($row->status instanceof KycStatus ? $row->status : KycStatus::from($row->status))->label(), 'count' => (int) $row->count])->all();

        return ['total' => (clone $users)->count(), 'byRole' => $byRole, 'byKycStatus' => $byKycStatus];
    }

    /**
     * @return array{total: int, published: int, verified: int, warehoused: int, byStatus: array<int, array{status: string, count: int}>}
     */
    public function productReport(): array
    {
        return [
            'total' => Product::query()->count(),
            'published' => Product::query()->where('publication_status', 'published')->count(),
            'verified' => Product::query()->where('verification_status', 'verified')->count(),
            'warehoused' => Product::query()->where('warehouse_status', 'stored')->count(),
            'byStatus' => Product::query()->select('status')->selectRaw('COUNT(*) as count')->groupBy('status')
                ->get()->map(fn ($row) => ['status' => $row->status->label(), 'count' => (int) $row->count])->all(),
        ];
    }

    /**
     * @return array{active: int, expired: int, cancelled: int, revenue: float}
     */
    public function subscriptionReport(Carbon $from, Carbon $to): array
    {
        return [
            'active' => Subscription::query()->where('status', 'active')->count(),
            'expired' => Subscription::query()->where('status', 'expired')->count(),
            'cancelled' => Subscription::query()->where('status', 'cancelled')->count(),
            'revenue' => (float) Subscription::query()
                ->join('subscription_plans', 'subscription_plans.id', '=', 'subscriptions.plan_id')
                ->whereBetween('subscriptions.created_at', [$from, $to->copy()->endOfDay()])
                ->sum('subscription_plans.price'),
        ];
    }

    /**
     * @return array{appointments: int, passed: int, failed: int, passRate: float}
     */
    public function verificationReport(Carbon $from, Carbon $to): array
    {
        $verifications = ProductVerification::query()->whereBetween('created_at', [$from, $to->copy()->endOfDay()]);

        $total = (clone $verifications)->count();
        $passed = (clone $verifications)->where('status', 'verified')->count();
        $failed = (clone $verifications)->where('status', 'rejected')->count();
        $decided = $passed + $failed;

        return [
            'appointments' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'passRate' => $decided > 0 ? round($passed / $decided * 100, 1) : 0.0,
        ];
    }

    /**
     * @return array{stored: int, released: int, movements: int}
     */
    public function warehouseReport(Carbon $from, Carbon $to): array
    {
        return [
            'stored' => WarehouseProduct::query()->where('storage_status', 'stored')->count(),
            'released' => WarehouseProduct::query()
                ->whereBetween('released_at', [$from, $to->copy()->endOfDay()])
                ->count(),
            'movements' => InventoryMovement::query()
                ->whereIn('type', ['warehouse_in', 'warehouse_out'])
                ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
                ->count(),
        ];
    }

    /**
     * @return array{totalPaid: float, pending: int, byStatus: array<int, array{status: string, count: int, total: float}>}
     */
    public function payoutReport(Carbon $from, Carbon $to): array
    {
        $payouts = SellerPayout::query()->whereBetween('created_at', [$from, $to->copy()->endOfDay()]);

        return [
            'totalPaid' => (float) (clone $payouts)->where('status', 'completed')->sum('amount'),
            'pending' => (clone $payouts)->where('status', 'requested')->count(),
            'byStatus' => (clone $payouts)->select('status')->selectRaw('COUNT(*) as count, SUM(amount) as total')->groupBy('status')
                ->get()->map(fn ($row) => ['status' => $row->status->label(), 'count' => (int) $row->count, 'total' => (float) $row->total])->all(),
        ];
    }
}
