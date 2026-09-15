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

        <x-table :headers="['Order', 'Customer', 'Date', 'Status', 'Total', '']" id="seller-orders-table">
            @forelse ($vendorOrders as $vendorOrder)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $vendorOrder->vendor_order_number }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $vendorOrder->order->customer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $vendorOrder->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3"><x-badge :color="$vendorOrder->status->badgeColor()">{{ $vendorOrder->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($vendorOrder->total, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route($routePrefix.'orders.show', $vendorOrder) }}" class="text-brand-600 font-medium hover:underline text-sm">Manage</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No orders yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$vendorOrders" />
    </x-card>
@endsection
