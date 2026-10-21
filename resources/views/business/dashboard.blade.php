@extends('layouts.business')

@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $icon = \App\Support\Icons::PATHS;
        $firstName = \Illuminate\Support\Str::title(\Illuminate\Support\Str::of($user->businessProfile?->business_name ?? $user->name)->before(' ')->toString());
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
            ['key' => 'revenue', 'label' => 'Revenue', 'value' => $revenueTotal, 'format' => 'currency', 'route' => 'business.reports.index', 'icon' => $icon['banknotes'], 'tint' => 'bg-emerald-50 text-emerald-500', 'stroke' => '#10b981', 'fill' => '#d1fae5'],
            ['key' => 'orders', 'label' => 'Orders', 'value' => $ordersCount, 'route' => 'business.orders.index', 'icon' => $icon['shopping-bag'], 'tint' => 'bg-blue-50 text-blue-500', 'stroke' => '#3b82f6', 'fill' => '#dbeafe'],
            ['key' => 'products', 'label' => 'Products', 'value' => $productsCount, 'route' => 'business.products.index', 'icon' => $icon['box'], 'tint' => 'bg-violet-50 text-violet-500', 'stroke' => '#8b5cf6', 'fill' => '#ede9fe'],
            ['key' => 'reviews', 'label' => 'Reviews', 'value' => $reviewsCount, 'route' => 'business.reviews.index', 'icon' => $icon['star'], 'tint' => 'bg-orange-50 text-orange-500', 'stroke' => '#f97316', 'fill' => '#ffedd5'],
        ];

        // Revenue overview (SVG area chart)
        $totalRevenue7d = collect($revenueSeries)->sum('amount');
        $maxRevenue = max(collect($revenueSeries)->max('amount'), 1);
        $niceMax = max(10, (int) (ceil($maxRevenue / 10) * 10));
        $chartPoints = [];
        foreach ($revenueSeries as $i => $day) {
            $chartPoints[] = [round(30 + $i * (350 / (count($revenueSeries) - 1)), 1), round(130 - ($day['amount'] / $niceMax) * 110, 1)];
        }
        $chartLine = collect($chartPoints)->map(fn ($p) => $p[0].','.$p[1])->implode(' ');
        $chartArea = '30,130 '.$chartLine.' 380,130';

        // Top products (SVG donut) — brand accent first, neutral slate scale after
        $segmentColors = ['var(--color-brand-primary, #f59e0b)', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0'];
        $radius = 40;
        $circumference = 2 * M_PI * $radius;
        $offset = 0;

        // Store setup checklist
        $doneSteps = collect($storeSteps)->where('done', true)->count();
        $completion = (int) round($doneSteps / count($storeSteps) * 100);

        $quickActions = [
            ['label' => 'Add Product', 'route' => 'business.products.create', 'icon' => $icon['box']],
            ['label' => 'Orders', 'route' => 'business.orders.index', 'icon' => $icon['shopping-bag']],
            ['label' => 'Wallet & Payouts', 'route' => 'business.payouts.index', 'icon' => $icon['wallet'], 'permission' => 'payments.manage'],
            ['label' => 'Subscription', 'route' => 'business.subscription.index', 'icon' => $icon['credit-card'], 'permission' => 'subscriptions.manage'],
            ['label' => 'Refunds', 'route' => 'business.refunds.index', 'icon' => $icon['undo']],
            ['label' => 'Messages', 'route' => 'business.messages.index', 'icon' => $icon['chat']],
            ['label' => 'Get Support', 'route' => 'business.support.index', 'icon' => $icon['support']],
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
                    <p class="text-xs font-bold text-blue-900 uppercase tracking-wider mb-1.5">{{ $greeting }}</p>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ $firstName }} <span aria-hidden="true">👋</span></h1>
                    <p class="text-sm text-gray-600 mt-2 max-w-md">Manage your products, orders, and store performance all in one place.</p>
                </div>
                <div class="relative z-10 flex items-center gap-2 shrink-0">
                    <a href="{{ route('business.products.create') }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-brand-500 hover:bg-brand-600 px-3.5 py-2.5 text-xs font-bold text-gray-950 shadow-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        Add Product
                    </a>
                    <a href="{{ route('business.store.edit') }}" class="hidden sm:inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-brand-200 bg-white/80 backdrop-blur px-3.5 py-2.5 text-xs font-bold text-gray-900 hover:bg-white transition">
                        Store Profile
                    </a>
                </div>
            </div>

            {{-- Dedicated Subscription Status Section --}}
            @if (user_can('subscriptions.manage'))
            <div class="bg-white rounded-2xl p-5 shadow-xs border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5 min-w-0">
                    <span class="w-11 h-11 rounded-xl bg-brand-50 border border-brand-200/60 text-brand-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icon['credit-card'] }}" /></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-sm font-bold text-gray-900">Subscription & Seller Tier</h2>
                            @if (!empty($activeSubscription) && $activeSubscription->isActive())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inactive / Free Tier
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            @if (!empty($activeSubscription) && $activeSubscription->isActive())
                                Current plan: <span class="font-semibold text-gray-800">{{ $activeSubscription->plan?->name }}</span>
                                @if ($activeSubscription->ends_at)
                                    · Valid until {{ $activeSubscription->ends_at->format('M j, Y') }}
                                @endif
                                @if ($activeSubscription->credits)
                                    · {{ $activeSubscription->credits->remaining_listings ?? 0 }} listing credits remaining
                                @endif
                            @else
                                Subscribe to a seller plan to increase your listing limit, verification priority, and marketplace benefits.
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('business.subscription.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-500 hover:bg-brand-600 text-gray-950 text-xs font-bold transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['credit-card'] }}" /></svg>
                        {{ (!empty($activeSubscription) && $activeSubscription->isActive()) ? 'Manage Subscription' : 'Upgrade Plan' }}
                    </a>
                </div>
            </div>
            @endif

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($stats as $stat)
                    @php
                        $trend = $trends[$stat['key']];
                        [$line, $area] = $spark($trend['series']);
                        $display = ($stat['format'] ?? null) === 'currency' ? '$'.number_format($stat['value'], 2) : number_format($stat['value']);
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
                        <p class="text-2xl font-extrabold text-gray-900 leading-tight mt-3">{{ $display }}</p>
                        <p class="text-xs font-medium text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
                        <svg viewBox="0 0 100 28" class="w-full h-7 mt-3" preserveAspectRatio="none" aria-hidden="true">
                            <polygon points="{{ $area }}" fill="{{ $stat['fill'] }}" opacity="0.6" />
                            <polyline points="{{ $line }}" fill="none" stroke="{{ $stat['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                        </svg>
                        <p class="text-[10px] text-gray-400 mt-1">vs. last 7 days</p>
                    </a>
                @endforeach
            </div>

            {{-- Recent orders --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon['shopping-bag'] }}" /></svg>
                        Recent Orders
                    </h2>
                    <a href="{{ route('business.orders.index') }}" class="text-xs font-semibold text-gray-700 hover:text-gray-950 inline-flex items-center gap-1">View All Orders <span aria-hidden="true">→</span></a>
                </div>

                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl border border-gray-100">
                        <table class="w-full text-sm min-w-[560px]">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500 bg-gray-50/80">
                                    <th class="px-4 py-3">Order ID</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3">Items</th>
                                    <th class="px-4 py-3">Total</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($recentOrders as $order)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-900">
                                            {{ $order->vendor_order_number }}
                                            <span class="block text-xs text-gray-400 font-normal">{{ $order->created_at->format('M j, Y') }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $order->order?->customer?->name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $order->items_count }} {{ \Illuminate\Support\Str::plural('item', $order->items_count) }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-900">${{ number_format($order->total, 2) }}</td>
                                        <td class="px-4 py-3"><x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge></td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('business.orders.show', $order) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-800 hover:bg-gray-50 transition">View Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-10 text-center">
                        <p class="text-sm text-gray-400">No orders yet.</p>
                        <a href="{{ route('business.products.create') }}" class="mt-3 inline-flex brand-btn-primary text-xs font-bold px-4 py-2 rounded-lg">List a product</a>
                    </div>
                @endif
            </div>

            {{-- Revenue / top products / quick actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-bold text-gray-900">Revenue Overview</h2>
                        <span class="text-[11px] text-gray-500 border border-gray-200 rounded-md px-2 py-1">Last 7 Days</span>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900 mb-2">${{ number_format($totalRevenue7d, 2) }}</p>
                    <svg viewBox="0 0 400 160" class="w-full h-auto" role="img" aria-label="Revenue over the last 7 days">
                        @foreach ([0, 0.5, 1] as $tick)
                            @php $y = 130 - $tick * 110; @endphp
                            <line x1="30" y1="{{ $y }}" x2="380" y2="{{ $y }}" stroke="#f1f5f9" stroke-width="1" />
                            <text x="24" y="{{ $y + 3 }}" text-anchor="end" font-size="9" fill="#9ca3af">${{ number_format($niceMax * $tick) }}</text>
                        @endforeach
                        <polygon points="{{ $chartArea }}" style="fill: var(--color-brand-100, #fef3c7)" opacity="0.7" />
                        <polyline points="{{ $chartLine }}" fill="none" style="stroke: var(--color-brand-primary, #f59e0b)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($chartPoints as $i => $point)
                            <circle cx="{{ $point[0] }}" cy="{{ $point[1] }}" r="3" fill="#fff" style="stroke: var(--color-brand-primary, #f59e0b)" stroke-width="2"><title>{{ $revenueSeries[$i]['label'] }}: ${{ number_format($revenueSeries[$i]['amount'], 2) }}</title></circle>
                            <text x="{{ $point[0] }}" y="150" text-anchor="middle" font-size="9" fill="#9ca3af">{{ $revenueSeries[$i]['label'] }}</text>
                        @endforeach
                    </svg>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Top Products</h2>
                    @if (count($topProducts))
                        <div class="flex items-center gap-4">
                            <svg viewBox="0 0 100 100" class="w-28 h-28 shrink-0 -rotate-90" role="img" aria-label="Units sold by product">
                                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="#f1f5f9" stroke-width="14" />
                                @foreach ($topProducts as $i => $product)
                                    @php
                                        $length = $circumference * $product['percent'] / 100;
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
                                @foreach ($topProducts as $i => $product)
                                    <li class="flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $segmentColors[$i] ?? '#9ca3af' }}"></span>
                                        <span class="truncate text-gray-700">{{ $product['name'] }}</span>
                                        <span class="ml-auto font-semibold text-gray-900">{{ $product['percent'] }}%</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 py-8 text-center">Your top sellers will appear after your first order.</p>
                    @endif
                </div>

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
                        @php $logo = $user->businessProfile?->logoUrl(); @endphp
                        @if ($logo)
                            <img src="{{ $logo }}" alt="{{ $user->businessProfile?->business_name }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                        @else
                            <div class="w-14 h-14 rounded-xl bg-brand-100 text-brand-700 flex items-center justify-center text-xl font-extrabold shrink-0">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->businessProfile?->business_name ?? $user->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">Your Store</p>
                            <p class="text-base font-bold text-gray-900 truncate">{{ $user->businessProfile?->business_name ?? $user->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    @if ($verification?->status === \App\Enums\KycStatus::Approved)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-semibold px-2 py-0.5 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-600 text-[11px] font-semibold px-2 py-0.5 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $verification?->status?->label() ?? 'Unverified' }}
                        </span>
                    @endif
                </div>

                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-700 mb-1.5">
                        <span>Store Setup</span>
                        <span>{{ $completion }}%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ $completion }}%"></div>
                    </div>
                </div>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($storeSteps as $step)
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

            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <h2 class="text-sm font-bold text-gray-900 mb-3">Wallet</h2>
                <p class="text-xs text-gray-500">Available Balance</p>
                <p class="text-2xl font-black text-emerald-600 mt-0.5">${{ number_format((float) ($wallet->available_balance ?? 0), 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">${{ number_format((float) ($wallet->pending_balance ?? 0), 2) }} pending release</p>
                @if (user_can('payments.manage'))
                <a href="{{ route('business.payouts.index') }}" class="mt-4 inline-flex items-center gap-1.5 brand-btn-primary text-xs font-bold px-4 py-2.5 rounded-lg w-full justify-center">
                    Request Payout
                </a>
                @endif
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-gradient-to-br from-brand-50 to-white p-5">
                <p class="text-xs font-semibold text-blue-900">Reach More Buyers</p>
                <h3 class="mt-1 text-lg font-extrabold text-gray-900 leading-snug">List more, sell more</h3>
                <p class="mt-2 text-xs text-gray-600 max-w-[200px]">Active listings with complete details and photos convert best.</p>
                <a href="{{ route('business.products.create') }}" class="mt-4 inline-flex items-center gap-1.5 brand-btn-primary text-xs font-bold px-4 py-2.5 rounded-lg">
                    Add Product
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" /></svg>
                </a>
                <svg class="absolute -right-3 -bottom-4 h-28 w-28 text-brand-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4h10l3 4v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8l3-4zm0 5a5 5 0 0010 0h-2a3 3 0 01-6 0H7z" /></svg>
            </div>
        </aside>
    </div>
@endsection
