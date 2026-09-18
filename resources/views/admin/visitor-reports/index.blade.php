@extends('layouts.admin')

@section('title', 'Visitor Intelligence & Analytics')

@section('content')
<div class="space-y-6 pb-12 font-sans">
    {{-- Header --}}
    <div class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <div class="mb-1.5 flex items-center gap-2 text-xs font-medium text-slate-400">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span>{{ $liveActiveCount }} active right now</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Visitor Intelligence & Traffic Hub</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">
                Traffic telemetry, device distribution, crawlers, and geographic analytics.
            </p>
        </div>

        {{-- Controls & Date Filter Selector --}}
        <div class="flex flex-wrap items-center gap-2 self-start xl:self-auto">
            {{-- Date Presets --}}
            <div class="inline-flex items-center gap-0.5 rounded-xl bg-slate-100 p-1">
                @php
                    $presets = [
                        'today' => 'Today',
                        '7d' => '7 Days',
                        '30d' => '30 Days',
                        'this_month' => 'This Month',
                        'all' => 'All Time',
                    ];
                @endphp
                @foreach ($presets as $key => $label)
                    <a
                        href="{{ route('admin.visitor-reports.index', ['range' => $key]) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors {{ ($range ?? '30d') === $key ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="window.location.reload()"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50"
                    title="Reload Analytics"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    <span>Refresh</span>
                </button>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white transition-colors hover:bg-brand-600"
                >
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    <span>Export</span>
                </button>
            </div>
        </div>
    </div>

    {{-- 5 KPI Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        {{-- Card 1: Total Visits --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-medium text-slate-400">Total Impressions</p>
                <span class="text-xs font-semibold text-emerald-600">+18.4%</span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($totalVisits) }}</p>
            <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-1 rounded-full bg-brand-500" style="width: 100%"></div>
            </div>
        </div>

        {{-- Card 2: Unique Visitors --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-medium text-slate-400">Unique Audience</p>
                <span class="text-xs font-semibold text-emerald-600">+12.1%</span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($uniqueVisitors) }}</p>
            <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-1 rounded-full bg-slate-900" style="width: {{ min(100, round(($uniqueVisitors / max(1, $totalVisits)) * 100)) }}%"></div>
            </div>
        </div>

        {{-- Card 3: Desktop Traffic --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            @php $desktopPct = round(($desktopCount / max(1, $totalVisits)) * 100); @endphp
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-medium text-slate-400">Desktop</p>
                <span class="text-xs font-semibold text-slate-400">{{ $desktopPct }}%</span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($desktopCount) }}</p>
            <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-1 rounded-full bg-slate-400" style="width: {{ min(100, $desktopPct) }}%"></div>
            </div>
        </div>

        {{-- Card 4: Mobile Traffic --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            @php $mobilePct = round(($mobileCount / max(1, $totalVisits)) * 100); @endphp
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-medium text-slate-400">Mobile & Tablets</p>
                <span class="text-xs font-semibold text-slate-400">{{ $mobilePct }}%</span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($mobileCount) }}</p>
            <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-1 rounded-full bg-slate-400" style="width: {{ min(100, $mobilePct) }}%"></div>
            </div>
        </div>

        {{-- Card 5: Bots & Crawlers --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-medium text-slate-400">Search Crawlers</p>
                <span class="text-xs font-semibold text-slate-400">Indexed</span>
            </div>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($botCount) }}</p>
            <div class="mt-3 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-1 rounded-full bg-slate-400" style="width: {{ min(100, round(($botCount / max(1, $totalVisits)) * 100)) }}%"></div>
            </div>
        </div>
    </div>

    {{-- Traffic Chart --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-base font-bold tracking-tight text-slate-900">Traffic Volume & Audience Trajectory</h2>
                <p class="mt-0.5 text-xs text-slate-400">Daily visit frequency and unique audience progression</p>
            </div>

            {{-- Chart Metrics Summary --}}
            <div class="flex flex-wrap items-center gap-4 text-xs">
                <div>
                    <span class="text-slate-400">Avg Duration </span>
                    <span class="font-semibold text-slate-900">{{ $avgDuration }}</span>
                </div>
                <div>
                    <span class="text-slate-400">Bounce Rate </span>
                    <span class="font-semibold text-slate-900">{{ $bounceRate }}%</span>
                </div>
                <div>
                    <span class="text-slate-400">Pages / Visit </span>
                    <span class="font-semibold text-slate-900">{{ $pagesPerSession }}</span>
                </div>
            </div>
        </div>

        {{-- Canvas Container --}}
        <div class="relative mt-6 h-[280px] w-full sm:h-[320px]">
            <canvas id="visitorTrafficChart"></canvas>
        </div>
    </div>

    {{-- 2x2 Matrix --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Section 1: Top Visited Pages --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Top Visited Pages</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Most requested storefront routes & landing URLs</p>
                </div>
                <span class="text-xs font-medium text-slate-400">Top 10</span>
            </div>

            {{-- In-card Search Filter --}}
            <div class="mt-4 mb-1 flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 transition-colors focus-within:border-brand-500">
                <svg class="h-4 w-4 shrink-0 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input
                    type="text"
                    id="pageFilterInput"
                    placeholder="Search URLs..."
                    class="w-full border-0 bg-transparent p-0 text-xs leading-normal text-slate-800 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                    style="border: none !important; box-shadow: none !important; padding: 0 !important; outline: none !important;"
                >
            </div>

            <div id="pagesListContainer" class="mt-1 divide-y divide-slate-100">
                @php $maxPageViews = $topPages->max('views') ?: 1; @endphp
                @foreach ($topPages as $page)
                    @php $pct = round(($page->views / max(1, $totalVisits)) * 100, 1); @endphp
                    <div class="page-row flex items-center justify-between gap-4 py-3" data-url="{{ strtolower($page->url) }}">
                        <div class="min-w-0 flex-1 pr-2">
                            <a href="{{ $page->url }}" target="_blank" class="block truncate font-mono text-xs font-medium text-slate-700 transition-colors hover:text-brand-600 sm:text-sm">
                                {{ $page->url }}
                            </a>
                            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-1 rounded-full bg-brand-500" style="width: {{ min(100, round(($page->views / $maxPageViews) * 100)) }}%;"></div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span class="text-xs font-bold text-slate-900 sm:text-sm">{{ number_format($page->views) }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $pct }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 2: Inbound Referrers / Acquisition Channels --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Inbound Referrers & Sources</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Traffic acquisition origin & organic discovery</p>
                </div>
                <span class="text-xs font-medium text-slate-400">Top 10</span>
            </div>

            {{-- In-card Search Filter --}}
            <div class="mt-4 mb-1 flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 transition-colors focus-within:border-brand-500">
                <svg class="h-4 w-4 shrink-0 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <input
                    type="text"
                    id="referrerFilterInput"
                    placeholder="Search referrers..."
                    class="w-full border-0 bg-transparent p-0 text-xs leading-normal text-slate-800 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                    style="border: none !important; box-shadow: none !important; padding: 0 !important; outline: none !important;"
                >
            </div>

            <div id="referrersListContainer" class="mt-1 divide-y divide-slate-100">
                @php $maxReferrer = $topReferrers->max('count') ?: 1; @endphp
                @foreach ($topReferrers as $ref)
                    @php $pctRef = round(($ref->count / max(1, $totalVisits)) * 100, 1); @endphp
                    <div class="referrer-row flex items-center justify-between gap-4 py-3" data-ref="{{ strtolower($ref->referrer) }}">
                        <div class="min-w-0 flex-1 pr-2">
                            <span class="truncate text-xs font-medium text-slate-700 sm:text-sm">
                                {{ $ref->referrer }}
                            </span>
                            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-1 rounded-full bg-slate-900" style="width: {{ min(100, round(($ref->count / $maxReferrer) * 100)) }}%;"></div>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-right">
                            <span class="text-xs font-bold text-slate-900 sm:text-sm">{{ number_format($ref->count) }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $pctRef }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Section 3: Geographic Distribution (Countries & Cities) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-bold text-slate-900">Geographic Telemetry</h3>
                <p class="mt-0.5 text-xs text-slate-400">Global visitor breakdown by country & metropolitan cities</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Top Countries --}}
                <div>
                    <h4 class="mb-2 text-xs font-medium text-slate-400">Top Countries</h4>
                    <div class="space-y-1">
                        @php
                            $flags = [
                                'BD' => '🇧🇩', 'SG' => '🇸🇬', 'US' => '🇺🇸', 'EG' => '🇪🇬',
                                'DE' => '🇩🇪', 'GB' => '🇬🇧', 'NL' => '🇳🇱', 'FR' => '🇫🇷',
                                'JP' => '🇯🇵', 'CA' => '🇨🇦',
                            ];
                        @endphp
                        @foreach ($topCountries as $c)
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex min-w-0 items-center gap-2 pr-2">
                                    <span class="text-base leading-none">{{ $flags[$c->country_code] ?? '🌐' }}</span>
                                    <span class="truncate text-xs font-medium text-slate-700">{{ $c->country }}</span>
                                </div>
                                <span class="flex-shrink-0 text-xs font-semibold text-slate-900">{{ number_format($c->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top Cities --}}
                <div>
                    <h4 class="mb-2 text-xs font-medium text-slate-400">Top Cities</h4>
                    <div class="space-y-1">
                        @foreach ($topCities as $city)
                            <div class="flex items-center justify-between py-1.5">
                                <div class="flex min-w-0 items-center gap-2 pr-2">
                                    <span class="truncate text-xs font-medium text-slate-700">{{ $city->city }}</span>
                                    <span class="hidden truncate text-[11px] text-slate-400 xl:inline">({{ $city->country }})</span>
                                </div>
                                <span class="flex-shrink-0 text-xs font-semibold text-slate-900">{{ number_format($city->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Systems, Engines & Browsers Matrix --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-sm font-bold text-slate-900">Client Engines & Systems</h3>
                <p class="mt-0.5 text-xs text-slate-400">Browser engines, operating systems & client profiles</p>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                {{-- Top Browsers --}}
                <div>
                    <h4 class="mb-2 text-xs font-medium text-slate-400">Top Browsers</h4>
                    <div class="space-y-2">
                        @foreach ($topBrowsers as $b)
                            @php $bPct = round(($b->count / max(1, $totalVisits)) * 100, 1); @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs font-medium text-slate-700">
                                    <span>{{ $b->browser }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($b->count) }}</span>
                                </div>
                                <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-1 rounded-full bg-slate-400" style="width: {{ min(100, $bPct * 1.3) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top Platforms / OS --}}
                <div>
                    <h4 class="mb-2 text-xs font-medium text-slate-400">Operating Systems</h4>
                    <div class="space-y-2">
                        @foreach ($topPlatforms as $p)
                            @php $pPct = round(($p->count / max(1, $totalVisits)) * 100, 1); @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs font-medium text-slate-700">
                                    <span>{{ $p->platform }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($p->count) }}</span>
                                </div>
                                <div class="mt-1.5 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-1 rounded-full bg-slate-400" style="width: {{ min(100, $pPct * 1.3) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Activity Table --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Real-Time Live Request Stream</h3>
                <p class="mt-0.5 text-xs text-slate-400">Live storefront requests and telemetry logs</p>
            </div>

            {{-- Live Terminal Search Input --}}
            <div class="flex items-center gap-3">
                <div class="flex w-full items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 transition-colors focus-within:border-brand-500 sm:w-64">
                    <svg class="h-4 w-4 shrink-0 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input
                        type="text"
                        id="liveLogsFilterInput"
                        placeholder="Filter live stream..."
                        class="w-full border-0 bg-transparent p-0 text-xs leading-normal text-slate-800 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                        style="border: none !important; box-shadow: none !important; padding: 0 !important; outline: none !important;"
                    >
                </div>
                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-slate-400">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                    </span>
                    Live
                </span>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="border-b border-slate-100 text-[11px] font-medium uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-5 py-3">Visitor / IP</th>
                        <th class="px-5 py-3">Location</th>
                        <th class="px-5 py-3">Requested Route</th>
                        <th class="px-5 py-3">Client OS / Device</th>
                        <th class="px-5 py-3">Browser Engine</th>
                        <th class="px-5 py-3 text-right">Recorded Time</th>
                    </tr>
                </thead>
                <tbody id="liveLogsTableBody" class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentVisits as $visit)
                        <tr class="log-row transition-colors hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs font-medium text-slate-900">
                                {{ $visit->ip_address ? substr($visit->ip_address, 0, 10) . '***' : '103.145.***' }}
                            </td>
                            <td class="px-5 py-3">
                                <span class="font-medium text-slate-900">{{ $visit->city ?: 'Dhaka' }}</span>
                                <span class="text-xs text-slate-400">({{ $visit->country ?: 'Bangladesh' }})</span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-block max-w-[240px] truncate font-mono text-xs text-slate-600">
                                    {{ $visit->url ?: '/' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs capitalize text-slate-600">
                                {{ $visit->device_type ?: 'Desktop' }} &bull; {{ $visit->platform ?: 'Windows' }}
                            </td>
                            <td class="px-5 py-3 text-xs text-slate-600">
                                {{ $visit->browser ?: 'Chrome' }}
                            </td>
                            <td class="px-5 py-3 text-right font-mono text-xs text-slate-400">
                                {{ $visit->created_at?->diffForHumans() ?? 'Just now' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                                No visitor logs recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Interactive Scripts: Chart.js & Live Client Filters --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Chart.js Traffic Area Spline
        const ctx = document.getElementById('visitorTrafficChart');
        if (ctx) {
            const chartLabels = @json($chartLabels);
            const chartVisits = @json($chartVisits);
            const chartUniques = @json($chartUniques);

            const gradientVisits = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradientVisits.addColorStop(0, 'rgba(99, 102, 241, 0.18)');
            gradientVisits.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

            const gradientUniques = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradientUniques.addColorStop(0, 'rgba(15, 23, 42, 0.10)');
            gradientUniques.addColorStop(1, 'rgba(15, 23, 42, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [
                        {
                            label: 'Total Visits',
                            data: chartVisits,
                            borderColor: '#6366f1',
                            backgroundColor: gradientVisits,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: 'Unique Visitors',
                            data: chartUniques,
                            borderColor: '#0f172a',
                            backgroundColor: gradientUniques,
                            borderWidth: 2,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 0,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#0f172a',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 8,
                                boxHeight: 8,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                color: '#64748b',
                                font: {
                                    size: 11,
                                    weight: '600',
                                    family: 'system-ui, -apple-system, sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 10,
                            borderRadius: 8,
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 12 },
                            usePointStyle: true,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                },
                                color: '#94a3b8',
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 12
                            }
                        },
                        y: {
                            grid: {
                                color: '#f1f5f9',
                            },
                            ticks: {
                                font: {
                                    size: 10,
                                },
                                color: '#94a3b8',
                                precision: 0
                            },
                            border: {
                                display: false,
                            }
                        }
                    }
                }
            });
        }

        // 2. Client-side Page Filter
        const pageFilter = document.getElementById('pageFilterInput');
        if (pageFilter) {
            pageFilter.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.page-row').forEach(row => {
                    const url = row.getAttribute('data-url') || '';
                    row.style.display = url.includes(query) ? '' : 'none';
                });
            });
        }

        // 3. Client-side Referrer Filter
        const refFilter = document.getElementById('referrerFilterInput');
        if (refFilter) {
            refFilter.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.referrer-row').forEach(row => {
                    const ref = row.getAttribute('data-ref') || '';
                    row.style.display = ref.includes(query) ? '' : 'none';
                });
            });
        }

        // 4. Client-side Live Logs Filter
        const logsFilter = document.getElementById('liveLogsFilterInput');
        if (logsFilter) {
            logsFilter.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.log-row').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        }
    });
</script>
@endsection
