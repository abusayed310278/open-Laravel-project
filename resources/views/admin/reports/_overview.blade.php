    <div class="grid sm:grid-cols-4 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Revenue</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($d['totalRevenue'], 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Orders</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['totalOrders']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Commission Earned</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($d['totalCommission'], 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Active Sellers</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['activeSellers']) }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mt-6">
        <x-card title="Daily Sales">
            @if (empty($d['dailySales']))
                <p class="text-sm text-gray-400 text-center py-10">No sales in this range.</p>
            @else
                @php $max = max(array_column($d['dailySales'], 'total')) ?: 1; @endphp
                <div class="space-y-2">
                    @foreach ($d['dailySales'] as $day)
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

        <x-card title="Order Status">
            @if (empty($d['statusBreakdown']))
                <p class="text-sm text-gray-400 text-center py-10">No orders in this range.</p>
            @else
                <div class="space-y-3">
                    @foreach ($d['statusBreakdown'] as $row)
                        <div class="flex items-center justify-between text-sm">
                            <x-badge :color="$row['color']">{{ $row['label'] }}</x-badge>
                            <span class="text-gray-700 font-medium">{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="Top Products">
            <x-table :headers="['Product', 'Qty Sold', 'Revenue']" id="top-products-table">
                @forelse ($d['topProducts'] as $product)
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

        <x-card title="Top Sellers">
            <x-table :headers="['Seller', 'Orders', 'Revenue']" id="top-sellers-table">
                @forelse ($d['topSellers'] as $seller)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 text-gray-700">{{ $seller['name'] }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $seller['orders'] }}</td>
                        <td class="px-4 py-3 text-gray-900">${{ number_format($seller['revenue'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No sales yet.</td>
                    </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
