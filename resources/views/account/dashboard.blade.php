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

        // Tiny inline sparkline: returns [line points, area points] for a 100x32 box.
        $spark = function (array $series): array {
            $max = max(max($series), 1);
            $step = 100 / (count($series) - 1);
            $points = [];
            foreach ($series as $i => $value) {
                $points[] = round($i * $step, 1).','.round(30 - ($value / $max) * 26, 1);
            }
            $line = implode(' ', $points);

            return [$line, '0,32 '.$line.' 100,32'];
        };

        $stats = [
            ['key' => 'orders', 'label' => 'Total Orders', 'value' => $ordersCount, 'route' => 'account.orders.index', 'icon' => $icon['shopping-bag'], 'tint' => 'bg-blue-50 text-blue-500', 'stroke' => '#3b82f6', 'fill' => '#dbeafe'],
            ['key' => 'wishlist', 'label' => 'Wishlist', 'value' => $wishlistCount, 'route' => 'account.wishlist.index', 'icon' => $icon['heart'], 'tint' => 'bg-emerald-50 text-emerald-500', 'stroke' => '#10b981', 'fill' => '#d1fae5'],
            ['key' => 'messages', 'label' => 'Messages', 'value' => $unreadMessagesCount, 'route' => 'account.messages.index', 'icon' => $icon['chat'], 'tint' => 'bg-violet-50 text-violet-500', 'stroke' => '#8b5cf6', 'fill' => '#ede9fe'],
            ['key' => 'reviews', 'label' => 'Reviews', 'value' => $reviewsCount, 'route' => 'account.reviews.index', 'icon' => $icon['star'], 'tint' => 'bg-orange-50 text-orange-500', 'stroke' => '#f97316', 'fill' => '#ffedd5'],
        ];

        // Spending overview (SVG area chart)
        $maxSpend = max(collect($spending)->max('amount'), 1);
        $niceMax = max(10, (int) (ceil($maxSpend / 10) * 10));
        $chartPoints = [];
        foreach ($spending as $i => $day) {
            $chartPoints[] = [round(30 + $i * (350 / (count($spending) - 1)), 1), round(130 - ($day['amount'] / $niceMax) * 110, 1)];
        }
        $chartLine = collect($chartPoints)->map(fn ($p) => $p[0].','.$p[1])->implode(' ');
        $chartArea = '30,130 '.$chartLine.' 380,130';

        // Top categories (SVG donut)
        $segmentColors = ['#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#9ca3af'];
        $radius = 40;
        $circumference = 2 * M_PI * $radius;
        $offset = 0;

        // Profile completion
        $doneSteps = collect($profileSteps)->where('done', true)->count();
        $completion = (int) round($doneSteps / count($profileSteps) * 100);

        $quickActions = [
            ['label' => 'Browse Shop', 'route' => 'shop', 'icon' => $icon['shopping-bag']],
            ['label' => 'My Orders', 'route' => 'account.orders.index', 'icon' => $icon['clipboard'] ?? $icon['shopping-bag']],
            ['label' => 'Track Returns', 'route' => 'account.returns.index', 'icon' => $icon['undo']],
            ['label' => 'Get Support', 'route' => 'account.support.index', 'icon' => $icon['support']],
        ];
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_320px] gap-6">
        {{-- ============ MAIN COLUMN ============ --}}
        <div class="space-y-6 min-w-0">
            {{-- Greeting --}}
            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-brand-50 px-6 py-5 flex items-center justify-between gap-4">
                <div class="relative z-10">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">{{ $greeting }}, {{ $firstName }}! <span aria-hidden="true">👋</span></h1>
                    <p class="text-sm text-gray-600 mt-1">Here's what's happening with your account today.</p>
                </div>
                <a href="{{ route('account.profile.edit') }}" class="relative z-10 shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-brand-300 bg-white px-4 py-2 text-xs font-bold text-gray-900 hover:bg-brand-50 transition">
                    Manage Profile
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6" /></svg>
                </a>
                <svg class="hidden md:block absolute right-44 -bottom-3 h-24 w-24 text-brand-300/60" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 4h10l3 4v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8l3-4zm0 5a5 5 0 0010 0h-2a3 3 0 01-6 0H7z" /></svg>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($stats as $stat)
                    @php
                        $trend = $trends[$stat['key']];
                        [$line, $area] = $spark($trend['series']);
                    @endphp
                    <a href="{{ route($stat['route']) }}" class="group bg-white border border-gray-100 hover:border-brand-200 hover:shadow-sm rounded-2xl p-4 transition">
                        <div class="w-10 h-10 rounded-xl {{ $stat['tint'] }} flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stat['icon'] }}" /></svg>
                        </div>
                        <div class="flex items-end justify-between gap-2">
                            <div>
                                <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                                <p class="text-2xl font-extrabold text-gray-900 leading-tight">{{ number_format($stat['value']) }}</p>
                                <p @class([
                                    'text-[11px] font-semibold mt-0.5',
                                    'text-emerald-600' => $trend['change'] > 0,
                                    'text-red-500' => $trend['change'] < 0,
                                    'text-gray-400' => $trend['change'] === 0,
                                ])>
                                    {{ $trend['change'] > 0 ? '↑' : ($trend['change'] < 0 ? '↓' : '→') }} {{ abs($trend['change']) }}%
                                    <span class="font-normal text-gray-400">vs. last 7 days</span>
                                </p>
                            </div>
                            <svg viewBox="0 0 100 32" class="w-16 h-8 shrink-0" preserveAspectRatio="none" aria-hidden="true">
                                <polygon points="{{ $area }}" fill="{{ $stat['fill'] }}" opacity="0.6" />
                                <polyline points="{{ $line }}" fill="none" stroke="{{ $stat['stroke'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                            </svg>
                        </div>
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
                    <a href="{{ route('account.orders.index') }}" class="text-xs font-semibold text-gray-700 hover:text-gray-950 inline-flex items-center gap-1">View All Orders <span aria-hidden="true">→</span></a>
                </div>

                @if ($recentOrders->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl bg-gray-50/70">
                        <table class="w-full text-sm min-w-[560px]">
                            <thead>
                                <tr class="text-left text-xs font-semibold text-gray-500">
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
                                    <tr class="bg-white/60">
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

            {{-- Spending / categories / quick actions --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-bold text-gray-900">Spending Overview</h2>
                        <span class="text-[11px] text-gray-500 border border-gray-200 rounded-md px-2 py-1">Last 7 Days</span>
                    </div>
                    <svg viewBox="0 0 400 160" class="w-full h-auto" role="img" aria-label="Spending over the last 7 days">
                        @foreach ([0, 0.5, 1] as $tick)
                            @php $y = 130 - $tick * 110; @endphp
                            <line x1="30" y1="{{ $y }}" x2="380" y2="{{ $y }}" stroke="#f1f5f9" stroke-width="1" />
                            <text x="24" y="{{ $y + 3 }}" text-anchor="end" font-size="9" fill="#9ca3af">${{ number_format($niceMax * $tick) }}</text>
                        @endforeach
                        <polygon points="{{ $chartArea }}" fill="#fef3c7" opacity="0.7" />
                        <polyline points="{{ $chartLine }}" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($chartPoints as $i => $point)
                            <circle cx="{{ $point[0] }}" cy="{{ $point[1] }}" r="3" fill="#fff" stroke="#f59e0b" stroke-width="2"><title>{{ $spending[$i]['label'] }}: ${{ number_format($spending[$i]['amount'], 2) }}</title></circle>
                            <text x="{{ $point[0] }}" y="150" text-anchor="middle" font-size="9" fill="#9ca3af">{{ $spending[$i]['label'] }}</text>
                        @endforeach
                    </svg>
                </div>

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
                                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="{{ $segmentColors[$i] ?? '#9ca3af' }}" stroke-width="14" stroke-dasharray="{{ round($length, 2) }} {{ round($circumference - $length, 2) }}" stroke-dashoffset="{{ -round($offset, 2) }}" />
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

                <div class="bg-white border border-gray-100 rounded-2xl p-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Quick Actions</h2>
                    <div class="space-y-2">
                        @foreach ($quickActions as $action)
                            <a href="{{ route($action['route']) }}" class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50 transition">
                                <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $action['icon'] }}" /></svg>
                                </span>
                                <span class="flex-1">{{ $action['label'] }}</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
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

            <div class="relative overflow-hidden rounded-2xl border border-brand-100 bg-brand-50 p-5">
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
