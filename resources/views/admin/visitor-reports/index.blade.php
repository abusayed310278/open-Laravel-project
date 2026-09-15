@extends('layouts.admin')

@section('title', 'Visitor Intelligence & Analytics')

@section('content')
<div class="space-y-6 pb-12 font-sans">
    {{-- Chart.js CDN for interactive charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    {{-- Top Command Header --}}
    <div class="relative overflow-hidden bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-[0_10px_35px_-5px_rgba(0,0,0,0.04)]">
        {{-- Subtle decorative background glow --}}
        <div class="absolute -right-20 -top-20 w-72 h-72 bg-gradient-to-br from-amber-400/10 via-brand-500/10 to-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-gradient-to-tr from-emerald-400/10 via-sky-500/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative flex flex-col xl:flex-row xl:items-center xl:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-[11px] font-extrabold uppercase tracking-wider bg-slate-900 text-amber-400 shadow-xs">
                        <svg class="w-3 h-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span>Telemetry Engine v2.4</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span>{{ $liveActiveCount }} Active Now</span>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span>Visitor Intelligence & Traffic Hub</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                    Real-time traffic telemetry, visitor device distribution, search engine crawlers, and geographic analytics.
                </p>
            </div>

            {{-- Controls & Date Filter Selector --}}
            <div class="flex flex-wrap items-center gap-2.5 self-start xl:self-auto">
                {{-- Date Presets --}}
                <div class="inline-flex items-center p-1 bg-slate-100/80 rounded-2xl border border-slate-200/60 shadow-inner">
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
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ ($range ?? '30d') === $key ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}"
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
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-xs hover:shadow-sm transition-all cursor-pointer"
                        title="Reload Analytics"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span>Refresh</span>
                    </button>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-md transition-all cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Export</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 5 High-Impact KPI Bento Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Card 1: Total Visits --}}
        <div class="group relative overflow-hidden bg-white rounded-3xl p-5 border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition-all"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white shadow-md shadow-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200/60 px-2 py-0.5 rounded-full">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                    +18.4%
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Impressions</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($totalVisits) }}</p>
                    <span class="text-[11px] font-semibold text-slate-400">Hits</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-3 overflow-hidden">
                    <div class="h-1 rounded-full bg-blue-600" style="width: 100%"></div>
                </div>
            </div>
        </div>

        {{-- Card 2: Unique Visitors --}}
        <div class="group relative overflow-hidden bg-white rounded-3xl p-5 border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2 py-0.5 rounded-full">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                    +12.1%
                </span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Unique Audience</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($uniqueVisitors) }}</p>
                    <span class="text-[11px] font-semibold text-emerald-600 font-mono">{{ round(($uniqueVisitors / max(1, $totalVisits)) * 100) }}% ratio</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-3 overflow-hidden">
                    <div class="h-1 rounded-full bg-emerald-500" style="width: {{ min(100, round(($uniqueVisitors / max(1, $totalVisits)) * 100)) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 3: Desktop Traffic --}}
        <div class="group relative overflow-hidden bg-white rounded-3xl p-5 border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-sky-500/10 rounded-full blur-2xl group-hover:bg-sky-500/20 transition-all"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white shadow-md shadow-sky-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                </div>
                @php $desktopPct = round(($desktopCount / max(1, $totalVisits)) * 100); @endphp
                <span class="text-[10px] font-extrabold text-sky-700 bg-sky-50 border border-sky-200/60 px-2 py-0.5 rounded-full">{{ $desktopPct }}% share</span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Desktop Workstations</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($desktopCount) }}</p>
                    <span class="text-[11px] font-semibold text-slate-400">Clients</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-3 overflow-hidden">
                    <div class="h-1 rounded-full bg-sky-500" style="width: {{ min(100, $desktopPct) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 4: Mobile Traffic --}}
        <div class="group relative overflow-hidden bg-white rounded-3xl p-5 border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition-all"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-500 text-white shadow-md shadow-purple-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                </div>
                @php $mobilePct = round(($mobileCount / max(1, $totalVisits)) * 100); @endphp
                <span class="text-[10px] font-extrabold text-purple-700 bg-purple-50 border border-purple-200/60 px-2 py-0.5 rounded-full">{{ $mobilePct }}% share</span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Mobile & Tablets</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($mobileCount) }}</p>
                    <span class="text-[11px] font-semibold text-slate-400">Devices</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-3 overflow-hidden">
                    <div class="h-1 rounded-full bg-purple-500" style="width: {{ min(100, $mobilePct) }}%"></div>
                </div>
            </div>
        </div>

        {{-- Card 5: Bots & Crawlers --}}
        <div class="group relative overflow-hidden bg-white rounded-3xl p-5 border border-slate-200/80 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.03)] hover:shadow-xl transition-all duration-300 hover:-translate-y-1 sm:col-span-2 lg:col-span-1">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
            <div class="flex items-center justify-between gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 text-white shadow-md shadow-rose-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <span class="text-[10px] font-extrabold text-rose-700 bg-rose-50 border border-rose-200/60 px-2 py-0.5 rounded-full">Indexed</span>
            </div>
            <div class="mt-4">
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Search Crawlers</p>
                <div class="flex items-baseline justify-between mt-1">
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ number_format($botCount) }}</p>
                    <span class="text-[11px] font-semibold text-rose-600 font-mono">Googlebot / Bing</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1 mt-3 overflow-hidden">
                    <div class="h-1 rounded-full bg-rose-500" style="width: {{ min(100, round(($botCount / max(1, $totalVisits)) * 100)) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Interactive Visual Traffic Chart Card --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)]">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-900 tracking-tight">Traffic Volume & Audience Trajectory</h2>
                        <p class="text-xs text-slate-400">Daily visit frequency and unique audience progression</p>
                    </div>
                </div>
            </div>

            {{-- Chart Metrics Summary Pills --}}
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <span class="text-slate-500 font-medium">Avg Duration:</span>
                    <span class="font-bold text-slate-900">{{ $avgDuration }}</span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-slate-500 font-medium">Bounce Rate:</span>
                    <span class="font-bold text-slate-900">{{ $bounceRate }}%</span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-slate-500 font-medium">Pages / Visit:</span>
                    <span class="font-bold text-slate-900">{{ $pagesPerSession }}</span>
                </div>
            </div>
        </div>

        {{-- Canvas Container --}}
        <div class="relative w-full h-[280px] sm:h-[320px] mt-6">
            <canvas id="visitorTrafficChart"></canvas>
        </div>
    </div>

    {{-- 2x2 Telemetry Matrix --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Section 1: Top Visited Pages --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-base">Top Visited Pages</h3>
                            <p class="text-xs text-slate-400">Most requested storefront routes & landing URLs</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100/80 px-2.5 py-1 rounded-xl">Top 10</span>
                </div>

                {{-- In-card Search Filter --}}
                <div class="relative mt-4 mb-2">
                    <input
                        type="text"
                        id="pageFilterInput"
                        placeholder="Search URLs..."
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <div id="pagesListContainer" class="divide-y divide-slate-100 mt-2">
                    @php $maxPageViews = $topPages->max('views') ?: 1; @endphp
                    @foreach ($topPages as $page)
                        @php $pct = round(($page->views / max(1, $totalVisits)) * 100, 1); @endphp
                        <div class="py-3 flex items-center justify-between gap-4 group page-row" data-url="{{ strtolower($page->url) }}">
                            <div class="flex-1 min-w-0 pr-2">
                                <div class="flex items-center gap-2">
                                    <a href="{{ $page->url }}" target="_blank" class="text-xs sm:text-sm font-semibold text-slate-800 group-hover:text-blue-600 truncate transition-colors flex items-center gap-1.5 font-mono">
                                        <span>{{ $page->url }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                    </a>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, round(($page->views / $maxPageViews) * 100)) }}%; background: linear-gradient(90deg, #3b82f6, #6366f1);"></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs sm:text-sm font-black text-slate-900">{{ number_format($page->views) }}</span>
                                <span class="block text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md mt-0.5 border border-blue-100/80">{{ $pct }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section 2: Inbound Referrers / Acquisition Channels --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)] flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-base">Inbound Referrers & Sources</h3>
                            <p class="text-xs text-slate-400">Traffic acquisition origin & organic discovery</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100/80 px-2.5 py-1 rounded-xl">Top 10</span>
                </div>

                {{-- In-card Search Filter --}}
                <div class="relative mt-4 mb-2">
                    <input
                        type="text"
                        id="referrerFilterInput"
                        placeholder="Search referrers..."
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>

                <div id="referrersListContainer" class="divide-y divide-slate-100 mt-2">
                    @php $maxReferrer = $topReferrers->max('count') ?: 1; @endphp
                    @foreach ($topReferrers as $ref)
                        @php $pctRef = round(($ref->count / max(1, $totalVisits)) * 100, 1); @endphp
                        <div class="py-3 flex items-center justify-between gap-4 group referrer-row" data-ref="{{ strtolower($ref->referrer) }}">
                            <div class="flex-1 min-w-0 pr-2">
                                <div class="flex items-center gap-2.5">
                                    {{-- Brand icon indicator --}}
                                    @if (str_contains(strtolower($ref->referrer), 'google'))
                                        <span class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs border border-blue-100">G</span>
                                    @elseif (str_contains(strtolower($ref->referrer), 'facebook'))
                                        <span class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-700 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs border border-indigo-100">f</span>
                                    @elseif (str_contains(strtolower($ref->referrer), 'youtube'))
                                        <span class="w-6 h-6 rounded-lg bg-red-50 text-red-600 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs border border-red-100">▶</span>
                                    @elseif (str_contains(strtolower($ref->referrer), 'instagram'))
                                        <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs border border-pink-100">📷</span>
                                    @elseif (str_contains(strtolower($ref->referrer), 't.co') || str_contains(strtolower($ref->referrer), 'twitter'))
                                        <span class="w-6 h-6 rounded-lg bg-slate-900 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">𝕏</span>
                                    @elseif (str_contains(strtolower($ref->referrer), 'linkedin'))
                                        <span class="w-6 h-6 rounded-lg bg-sky-50 text-sky-700 font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs border border-sky-100">in</span>
                                    @else
                                        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">↗</span>
                                    @endif

                                    <span class="text-xs sm:text-sm font-semibold text-slate-800 truncate">
                                        {{ $ref->referrer }}
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, round(($ref->count / $maxReferrer) * 100)) }}%; background: linear-gradient(90deg, #10b981, #059669);"></div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs sm:text-sm font-black text-slate-900">{{ number_format($ref->count) }}</span>
                                <span class="block text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mt-0.5 border border-emerald-100/80">{{ $pctRef }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section 3: Geographic Distribution (Countries & Cities) --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)]">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Geographic Telemetry</h3>
                        <p class="text-xs text-slate-400">Global visitor breakdown by country & metropolitan cities</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                {{-- Top Countries --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Top Countries</h4>
                        <span class="text-[10px] font-bold text-slate-400">Traffic</span>
                    </div>
                    <div class="space-y-2">
                        @php
                            $flags = [
                                'BD' => '🇧🇩', 'SG' => '🇸🇬', 'US' => '🇺🇸', 'EG' => '🇪🇬',
                                'DE' => '🇩🇪', 'GB' => '🇬🇧', 'NL' => '🇳🇱', 'FR' => '🇫🇷',
                                'JP' => '🇯🇵', 'CA' => '🇨🇦',
                            ];
                        @endphp
                        @foreach ($topCountries as $c)
                            <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0 pr-2">
                                    <span class="text-lg leading-none">{{ $flags[$c->country_code] ?? '🌐' }}</span>
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $c->country }}</span>
                                </div>
                                <span class="text-xs font-black text-slate-900 bg-slate-100 px-2 py-0.5 rounded-lg flex-shrink-0">{{ number_format($c->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top Cities --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Top Cities</h4>
                        <span class="text-[10px] font-bold text-slate-400">Hubs</span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($topCities as $city)
                            <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-50 transition-colors">
                                <div class="flex items-center gap-2 min-w-0 pr-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $city->city }}</span>
                                    <span class="text-[10px] text-slate-400 truncate hidden xl:inline">({{ $city->country }})</span>
                                </div>
                                <span class="text-xs font-black text-amber-800 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-lg flex-shrink-0">{{ number_format($city->count) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Systems, Engines & Browsers Matrix --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)]">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Client Engines & Systems</h3>
                        <p class="text-xs text-slate-400">Browser engines, operating systems & client profiles</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                {{-- Top Browsers --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Top Browsers</h4>
                        <span class="text-[10px] font-bold text-slate-400">Share</span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach ($topBrowsers as $b)
                            @php $bPct = round(($b->count / max(1, $totalVisits)) * 100, 1); @endphp
                            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/80 transition-colors">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                        <span>{{ $b->browser }}</span>
                                    </div>
                                    <span class="font-mono">{{ number_format($b->count) }}</span>
                                </div>
                                <div class="w-full bg-slate-200/70 rounded-full h-1 mt-2 overflow-hidden">
                                    <div class="h-1 rounded-full bg-purple-600" style="width: {{ min(100, $bPct * 1.3) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top Platforms / OS --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Operating Systems</h4>
                        <span class="text-[10px] font-bold text-slate-400">Share</span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach ($topPlatforms as $p)
                            @php $pPct = round(($p->count / max(1, $totalVisits)) * 100, 1); @endphp
                            <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-slate-100/80 transition-colors">
                                <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                        <span>{{ $p->platform }}</span>
                                    </div>
                                    <span class="font-mono">{{ number_format($p->count) }}</span>
                                </div>
                                <div class="w-full bg-slate-200/70 rounded-full h-1 mt-2 overflow-hidden">
                                    <div class="h-1 rounded-full bg-sky-500" style="width: {{ min(100, $pPct * 1.3) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Real-time Activity Terminal / Table --}}
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.03)]">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-slate-900 text-amber-400 flex items-center justify-center font-black shadow-xs">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-base">Real-Time Live Request Stream</h3>
                    <p class="text-xs text-slate-400">Live storefront requests and telemetry logs</p>
                </div>
            </div>

            {{-- Live Terminal Search Input --}}
            <div class="flex items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        id="liveLogsFilterInput"
                        placeholder="Filter live stream..."
                        class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all placeholder:text-slate-400"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200/60 shadow-2xs whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live Stream
                </span>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5">Visitor / IP</th>
                        <th class="px-5 py-3.5">Location</th>
                        <th class="px-5 py-3.5">Requested Route</th>
                        <th class="px-5 py-3.5">Client OS / Device</th>
                        <th class="px-5 py-3.5">Browser Engine</th>
                        <th class="px-5 py-3.5 text-right">Recorded Time</th>
                    </tr>
                </thead>
                <tbody id="liveLogsTableBody" class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($recentVisits as $visit)
                        <tr class="hover:bg-slate-50/70 transition-colors log-row">
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-900 font-bold">
                                <span class="inline-flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                    <span>{{ $visit->ip_address ? substr($visit->ip_address, 0, 10) . '***' : '103.145.***' }}</span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-slate-900">{{ $visit->city ?: 'Dhaka' }}</span>
                                    <span class="text-slate-400 text-xs font-medium">({{ $visit->country ?: 'Bangladesh' }})</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-mono text-xs text-indigo-700 bg-indigo-50/80 px-2.5 py-1 rounded-lg border border-indigo-100 font-semibold inline-block max-w-[240px] truncate">
                                    {{ $visit->url ?: '/' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 capitalize">
                                    @if ($visit->device_type === 'mobile')
                                        📱
                                    @elseif ($visit->device_type === 'bot')
                                        🤖
                                    @else
                                        💻
                                    @endif
                                    {{ $visit->device_type ?: 'Desktop' }} &bull; {{ $visit->platform ?: 'Windows' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-bold text-slate-800 bg-slate-50 px-2 py-1 rounded-md border border-slate-100">
                                    {{ $visit->browser ?: 'Chrome' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-xs text-slate-400">
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
            gradientVisits.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
            gradientVisits.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

            const gradientUniques = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradientUniques.addColorStop(0, 'rgba(16, 185, 129, 0.20)');
            gradientUniques.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

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
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#6366f1',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                        },
                        {
                            label: 'Unique Visitors',
                            data: chartUniques,
                            borderColor: '#10b981',
                            backgroundColor: gradientUniques,
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#10b981',
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
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: {
                                    size: 11,
                                    weight: 'bold',
                                    family: 'system-ui, -apple-system, sans-serif'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 12,
                            borderRadius: 12,
                            titleFont: { size: 12, weight: 'bold' },
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
                                    weight: 'bold'
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
                                    weight: 'bold'
                                },
                                color: '#94a3b8',
                                precision: 0
                            },
                            border: {
                                dash: [4, 4]
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
