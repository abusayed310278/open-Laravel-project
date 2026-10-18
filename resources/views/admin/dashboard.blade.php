@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Marketplace overview</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.verifications.index') }}" class="px-3 py-2 rounded-lg text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition">
                KYC Queue ({{ $metrics['pendingKycUsers'] }})
            </a>
            <a href="{{ route('admin.verifiers.index') }}" class="px-3 py-2 rounded-lg text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition">
                Verifiers
            </a>
            <a href="{{ route('admin.verification-requirements.index') }}" class="px-3 py-2 rounded-lg text-xs font-medium bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 transition">
                KYC Rules
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-lg text-xs font-medium bg-gray-900 hover:bg-black text-white transition">
                Reports
            </a>
        </div>
    </div>

    {{-- Metrics --}}
    @php
        $revenueChange = $metrics['revenueChange'];
        $ordersChange = $metrics['ordersChange'];
        $cards = [
            ['Platform Revenue', 'Tk '.number_format($metrics['totalRevenue'], 2), ($revenueChange >= 0 ? '+' : '').$revenueChange.'% vs last month'],
            ['Total Orders', number_format($metrics['totalOrders']), ($ordersChange >= 0 ? '+' : '').$ordersChange.'% vs last month'],
            ['Active Products', number_format($metrics['activeProducts']), number_format($metrics['totalProducts']).' total'],
            ['Pending Review', number_format($metrics['pendingVerifications']), $metrics['pendingKycUsers'].' users · '.$metrics['pendingProducts'].' products'],
            ['Platform Users', number_format($metrics['totalUsers']), ($roleCounts['customer'] + $roleCounts['saler']).' individual users (buy & sell) · '.$roleCounts['business'].' store owners · '.$roleCounts['verifier'].' verifiers'],
            ['Earned Commission', 'Tk '.number_format($metrics['totalCommission'], 2), 'Marketplace fees'],
        ];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($cards as [$label, $value, $hint])
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500">{{ $label }}</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $value }}</p>
                <p class="text-[11px] text-gray-400 mt-0.5">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    {{-- KYC summary --}}
    <div class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Seller KYC</h3>
            <p class="text-xs text-gray-500 mt-0.5">Identity and document verification for sellers.</p>
        </div>
        <div class="flex flex-wrap items-center gap-6 text-xs">
            <div><span class="text-gray-500">Total</span> <span class="ml-1 font-bold text-gray-900">{{ $metrics['totalKycApplications'] }}</span></div>
            <div><span class="text-gray-500">Pending</span> <span class="ml-1 font-bold text-gray-900">{{ $metrics['pendingKycUsers'] }}</span></div>
            <div><span class="text-gray-500">Approved</span> <span class="ml-1 font-bold text-gray-900">{{ $metrics['approvedKycUsers'] }}</span></div>
            <div><span class="text-gray-500">Rejected</span> <span class="ml-1 font-bold text-gray-900">{{ $metrics['rejectedKycUsers'] }}</span></div>
            <a href="{{ route('admin.verifications.index') }}" class="px-3 py-2 bg-gray-900 hover:bg-black text-white font-medium rounded-lg transition">Applications</a>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Last 30 days</h2>
                <div class="flex items-center gap-4 text-xs text-gray-600">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-900"></span>Revenue</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-gray-400"></span>Orders</span>
                </div>
            </div>

            <div class="relative mt-4 h-72 w-full">
                <canvas id="adminRevenueChart"></canvas>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 grid grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-gray-500">Revenue</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">Tk {{ number_format($metrics['revenueLast30Days'], 2) }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Orders</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">{{ number_format($metrics['ordersLast30Days']) }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Avg. order</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">
                        Tk {{ $metrics['ordersLast30Days'] > 0 ? number_format($metrics['revenueLast30Days'] / $metrics['ordersLast30Days'], 2) : '0.00' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="pb-3 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Top categories</h2>
            </div>

            <div class="relative mt-4 h-64 flex items-center justify-center">
                @if (count($categoryData) > 0 && array_sum($categoryData) > 0)
                    <canvas id="adminCategoryChart"></canvas>
                @else
                    <p class="text-xs text-gray-400">No category items yet</p>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-gray-500">Total products</span>
                <span class="font-bold text-gray-900">{{ number_format($metrics['totalProducts']) }}</span>
            </div>
        </div>
    </div>

    {{-- Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Recent Orders">
            <x-slot:headerAction>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-gray-600 hover:text-gray-900 hover:underline">View all</a>
            </x-slot:headerAction>

            <div class="overflow-x-auto -mx-5 -my-4">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="text-gray-500 border-b border-gray-100 text-[11px]">
                        <tr>
                            <th class="px-5 py-3 font-medium">Order</th>
                            <th class="px-5 py-3 font-medium">Customer</th>
                            <th class="px-5 py-3 font-medium">Amount</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">#{{ $order->order_number ?? $order->id }}</a>
                                </td>
                                <td class="px-5 py-3">{{ $order->user?->name ?? 'Guest' }}</td>
                                <td class="px-5 py-3 text-gray-900">Tk {{ number_format($order->total, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-[11px]">
                                        {{ $order->status?->label() ?? ucfirst((string) $order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-gray-400 whitespace-nowrap">{{ $order->created_at->format('M j, g:ia') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-400">No orders placed yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Pending KYC">
            <x-slot:headerAction>
                <a href="{{ route('admin.verifications.index') }}" class="text-xs text-gray-600 hover:text-gray-900 hover:underline">View queue</a>
            </x-slot:headerAction>

            <div class="overflow-x-auto -mx-5 -my-4">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="text-gray-500 border-b border-gray-100 text-[11px]">
                        <tr>
                            <th class="px-5 py-3 font-medium">Applicant</th>
                            <th class="px-5 py-3 font-medium">Role</th>
                            <th class="px-5 py-3 font-medium">Submitted</th>
                            <th class="px-5 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($recentKycList as $kyc)
                            @php($applicant = $kyc->user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <p class="font-medium text-gray-900 leading-tight">{{ $applicant->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $applicant->email }}</p>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">{{ $applicant->role->label() }}</td>
                                <td class="px-5 py-3 text-gray-400 whitespace-nowrap">{{ $kyc->created_at->diffForHumans() }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.verifications.show', $kyc) }}" class="px-2.5 py-1 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-[11px] font-medium transition">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400">No pending KYC applications.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>

    <x-card title="Top Selling Products">
        <x-slot:headerAction>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-gray-600 hover:text-gray-900 hover:underline">View all</a>
        </x-slot:headerAction>

        <div class="overflow-x-auto -mx-5 -my-4">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="text-gray-500 border-b border-gray-100 text-[11px]">
                    <tr>
                        <th class="px-5 py-3 font-medium">Product</th>
                        <th class="px-5 py-3 font-medium text-right">Units</th>
                        <th class="px-5 py-3 font-medium text-right">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($topProducts as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 font-medium text-gray-900">{{ $product->product_title }}</td>
                            <td class="px-5 py-3 text-right">{{ number_format($product->qty) }}</td>
                            <td class="px-5 py-3 text-right text-gray-900">Tk {{ number_format($product->rev, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-gray-400">No sales recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dark = '#111827';
            const mid = '#9ca3af';

            const revCanvas = document.getElementById('adminRevenueChart');
            if (revCanvas) {
                new Chart(revCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Revenue (Tk)',
                                data: @json($chartRevenue),
                                borderColor: dark,
                                borderWidth: 2,
                                tension: 0.3,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Orders',
                                data: @json($chartOrders),
                                borderColor: mid,
                                borderWidth: 2,
                                borderDash: [4, 4],
                                tension: 0.3,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (context) => context.dataset.yAxisID === 'y'
                                        ? 'Revenue: Tk ' + Number(context.raw).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                                        : 'Orders: ' + context.raw
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, color: mid, maxTicksLimit: 10 } },
                            y: {
                                position: 'left',
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    font: { size: 10 },
                                    color: mid,
                                    callback: (val) => 'Tk ' + (val >= 1000 ? (val / 1000).toFixed(0) + 'k' : val)
                                }
                            },
                            y1: {
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: { font: { size: 10 }, color: mid, stepSize: 1 }
                            }
                        }
                    }
                });
            }

            const catCanvas = document.getElementById('adminCategoryChart');
            if (catCanvas) {
                new Chart(catCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryLabels),
                        datasets: [{
                            data: @json($categoryData),
                            backgroundColor: ['#111827', '#374151', '#6b7280', '#9ca3af', '#d1d5db', '#e5e7eb'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 }, color: '#4b5563', padding: 10 } }
                        }
                    }
                });
            }
        });
    </script>
@endsection
