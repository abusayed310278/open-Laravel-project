@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Orders')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Order', 'Customer', 'Date', 'Status', 'Total', 'Invoice', '']" id="seller-orders-table">
            @forelse ($vendorOrders as $vendorOrder)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $vendorOrder->vendor_order_number }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $vendorOrder->order->customer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $vendorOrder->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3"><x-badge :color="$vendorOrder->status->badgeColor()">{{ $vendorOrder->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-800 font-medium">${{ number_format($vendorOrder->total, 2) }}</td>
                    <td class="px-4 py-3">
                        @if ($vendorOrder->invoice)
                            <a href="{{ route($routePrefix.'invoices.show', $vendorOrder->invoice) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $vendorOrder->invoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route($routePrefix.'orders.show', $vendorOrder) }}" class="text-brand-600 font-medium hover:underline text-sm">Manage</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No orders yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$vendorOrders" />
    </x-card>
@endsection
