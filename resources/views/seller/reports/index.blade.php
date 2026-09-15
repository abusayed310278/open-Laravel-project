@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Reports')

@section('content')
    <x-card>
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <x-input label="From" name="from" type="date" :value="$from->format('Y-m-d')" />
            <x-input label="To" name="to" type="date" :value="$to->format('Y-m-d')" />
            <x-button type="submit">Apply</x-button>
        </form>
    </x-card>

    <div class="grid sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Revenue</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Orders</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">{{ $isSaler ? 'Buyers' : 'Customers' }}</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($uniqueCustomers) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Active Listings</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($activeListings) }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mt-6">
        <x-card title="Daily Sales">
            @if (empty($dailySales))
                <p class="text-sm text-gray-400 text-center py-10">No sales in this range.</p>
            @else
                @php $max = max(array_column($dailySales, 'total')) ?: 1; @endphp
                <div class="space-y-2">
                    @foreach ($dailySales as $day)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-20 text-gray-400 text-xs">{{ \Illuminate\Support\Carbon::parse($day['date'])->format('M j') }}</span>
                            <div class="flex-1 bg-gray-50 rounded h-4 overflow-hidden">
                                <div class="bg-brand-500 h-4 rounded" style="width: {{ max(2, round($day['total'] / $max * 100)) }}%"></div>
                            </div>
                            <span class="w-20 text-right text-gray-700 font-medium">${{ number_format($day['total'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="Account">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total listing views</span>
                    <span class="text-gray-900 font-medium">{{ number_format($totalViews) }}</span>
                </div>

                @if ($isSaler)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Listing credits remaining</span>
                        <span class="text-gray-900 font-medium">{{ $listingCredit?->remaining_credits ?? 0 }} / {{ $listingCredit?->total_credits ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Credits expire</span>
                        <span class="text-gray-900 font-medium">{{ $listingCredit?->expires_at?->format('M j, Y') ?? '—' }}</span>
                    </div>
                @else
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subscription plan</span>
                        <span class="text-gray-900 font-medium">{{ $subscription?->plan?->name ?? 'None' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Renews</span>
                        <span class="text-gray-900 font-medium">{{ $subscription?->next_billing_at?->format('M j, Y') ?? '—' }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        <x-card title="Top Products" class="lg:col-span-2">
            <x-table :headers="['Product', 'Qty Sold', 'Revenue']" id="seller-top-products-table">
                @forelse ($topProducts as $product)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $product['title'] }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product['quantity'] }}</td>
                        <td class="px-4 py-3 text-gray-900">${{ number_format($product['revenue'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No sales yet.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
@endsection
