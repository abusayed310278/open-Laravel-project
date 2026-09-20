@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    {{-- Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-950 tracking-tight">Admin Overview & Analytics</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Real-time marketplace telemetry, revenue velocity, and operations pipeline.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.verifications.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>KYC Queue ({{ $metrics['pendingKycUsers'] }})</span>
            </a>

            <a href="{{ route('admin.verification-requirements.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white border border-gray-200/80 hover:bg-gray-50 text-gray-700 shadow-2xs transition">
                <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>KYC Rules</span>
            </a>

            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-gray-900 hover:bg-black text-white shadow-xs transition">
                <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span>Full Reports</span>
            </a>
        </div>
    </div>

    {{-- Primary Metrics Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            label="Platform Revenue"
            :value="'Tk '.number_format($metrics['totalRevenue'], 2)"
            :change="($metrics['revenueChange'] >= 0 ? '+'.$metrics['revenueChange'].'%' : $metrics['revenueChange'].'%').' vs last month'"
            :change-color="$metrics['revenueChange'] >= 0 ? 'text-emerald-500' : 'text-red-500'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Total Orders"
            :value="number_format($metrics['totalOrders'])"
            :change="($metrics['ordersChange'] >= 0 ? '+'.$metrics['ordersChange'].'%' : $metrics['ordersChange'].'%').' vs last month'"
            :change-color="$metrics['ordersChange'] >= 0 ? 'text-emerald-500' : 'text-red-500'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>'
        />

        <x-stat-card
            label="Active Products"
            :value="number_format($metrics['activeProducts'])"
            :hint="number_format($metrics['totalProducts']).' total catalog items'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
        />

        <x-stat-card
            label="Pending KYC & Review"
            :value="number_format($metrics['pendingVerifications'])"
            :hint="$metrics['pendingKycUsers'].' Users · '.$metrics['pendingProducts'].' Products'"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />
    </div>

    {{-- Secondary Metrics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200/70 rounded-2xl p-4.5 shadow-2xs hover:shadow-sm transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Platform Users</p>
                <p class="text-xl font-bold text-gray-950 mt-1">{{ number_format($metrics['totalUsers']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">
                    {{ $roleCounts['customer'] }} Users · {{ $roleCounts['saler'] + $roleCounts['business'] }} Sellers · {{ $roleCounts['verifier'] }} Verifiers
                </p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-brand-50 border border-brand-200/60 flex items-center justify-center text-brand-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <div class="bg-white border border-gray-200/70 rounded-2xl p-4.5 shadow-2xs hover:shadow-sm transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Earned Commission</p>
                <p class="text-xl font-bold text-gray-950 mt-1">Tk {{ number_format($metrics['totalCommission'], 2) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Marketplace fee revenue</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-200/60 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <div class="bg-white border border-gray-200/70 rounded-2xl p-4.5 shadow-2xs hover:shadow-sm transition-shadow flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Merchants & Stores</p>
                <p class="text-xl font-bold text-gray-950 mt-1">{{ number_format($roleCounts['business'] + $roleCounts['saler']) }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $roleCounts['business'] }} Store Owners · {{ $roleCounts['saler'] }} Individual Sellers</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-200/60 flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
        </div>
    </div>

    {{-- Seller Identity & KYC Control Center Telemetry Bar --}}
    <div class="bg-gradient-to-r from-gray-900 via-gray-850 to-gray-900 border border-gray-800 rounded-2xl p-5 shadow-xs text-white flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-brand-500/20 border border-brand-500/40 flex items-center justify-center text-brand-400 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-white flex items-center gap-2">
                    <span>Seller Identity & KYC Control Center</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">Active</span>
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Manage trade licenses, NID, tax proofs, and identity verifications for Store Owners & Individual Sellers.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 sm:gap-6 text-xs shrink-0 flex-wrap">
            <div class="text-center px-3 py-1.5 rounded-xl bg-white/5 border border-white/10">
                <span class="text-gray-400 block text-[10px] uppercase tracking-wider font-semibold">Total</span>
                <span class="text-sm font-extrabold text-white">{{ $metrics['totalKycApplications'] }}</span>
            </div>
            <div class="text-center px-3 py-1.5 rounded-xl bg-blue-500/10 border border-blue-500/20">
                <span class="text-blue-300 block text-[10px] uppercase tracking-wider font-semibold">Pending</span>
                <span class="text-sm font-extrabold text-blue-400">{{ $metrics['pendingKycUsers'] }}</span>
            </div>
            <div class="text-center px-3 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                <span class="text-emerald-300 block text-[10px] uppercase tracking-wider font-semibold">Approved</span>
                <span class="text-sm font-extrabold text-emerald-400">{{ $metrics['approvedKycUsers'] }}</span>
            </div>
            <div class="text-center px-3 py-1.5 rounded-xl bg-rose-500/10 border border-rose-500/20">
                <span class="text-rose-300 block text-[10px] uppercase tracking-wider font-semibold">Rejected</span>
                <span class="text-sm font-extrabold text-rose-400">{{ $metrics['rejectedKycUsers'] }}</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.verifications.index') }}" class="px-3.5 py-2 bg-brand-500 hover:bg-brand-600 text-white font-bold rounded-xl shadow-xs transition cursor-pointer text-xs whitespace-nowrap">
                    KYC Applications →
                </a>
                <a href="{{ route('admin.verification-requirements.index') }}" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl border border-white/10 transition cursor-pointer text-xs whitespace-nowrap">
                    Setup Rules
                </a>
            </div>
        </div>
    </div>

    {{-- Interactive Analytics & Graphs Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- 30-Day Revenue & Orders Velocity Area Chart --}}
        <div class="lg:col-span-2 bg-white border border-gray-200/70 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-bold text-gray-950">30-Day Sales & Orders Velocity</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Daily platform revenue and order volume trends</p>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <div class="flex items-center gap-1.5 text-brand-600">
                            <span class="w-3 h-3 rounded-full bg-brand-500"></span>
                            <span>Revenue (Tk)</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-700">
                            <span class="w-3 h-3 rounded-full bg-gray-900"></span>
                            <span>Orders</span>
                        </div>
                    </div>
                </div>

                <div class="relative mt-4 h-72 sm:h-80 w-full">
                    <canvas id="adminRevenueChart"></canvas>
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-gray-400 font-medium">30-Day Total Revenue</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">Tk {{ number_format($metrics['revenueLast30Days'], 2) }}</p>
                </div>
                <div>
                    <span class="text-gray-400 font-medium">30-Day Order Volume</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">{{ number_format($metrics['ordersLast30Days']) }} orders</p>
                </div>
                <div class="hidden sm:block">
                    <span class="text-gray-400 font-medium">Avg. Order Value</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">
                        Tk {{ $metrics['ordersLast30Days'] > 0 ? number_format($metrics['revenueLast30Days'] / $metrics['ordersLast30Days'], 2) : '0.00' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Categories Distribution Chart --}}
        <div class="bg-white border border-gray-200/70 rounded-2xl p-5 sm:p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="pb-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-950">Top Categories Catalog</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Product distribution across departments</p>
                </div>

                <div class="relative mt-4 h-64 sm:h-72 flex items-center justify-center">
                    @if (count($categoryData) > 0 && array_sum($categoryData) > 0)
                        <canvas id="adminCategoryChart"></canvas>
                    @else
                        <div class="text-center text-gray-400 py-10">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <p class="text-xs font-semibold">No category items yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-gray-400">Total Catalog Items</span>
                <span class="font-bold text-gray-900">{{ number_format($metrics['totalProducts']) }} Products</span>
            </div>
        </div>
    </div>

    {{-- Operational Tables Grid (Recent Orders & Pending KYC) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Orders Table --}}
        <x-card title="Recent Orders" class="shadow-2xs">
            <x-slot:headerAction>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                    View All Orders →
                </a>
            </x-slot:headerAction>

            <div class="overflow-x-auto -mx-5 -my-4">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-500 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Order ID</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Amount</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-5 py-3.5 font-bold text-gray-900">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:text-brand-600 transition">
                                        #{{ $order->order_number ?? $order->id }}
                                    </a>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="font-medium text-gray-950">{{ $order->user?->name ?? 'Guest User' }}</span>
                                </td>
                                <td class="px-5 py-3.5 font-bold text-gray-900">
                                    Tk {{ number_format($order->total, 2) }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $order->status?->badgeClass() ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $order->status?->label() ?? ucfirst((string) $order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-gray-400 whitespace-nowrap">
                                    {{ $order->created_at->format('M j, g:ia') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-400 text-xs">
                                    No orders placed yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Pending KYC & Verifications --}}
        <x-card title="Pending KYC Verifications" class="shadow-2xs">
            <x-slot:headerAction>
                <a href="{{ route('admin.verifications.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                    View KYC Queue →
                </a>
            </x-slot:headerAction>

            <div class="overflow-x-auto -mx-5 -my-4">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 text-gray-500 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-5 py-3">Applicant</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Submitted</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentKycList as $kyc)
                            @php
                                $applicant = $kyc->user;
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-brand-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($applicant->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-950 leading-tight">{{ $applicant->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $applicant->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $applicant->role->badgeClass() }}">
                                        {{ $applicant->role->label() }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-gray-400 whitespace-nowrap">
                                    {{ $kyc->created_at->diffForHumans() }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a
                                        href="{{ route('admin.verifications.show', $kyc) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-[11px] font-bold shadow-2xs transition"
                                    >
                                        <span>Review</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-xs">
                                    No pending KYC applications in queue.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    {{-- Top Selling Products --}}
    <x-card title="Top Selling Products" class="shadow-2xs">
        <x-slot:headerAction>
            <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                View All Products →
            </a>
        </x-slot:headerAction>

        <div class="overflow-x-auto -mx-5 -my-4">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/80 text-gray-500 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3 text-right">Units Sold</th>
                        <th class="px-5 py-3 text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($topProducts as $product)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-3.5 font-bold text-gray-950">{{ $product->product_title }}</td>
                            <td class="px-5 py-3.5 text-right text-gray-600">{{ number_format($product->qty) }}</td>
                            <td class="px-5 py-3.5 text-right font-bold text-gray-900">Tk {{ number_format($product->rev, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-400 text-xs">
                                No sales recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const brandColor = getComputedStyle(document.documentElement).getPropertyValue('--color-brand-500').trim() || '#f59e0b';
            const hexToRgba = (hex, alpha) => {
                const normalized = hex.replace('#', '');
                const full = normalized.length === 3 ? normalized.split('').map((c) => c + c).join('') : normalized;
                const value = parseInt(full, 16);
                return `rgba(${(value >> 16) & 255}, ${(value >> 8) & 255}, ${value & 255}, ${alpha})`;
            };

            // 1. Revenue & Orders Velocity Chart
            const revCanvas = document.getElementById('adminRevenueChart');
            if (revCanvas) {
                const labels = @json($chartLabels);
                const revenueData = @json($chartRevenue);
                const ordersData = @json($chartOrders);

                const ctx = revCanvas.getContext('2d');
                const gradientRev = ctx.createLinearGradient(0, 0, 0, 280);
                gradientRev.addColorStop(0, hexToRgba(brandColor, 0.25));
                gradientRev.addColorStop(1, hexToRgba(brandColor, 0));

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Revenue (Tk)',
                                data: revenueData,
                                borderColor: brandColor,
                                backgroundColor: gradientRev,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.35,
                                pointRadius: 2,
                                pointHoverRadius: 6,
                                pointBackgroundColor: brandColor,
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Orders',
                                data: ordersData,
                                borderColor: '#0f172a',
                                backgroundColor: 'rgba(15, 23, 42, 0.05)',
                                borderWidth: 2,
                                borderDash: [4, 4],
                                fill: false,
                                tension: 0.3,
                                pointRadius: 2,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#0f172a',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                yAxisID: 'y1',
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
                                display: false,
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                borderColor: '#334155',
                                borderWidth: 1,
                                padding: 10,
                                boxPadding: 4,
                                usePointStyle: true,
                                callbacks: {
                                    label: function (context) {
                                        if (context.dataset.yAxisID === 'y') {
                                            return 'Revenue: Tk ' + Number(context.raw).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                        }
                                        return 'Orders: ' + context.raw;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                                ticks: {
                                    font: { size: 10 },
                                    color: '#94a3b8',
                                    maxTicksLimit: 12,
                                }
                            },
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: {
                                    color: '#f1f5f9',
                                },
                                ticks: {
                                    font: { size: 10 },
                                    color: '#94a3b8',
                                    callback: function (val) {
                                        return 'Tk ' + (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val);
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    font: { size: 10 },
                                    color: '#94a3b8',
                                    stepSize: 1,
                                }
                            }
                        }
                    }
                });
            }

            // 2. Category Breakdown Chart
            const catCanvas = document.getElementById('adminCategoryChart');
            if (catCanvas) {
                const catLabels = @json($categoryLabels);
                const catData = @json($categoryData);

                if (catData && catData.length > 0) {
                    new Chart(catCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: catLabels,
                            datasets: [{
                                data: catData,
                                backgroundColor: [
                                    brandColor,
                                    '#0f172a',
                                    '#10b981',
                                    '#6366f1',
                                    '#ec4899',
                                    '#8b5cf6',
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        font: { size: 10 },
                                        color: '#475569',
                                        padding: 10,
                                    }
                                }
                            },
                            cutout: '65%',
                        }
                    });
                }
            }
        });
    </script>
@endsection
