<?php

namespace App\Http\Controllers;

use App\Enums\KycStatus;
use App\Enums\OrderStatus;
use App\Models\ChatMessage;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\VendorOrder;
use App\Services\WalletService;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    private const CHART_DAYS = 7;

    public function businessDashboard(): View
    {
        $seller = Auth::user();
        $since = now()->subDays(self::CHART_DAYS * 2 - 1)->startOfDay();

        $vendorOrders = VendorOrder::query()
            ->where('vendor_id', $seller->id)
            ->where('created_at', '>=', $since)
            ->get(['created_at', 'total', 'status']);

        $paidOrders = $vendorOrders->reject(fn ($order) => in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Failed], true));

        $productsCreated = $seller->products()->where('created_at', '>=', $since)->pluck('created_at');
        $reviews = $seller->reviewsAsSeller()->where('created_at', '>=', $since)->pluck('created_at');
        $messages = $this->receivedMessages($seller)->where('created_at', '>=', $since)->pluck('created_at');

        return view('business.dashboard', [
            'ordersCount' => VendorOrder::query()->where('vendor_id', $seller->id)->count(),
            'revenueTotal' => (float) VendorOrder::query()->where('vendor_id', $seller->id)->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::Failed])->sum('total'),
            'productsCount' => $seller->products()->count(),
            'reviewsCount' => $seller->reviewsAsSeller()->count(),
            'unreadMessagesCount' => $this->receivedMessages($seller)->whereNull('read_at')->count(),
            'recentOrders' => VendorOrder::query()->where('vendor_id', $seller->id)->with('order.customer')->withCount('items')->latest()->limit(5)->get(),
            'trends' => [
                'revenue' => $this->trendAmount($paidOrders),
                'orders' => $this->trend($vendorOrders->pluck('created_at')),
                'products' => $this->trend($productsCreated),
                'reviews' => $this->trend($reviews),
            ],
            'revenueSeries' => $this->sumByDay($paidOrders),
            'topProducts' => $this->topProducts($seller),
            'storeSteps' => $this->storeSteps($seller),
            'activeListingsCount' => $seller->products()->where('publication_status', \App\Enums\ProductPublicationStatus::Published)->count(),
            'wallet' => app(WalletService::class)->walletFor($seller),
            'activeSubscription' => $seller->activeSubscription()->with('plan', 'credits')->first(),
            'verification' => $seller->verifications()->latest()->first(),
        ]);
    }

    /**
     * Messages sent to the business/store by customers.
     */
    private function receivedMessages(User $seller)
    {
        return ChatMessage::query()
            ->whereHas('conversation', fn ($query) => $query->where('seller_id', $seller->id))
            ->where('sender_id', '!=', $seller->id);
    }

    /**
     * Daily counts for the last 7 days (oldest first) plus the change versus the 7 days before.
     *
     * @param  Collection<int, CarbonInterface>  $timestamps
     * @return array{series: array<int, int>, change: int, total: int}
     */
    private function trend(Collection $timestamps): array
    {
        $series = [];
        for ($offset = self::CHART_DAYS - 1; $offset >= 0; $offset--) {
            $day = now()->subDays($offset)->toDateString();
            $series[] = $timestamps->filter(fn ($at) => $at->toDateString() === $day)->count();
        }

        $recent = array_sum($series);
        $previous = $timestamps->filter(fn ($at) => $at->lt(now()->subDays(self::CHART_DAYS)->startOfDay()))->count();

        $change = match (true) {
            $previous > 0 => (int) round((($recent - $previous) / $previous) * 100),
            $recent > 0 => 100,
            default => 0,
        };

        return ['series' => $series, 'change' => $change, 'total' => $recent];
    }

    /**
     * Daily revenue totals for the last 7 days (oldest first) plus the change versus the 7 days before.
     *
     * @param  Collection<int, VendorOrder>  $orders
     * @return array{series: array<int, float>, change: int, total: float}
     */
    private function trendAmount(Collection $orders): array
    {
        $series = [];
        for ($offset = self::CHART_DAYS - 1; $offset >= 0; $offset--) {
            $day = now()->subDays($offset)->toDateString();
            $series[] = (float) $orders->filter(fn ($order) => $order->created_at->toDateString() === $day)->sum('total');
        }

        $recent = array_sum($series);
        $previous = (float) $orders->filter(fn ($order) => $order->created_at->lt(now()->subDays(self::CHART_DAYS)->startOfDay()))->sum('total');

        $change = match (true) {
            $previous > 0 => (int) round((($recent - $previous) / $previous) * 100),
            $recent > 0 => 100,
            default => 0,
        };

        return ['series' => $series, 'change' => $change, 'total' => $recent];
    }

    /**
     * Revenue per day for the last 7 days, oldest first.
     *
     * @param  Collection<int, VendorOrder>  $orders
     * @return array<int, array{label: string, amount: float}>
     */
    private function sumByDay(Collection $orders): array
    {
        $days = [];
        for ($offset = self::CHART_DAYS - 1; $offset >= 0; $offset--) {
            $day = now()->subDays($offset);
            $days[] = [
                'label' => $day->format('M j'),
                'amount' => (float) $orders->filter(fn ($order) => $order->created_at->isSameDay($day))->sum('total'),
            ];
        }

        return $days;
    }

    /**
     * Share of units sold per product (top 4 plus "Others").
     *
     * @return array<int, array{name: string, count: int, percent: int}>
     */
    private function topProducts(User $seller): array
    {
        $counts = OrderItem::query()
            ->whereHas('vendorOrder', fn ($query) => $query->where('vendor_id', $seller->id))
            ->get(['product_title', 'quantity'])
            ->groupBy('product_title')
            ->map(fn ($items) => (int) $items->sum('quantity'))
            ->sortDesc();

        $total = $counts->sum();
        if ($total === 0) {
            return [];
        }

        $top = $counts->take(4);
        $others = $counts->except($top->keys()->all())->sum();
        if ($others > 0) {
            $top['Others'] = ($top['Others'] ?? 0) + $others;
        }

        return $top->map(fn ($count, $name) => [
            'name' => $name,
            'count' => $count,
            'percent' => (int) round($count / $total * 100),
        ])->values()->all();
    }

    /**
     * @return array<int, array{label: string, done: bool, route: string}>
     */
    private function storeSteps(User $seller): array
    {
        return [
            ['label' => 'List Your First Product', 'done' => $seller->products()->exists(), 'route' => 'business.products.create'],
            ['label' => 'Submit Verification', 'done' => $seller->verifications()->whereIn('status', [KycStatus::Submitted, KycStatus::UnderReview, KycStatus::Approved])->exists(), 'route' => 'business.verification.index'],
            ['label' => 'Add a Payout Account', 'done' => $seller->paymentAccounts()->exists(), 'route' => 'business.payment-settings.edit'],
            ['label' => 'Complete Store Profile', 'done' => (bool) $seller->businessProfile?->profile_completed, 'route' => 'business.store.edit'],
        ];
    }
}
