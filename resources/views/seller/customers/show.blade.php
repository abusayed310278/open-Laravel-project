@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Customer Details - '.$customer->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route($routePrefix.'customers.index') }}" class="p-2 bg-white border border-gray-200 hover:bg-gray-100 text-gray-700 rounded-xl shadow-2xs transition-colors" title="Back to Customers">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-950">{{ $customer->name }}</h1>
                    <p class="text-xs text-gray-500">Customer profile and complete order history with your store.</p>
                </div>
            </div>
        </div>

        {{-- Customer Overview Cards --}}
        <div class="grid sm:grid-cols-4 gap-4">
            <x-card>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Store Orders</p>
                <p class="text-2xl font-bold text-gray-900 mt-1 font-mono">{{ number_format($ordersCount) }}</p>
            </x-card>

            <x-card>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Lifetime Spent</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">${{ number_format($totalSpent, 2) }}</p>
            </x-card>

            <x-card>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">First Purchase</p>
                <p class="text-base font-bold text-gray-800 mt-1">{{ $firstOrderAt ? $firstOrderAt->format('M j, Y') : '—' }}</p>
            </x-card>

            <x-card>
                <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Latest Purchase</p>
                <p class="text-base font-bold text-gray-800 mt-1">{{ $latestOrderAt ? $latestOrderAt->format('M j, Y') : '—' }}</p>
            </x-card>
        </div>

        {{-- Customer Info Card --}}
        <x-card title="Customer Information">
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-base border border-brand-200 shrink-0">
                        {{ substr($customer->name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ $customer->name }}</h3>
                        <p class="text-xs text-gray-500">Customer Account</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase">Contact Information</p>
                    <p class="text-xs font-semibold text-gray-800 mt-1">{{ $customer->email }}</p>
                    <p class="text-xs text-gray-600 mt-0.5">{{ $customer->phone ?: 'No phone provided' }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase">Registered Date</p>
                    <p class="text-xs font-semibold text-gray-800 mt-1">{{ $customer->created_at->format('M j, Y') }}</p>
                </div>
            </div>
        </x-card>

        {{-- Customer Order History --}}
        <x-card title="Order History with Your Store">
            <x-table :headers="['Order Number', 'Date', 'Items Purchased', 'Status', 'Total', 'Action']" id="customer-orders-table">
                @foreach ($vendorOrders as $vo)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3.5 font-medium text-gray-900 font-mono">
                            #{{ $vo->vendor_order_number }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-500">
                            {{ $vo->created_at->format('M j, Y \a\t g:i A') }}
                        </td>
                        <td class="px-4 py-3.5 text-xs text-gray-700">
                            <p class="font-semibold text-gray-900">{{ $vo->items->count() }} item{{ $vo->items->count() > 1 ? 's' : '' }}</p>
                            <p class="text-gray-400 text-[11px] truncate max-w-[220px]">
                                {{ $vo->items->pluck('product_title')->implode(', ') }}
                            </p>
                        </td>
                        <td class="px-4 py-3.5">
                            <x-badge :color="$vo->status->badgeColor()">{{ $vo->status->label() }}</x-badge>
                        </td>
                        <td class="px-4 py-3.5 text-xs font-bold text-gray-900">
                            ${{ number_format($vo->total, 2) }}
                        </td>
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                @if ($vo->invoice)
                                    <a href="{{ route($routePrefix.'invoices.show', $vo->invoice) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Invoice</span>
                                    </a>
                                @endif
                                <a href="{{ route($routePrefix.'orders.show', $vo) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold border border-gray-200/80 transition-colors">
                                    <span>View Order</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>
@endsection
