@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Refunds')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
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

        <x-table :headers="['Order', 'Requested by', 'Amount', 'Reason', 'Status', '']" id="seller-refunds-table">
            @forelse ($refunds as $refund)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $refund->vendorOrder->vendor_order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $refund->order->order_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $refund->requestedBy->name }}</td>
                    <td class="px-4 py-3 text-gray-900">${{ number_format($refund->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500 max-w-xs truncate" title="{{ $refund->reason }}">{{ $refund->reason }}</td>
                    <td class="px-4 py-3"><x-badge :color="$refund->status->badgeColor()">{{ $refund->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right space-x-3">
                        @if ($refund->status->value === 'pending')
                            <form method="POST" action="{{ route($routePrefix.'refunds.approve', $refund) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Approve</button>
                            </form>
                            <form method="POST" action="{{ route($routePrefix.'refunds.reject', $refund) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 font-medium hover:underline">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No refund requests yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$refunds" />
    </x-card>
@endsection
