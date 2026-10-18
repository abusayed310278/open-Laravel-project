<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\ChatMessage;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    private const CHART_DAYS = 7;

    public function index(): View
    {
        $user = Auth::user();
        $since = now()->subDays(self::CHART_DAYS * 2 - 1)->startOfDay();

        $orders = $user->orders()->where('created_at', '>=', $since)->get(['created_at', 'total', 'status']);
        $wishlist = $user->wishlist?->items()->where('created_at', '>=', $since)->pluck('created_at') ?? collect();
        $reviews = $user->reviewsWritten()->where('created_at', '>=', $since)->pluck('created_at');
        $messages = $this->receivedMessages($user)->where('created_at', '>=', $since)->pluck('created_at');

        $spendingByDay = $this->sumByDay(
            $orders->reject(fn ($order) => in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Failed], true)),
        );

        return view('account.dashboard', [
            'ordersCount' => $user->orders()->count(),
            'wishlistCount' => $user->wishlist?->items()->count() ?? 0,
            'unreadMessagesCount' => $this->receivedMessages($user)->whereNull('read_at')->count(),
            'reviewsCount' => $user->reviewsWritten()->count(),
            'recentOrders' => $user->orders()->withCount('items')->latest()->limit(5)->get(),
            'trends' => [
                'orders' => $this->trend($orders->pluck('created_at')),
                'wishlist' => $this->trend($wishlist),
                'messages' => $this->trend($messages),
                'reviews' => $this->trend($reviews),
            ],
            'spending' => $spendingByDay,
            'topCategories' => $this->topCategories($user),
            'profileSteps' => $this->profileSteps($user),
            'activeListingsCount' => $user->products()->where('publication_status', 'published')->count(),
            'totalProductsCount' => $user->products()->count(),
            'salesOrdersCount' => \App\Models\VendorOrder::where('vendor_id', $user->id)->count(),
            'wallet' => app(\App\Services\WalletService::class)->walletFor($user),
        ]);
    }

    /**
     * Messages sent to the customer by sellers.
     */
    private function receivedMessages(User $user)
    {
        return ChatMessage::query()
            ->whereHas('conversation', fn ($query) => $query->where('buyer_id', $user->id))
            ->where('sender_id', '!=', $user->id);
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
     * Money spent per day for the last 7 days, oldest first.
     *
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
     * Share of purchased items per category (top 4 plus "Others").
     *
     * @return array<int, array{name: string, count: int, percent: int}>
     */
    private function topCategories(User $user): array
    {
        $counts = OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('customer_id', $user->id))
            ->with('product.category:id,name')
            ->get(['product_id', 'quantity'])
            ->groupBy(fn ($item) => $item->product?->category?->name ?? 'Others')
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
    private function profileSteps(User $user): array
    {
        return [
            ['label' => 'Basic Information', 'done' => filled($user->name) && filled($user->email), 'route' => 'account.profile.edit'],
            ['label' => 'Phone Number', 'done' => filled($user->phone), 'route' => 'account.profile.edit'],
            ['label' => 'Profile Photo', 'done' => filled($user->avatar) || filled($user->profile?->avatar), 'route' => 'account.profile.edit'],
            ['label' => 'Delivery Address', 'done' => $user->addresses()->exists(), 'route' => 'account.addresses.index'],
        ];
    }
}
