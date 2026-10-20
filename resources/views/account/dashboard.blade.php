@extends('layouts.customer')

@section('title', 'Dashboard')

@section('account-content')
    @php
        $user = auth()->user();
        $icon = \App\Support\Icons::PATHS;
        $firstName = \Illuminate\Support\Str::of($user->name)->before(' ')->toString();
        $greeting = match (true) {
            now()->hour < 12 => 'Good morning',
            now()->hour < 18 => 'Good afternoon',
            default => 'Good evening',
        };

        // Tiny inline sparkline: returns [line points, area points] for a 100x28 box.
        $spark = function (array $series): array {
            $max = max(max($series), 1);
            $step = 100 / (count($series) - 1);
            $points = [];
            foreach ($series as $i => $value) {
                $points[] = round($i * $step, 1).','.round(26 - ($value / $max) * 22, 1);
            }
            $line = implode(' ', $points);

            return [$line, '0,28 '.$line.' 100,28'];
        };

        $stats = [
            ['key' => 'orders', 'label' => 'Total Orders', 'value' => $ordersCount, 'route' => 'account.orders.index', 'icon' => $icon['shopping-bag'], 'tint' => 'bg-blue-50 text-blue-500', 'stroke' => '#3b82f6', 'fill' => '#dbeafe', 'permission' => 'orders.manage'],
            ['key' => 'wishlist', 'label' => 'Wishlist', 'value' => $wishlistCount, 'route' => 'account.wishlist.index', 'icon' => $icon['heart'], 'tint' => 'bg-emerald-50 text-emerald-500', 'stroke' => '#10b981', 'fill' => '#d1fae5'],
            ['key' => 'messages', 'label' => 'Messages', 'value' => $unreadMessagesCount, 'route' => 'account.messages.index', 'icon' => $icon['chat'], 'tint' => 'bg-violet-50 text-violet-500', 'stroke' => '#8b5cf6', 'fill' => '#ede9fe', 'permission' => 'chat.access'],
            ['key' => 'reviews', 'label' => 'Reviews', 'value' => $reviewsCount, 'route' => 'account.reviews.index', 'icon' => $icon['star'], 'tint' => 'bg-orange-50 text-orange-500', 'stroke' => '#f97316', 'fill' => '#ffedd5', 'permission' => 'reviews.manage'],
        ];

        $stats = array_values(array_filter($stats, function ($stat) {
            return empty($stat['permission']) || user_can($stat['permission']);
        }));

        // Spending overview (SVG area chart)
        $totalSpend = collect($spending)->sum('amount');
        $maxSpend = max(collect($spending)->max('amount'), 1);
        $niceMax = max(10, (int) (ceil($maxSpend / 10) * 10));
        $chartPoints = [];
        foreach ($spending as $i => $day) {
            $chartPoints[] = [round(30 + $i * (350 / (count($spending) - 1)), 1), round(130 - ($day['amount'] / $niceMax) * 110, 1)];
        }
        $chartLine = collect($chartPoints)->map(fn ($p) => $p[0].','.$p[1])->implode(' ');
        $chartArea = '30,130 '.$chartLine.' 380,130';

        // Top categories (SVG donut) — brand accent first, neutral slate scale after
        $segmentColors = ['var(--color-brand-primary, #f59e0b)', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0'];
        $radius = 40;
        $circumference = 2 * M_PI * $radius;
        $offset = 0;

        // Profile completion
        $doneSteps = collect($profileSteps)->where('done', true)->count();
        $completion = (int) round($doneSteps / count($profileSteps) * 100);

        $quickActions = [
            ['label' => 'Browse Shop', 'route' => 'shop', 'icon' => $icon['shopping-bag']],
            ['label' => 'Sell a Product', 'route' => 'saler.products.create', 'icon' => $icon['box'], 'permission' => 'products.manage'],
            ['label' => 'My Orders', 'route' => 'account.orders.index', 'icon' => $icon['clipboard'] ?? $icon['shopping-bag'], 'permission' => 'orders.manage'],
            ['label' => 'Wallet & Payouts', 'route' => 'saler.payouts.index', 'icon' => $icon['wallet'], 'permission' => 'payments.manage'],
            ['label' => 'Track Returns', 'route' => 'account.returns.index', 'icon' => $icon['undo'], 'permission' => 'orders.manage'],
            ['label' => 'Get Support', 'route' => 'account.support.index', 'icon' => $icon['support'], 'permission' => 'support.manage'],
        ];

        $quickActions = array_values(array_filter($quickActions, function ($action) {
            return empty($action['permission']) || user_can($action['permission']);
        }));
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-6">
        {{-- ============ MAIN COLUMN ============ --}}
        <div class="space-y-6 min-w-0">
            {{-- Greeting --}}
            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 via-brand-50 to-white px-6 py-6 sm:py-7 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="absolute -right-8 -top-12 w-48 h-48 rounded-full bg-brand-200/40 blur-3xl" aria-hidden="true"></div>
                <div class="absolute right-28 -bottom-14 w-36 h-36 rounded-full bg-brand-300/30 blur-3xl" aria-hidden="true"></div>

                <div class="relative z-10 max-w-xl">
                    <p class="text-xs font-bold text-brand-600 uppercase tracking-wider mb-1.5">{{ $greeting }}</p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ $firstName }} <span aria-hidden="true">👋</span></h1>
                    <p class="text-sm text-gray-600 mt-2 max-w-md">Manage your purchases, orders, and seller listings all in one place.</p>
                </div>
                <div class="relative z-10 flex items-center gap-2 shrink-0">
                    @if (user_can('products.manage'))
                    <a href="{{ route('saler.products.create') }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 px-3.5 py-2.5 text-xs font-bold text-gray-950 shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Sell Product
                    </a>
                    @endif
                    <a href="{{ route('account.profile.edit') }}" class="hidden sm:inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-brand-200 bg-white/80 backdrop-blur px-3.5 py-2.5 text-xs font-bold text-gray-900 hover:bg-white transition">
                        Profile
                    </a>
                </div>
            </div>

            {{-- Seller Hub Quick Panel --}}
            @if (user_can('products.manage') || user_can('orders.manage') || user_can('payments.manage'))
            <div class="bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800 text-white rounded-2xl p-5 shadow-sm border border-gray-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/10">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-brand-400/10 text-brand-400 text-[10px] font-extrabold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-400"></span>
                            Seller Hub
                        </div>
                        <p role="heading" aria-level="2" class="text-base font-bold text-white mt-1">Your Selling Performance</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if (user_can('products.manage'))
                        <a href="{{ route('saler.products.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-500 hover:bg-brand-400 text-gray-950 text-xs font-bold transition shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Add Listing
                        </a>
                        <a href="{{ route('saler.products.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition">
                            My Listings
                        </a>
                        @endif
                        @if (user_can('payments.manage'))
                        <a href="{{ route('saler.payouts.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold transition">
                            Wallet
                        </a>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4">
                    @if (user_can('products.manage'))
                    <a href="{{ route('saler.products.index') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-xl border border-white/5 hover:border-white/10 transition block">
                        <span class="w-7 h-7 rounded-lg bg-white/10 text-brand-400 flex items-center justify-center mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon['box'] }}" /></svg>
                        </span>
                        <p class="text-xs text-white/50 font-medium">Active Listings</p>
                        <p class="text-xl font-black text-white mt-0.5">{{ number_format($activeListingsCount) }}</p>
                    </a>
                    <a href="{{ route('saler.products.index') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-xl border border-white/5 hover:border-white/10 transition block">
                        <span class="w-7 h-7 rounded-lg bg-white/10 text-brand-400 flex items-center justify-center mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon['tag'] }}" /></svg>
                        </span>
                        <p class="text-xs text-white/50 font-medium">Total Products</p>
                        <p class="text-xl font-black text-white mt-0.5">{{ number_format($totalProductsCount) }}</p>
                    </a>
                    @endif
                    @if (user_can('orders.manage'))
                    <a href="{{ route('saler.orders.index') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-xl border border-white/5 hover:border-white/10 transition block">
                        <span class="w-7 h-7 rounded-lg bg-white/10 text-brand-400 flex items-center justify-center mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon['shopping-bag'] }}" /></svg>
                        </span>
                        <p class="text-xs text-white/50 font-medium">Sales Orders</p>
                        <p class="text-xl font-black text-white mt-0.5">{{ number_format($salesOrdersCount) }}</p>
                    </a>
                    @endif
                    @if (user_can('payments.manage'))
                    <a href="{{ route('saler.payouts.index') }}" class="p-3.5 bg-white/5 hover:bg-white/10 rounded-xl border border-white/5 hover:border-white/10 transition block">
                        <span class="w-7 h-7 rounded-lg bg-emerald-400/10 text-emerald-400 flex items-center justify-center mb-2.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon['wallet'] }}" /></svg>
                        </span>
                        <p class="text-xs text-white/50 font-medium">Available Payout</p>
                        <p class="text-xl font-black text-emerald-400 mt-0.5">${{ number_format((float) ($wallet->available_balance ?? 0), 2) }}</p>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Stat cards --}}
            @if (!empty($stats))
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($stats as $stat)
                    @php
                        $trend = $trends[$stat['key']];
                        [$line, $area] = $spark($trend['series']);
                    @endphp
                    <a href="{{ route($stat['route']) }}" class="group bg-white border border-gray-100 hover:border-brand-200 hover:shadow-md rounded-2xl p-4 transition-all duration-200">
                        <div class="flex items-start justify-between gap-2">
                            <div class="w-10 h-10 rounded-xl {{ $stat['tint'] }} flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stat['icon'] }}" /></svg>
                            </div>
                            <span @class([
                                'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-[10px] font-bold shrink-0',
                                'bg-emerald-50 text-emerald-600' => $trend['change'] > 0,
                                'bg-red-50 text-red-500' => $trend['change'] < 0,
                                'bg-gray-50 text-gray-400' => $trend['change'] === 0,
                            ])>
                                {{ $trend['change'] > 0 ? '↑' : ($trend['change'] < 0 ? '↓' : '→') }} {{ abs($trend['change']) }}%
                            </span>
                        </div>
                        <p class="text-2xl font-extrabold text-gray-900 leading-tight mt-3">{{ number_format($stat['value']) }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
                        <svg viewBox="0 0 100 28" class="w-full h-7 mt-3" preserveAspectRatio="none" aria-hidden="true">
                            <polygon points="{{ $area }}" fill="{{ $stat['fill'] }}" opacity="0.6" />
                            <polyline points="{{ $line }}" fill="none" stroke="{{ $stat['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                        <p class="text-[10px] text-gray-400 mt-1">vs. last 7 days</p>
                    </a>
                @endforeach
            </div>
            @endif

            @if (user_can('orders.manage'))
            {{-- Recent orders --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon['shopping-bag'] }}" /></svg>
                        Recent Orders
                    </h2>
                    <a href="{{ route('account.orders.index') }}" class="text-xs font-semibold text-gray-700 hover:text-gray-950 inline-flex items-center gap-1">View All Orders <span aria-hidden="true">→</span></a>
                </div>

                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm min-w-[560px]">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 bg-gray-50/80">
                                    <th class="px-4 py-3">Order ID</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Items</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentOrders as $order)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $order->order_number }}</td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $order->created_at->format('M j, Y') }}
                                            <span class="block text-xs text-gray-400">{{ $order->created_at->format('g:i A') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                        <td class="px-4 py-3"><x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge></td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('account.orders.show', $order) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-10 text-center">
                        <p class="text-sm text-gray-400">No orders yet.</p>
                        <a href="{{ route('shop') }}" class="mt-3 inline-flex brand-btn-primary text-xs font-bold px-4 py-2 rounded-lg">Start shopping</a>
                    </div>
                @endif
            </div>
            @endif

            {{-- Spending / categories / quick actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @if (user_can('orders.manage'))
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-bold text-gray-900">Spending Overview</h2>
                        <span class="text-[11px] text-gray-500 border border-gray-200 rounded-md px-2 py-1">Last 7 Days</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900 mb-2">${{ number_format($totalSpend, 2) }}</p>
                    <svg viewBox="0 0 400 160" class="w-full h-auto" role="img" aria-label="Spending over the last 7 days">
                        @foreach ([0, 0.5, 1] as $tick)
                            @php $y = 130 - $tick * 110; @endphp
                            <line x1="30" y1="{{ $y }}" x2="380" y2="{{ $y }}" stroke="#f1f5f9" stroke-width="1" />
                            <text x="24" y="{{ $y + 3 }}" text-anchor="end" font-size="9" fill="#9ca3af">${{ number_format($niceMax * $tick) }}</text>
                        @endforeach
                        <polygon points="{{ $chartArea }}" style="fill: var(--color-brand-100, #fef3c7)" opacity="0.7" />
                        <polyline points="{{ $chartLine }}" fill="none" style="stroke: var(--color-brand-primary, #f59e0b)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($chartPoints as $i => $point)
                            <circle cx="{{ $point[0] }}" cy="{{ $point[1] }}" r="3" fill="#fff" style="stroke: var(--color-brand-primary, #f59e0b)" stroke-width="2"><title>{{ $spending[$i]['label'] }}: ${{ number_format($spending[$i]['amount'], 2) }}</title></circle>
                            <text x="{{ $point[0] }}" y="150" text-anchor="middle" font-size="9" fill="#9ca3af">{{ $spending[$i]['label'] }}</text>
                        @endforeach
                    </svg>
                </div>
                @endif

                @if (user_can('orders.manage'))
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Top Categories</h2>
                    @if (count($topCategories))
                        <div class="flex items-center gap-4">
                            <svg viewBox="0 0 100 100" class="w-28 h-28 shrink-0 -rotate-90" role="img" aria-label="Purchases by category">
                                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#f1f5f9" stroke-width="14" />
                                @foreach ($topCategories as $i => $category)
                                    @php
                                        $length = $circumference * $category['percent'] / 100;
                                    @endphp
                                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none" style="stroke: {{ $segmentColors[$i] ?? '#9ca3af' }}" stroke-width="14" stroke-dasharray="{{ round($length, 2) }} {{ round($circumference - $length, 2) }}" stroke-dashoffset="{{ -round($offset, 2) }}" />
                                    @php $offset += $length; @endphp
                                @endforeach
                                <g class="rotate-90" style="transform-origin: 50% 50%">
                                    <text x="50" y="49" text-anchor="middle" font-size="16" font-weight="800" fill="#111827">{{ $ordersCount }}</text>
                                    <text x="50" y="61" text-anchor="middle" font-size="7" fill="#9ca3af">Total Orders</text>
                                </g>
                            </svg>
                            <ul class="flex-1 space-y-2 text-xs min-w-0">
                                @foreach ($topCategories as $i => $category)
                                    <li class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $segmentColors[$i] ?? '#9ca3af' }}"></span>
                                        <span class="truncate text-gray-700">{{ $category['name'] }}</span>
                                        <span class="ml-auto font-semibold text-gray-900">{{ $category['percent'] }}%</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-8 text-center">Your top categories will appear after your first order.</p>
                    @endif
                </div>
                @endif

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Quick Actions</h2>
                    <div class="grid grid-cols-2 gap-2.5">
                        @foreach ($quickActions as $action)
                            <a href="{{ route($action['route']) }}" class="flex flex-col items-center text-center gap-2 rounded-xl border border-gray-100 px-3 py-4 hover:border-brand-200 hover:bg-brand-50/40 transition">
                                <span class="w-9 h-9 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $action['icon'] }}" /></svg>
                                </span>
                                <span class="text-xs font-semibold text-gray-800 leading-tight">{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ============ SIDE COLUMN ============ --}}
        <aside class="space-y-6">
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        @php $avatar = $user->profile?->avatarUrl(); @endphp
                        @if ($avatar)
                            <img src="{{ $avatar }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                        @else
                            <div class="w-14 h-14 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-extrabold shrink-0">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Your Account</p>
                            <p class="text-base font-bold text-gray-900 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-semibold px-2 py-0.5 shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                    </span>
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-700 mb-1.5">
                        <span>Profile Completion</span>
                        <span>{{ $completion }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ $completion }}%"></div>
                    </div>
                </div>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($profileSteps as $step)
                        <li>
                            <a href="{{ route($step['route']) }}" class="flex items-center gap-2.5 text-sm {{ $step['done'] ? 'text-gray-600' : 'text-gray-900 font-medium hover:underline' }}">
                                @if ($step['done'])
                                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                @else
                                    <span class="w-5 h-5 rounded-full border-2 border-gray-300 shrink-0"></span>
                                @endif
                                {{ $step['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 to-white p-5">
                <p class="text-xs font-semibold text-brand-700">Discover More</p>
                <h3 class="mt-1 text-lg font-extrabold text-gray-900 leading-snug">Find great deals on verified electronics</h3>
                <p class="mt-2 text-xs text-gray-600 max-w-[200px]">Every item is inspected and covered by our guarantee.</p>
                <a href="{{ route('shop') }}" class="mt-4 inline-flex items-center gap-1.5 brand-btn-primary text-xs font-bold px-4 py-2.5 rounded-lg">
                    Shop Now
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" /></svg>
                </a>
                <svg class="absolute -right-3 -bottom-4 h-28 w-28 text-brand-300/60" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4h10l3 4v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8l3-4zm0 5a5 5 0 0010 0h-2a3 3 0 01-6 0H7z" /></svg>
            </div>
        </aside>
    </div>
@endsection
