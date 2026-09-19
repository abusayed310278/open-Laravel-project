@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Customers')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-950">Customer Directory</h1>
                <p class="text-xs sm:text-sm text-gray-500">View customer lifetime value, order history, and contact buyers who purchased from your store.</p>
            </div>
        </div>

        {{-- Metrics Summary Cards --}}
        <div class="grid sm:grid-cols-3 gap-4">
            <x-card>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-brand-50 text-brand-600 rounded-xl border border-brand-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Buyers</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">{{ number_format($totalCustomers) }}</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Customer Lifetime Value</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">${{ number_format($totalRevenue, 2) }}</p>
                    </div>
                </div>
            </x-card>

            <x-card>
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl border border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Avg. Order Value</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">${{ number_format($avgOrderValue, 2) }}</p>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Search & Sort Toolbar --}}
        <x-card>
            <form method="GET" action="{{ route($routePrefix.'customers.index') }}" class="flex flex-wrap items-center gap-3">
                <div class="flex-1 min-w-[220px]">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search by customer name, email, or phone..."
                            class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                        >
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="w-48">
                    <select name="sort" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                        <option value="spent_desc" {{ $sort === 'spent_desc' ? 'selected' : '' }}>Highest Total Spent</option>
                        <option value="spent_asc" {{ $sort === 'spent_asc' ? 'selected' : '' }}>Lowest Total Spent</option>
                        <option value="orders_desc" {{ $sort === 'orders_desc' ? 'selected' : '' }}>Most Orders</option>
                        <option value="recent" {{ $sort === 'recent' ? 'selected' : '' }}>Recent Purchase</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                    Filter
                </button>

                @if ($search || $sort !== 'spent_desc')
                    <a href="{{ route($routePrefix.'customers.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                        Reset
                    </a>
                @endif
            </form>
        </x-card>

        {{-- Customers Table --}}
        <x-card>
            <x-table :headers="['Customer', 'Contact Number', 'Orders Placed', 'Total Spent', 'Last Purchase', 'Action']" id="customers-table">
                @forelse ($customers as $cust)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-gray-900">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm shrink-0 uppercase border border-brand-200/60">
                                    {{ substr($cust->name, 0, 2) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $cust->name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $cust->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-600">
                            {{ $cust->phone ?: '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-900 font-semibold">
                            <span class="px-2.5 py-1 bg-gray-100 rounded-lg text-gray-800 font-mono">{{ $cust->orders_count }} orders</span>
                        </td>
                        <td class="px-4 py-3.5 text-xs font-bold text-emerald-600">
                            ${{ number_format($cust->total_spent, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500">
                            {{ $cust->last_order_at ? \Carbon\Carbon::parse($cust->last_order_at)->format('M j, Y') : '—' }}
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route($routePrefix.'customers.show', $cust) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold border border-gray-200/80 transition-colors">
                                    <span>View Details</span>
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            No customers found for your store yet.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                <x-pagination :paginator="$customers" />
            </div>
        </x-card>
    </div>
@endsection
