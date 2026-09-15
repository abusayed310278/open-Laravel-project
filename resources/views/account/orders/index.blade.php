@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Orders</h1>

        <x-card>
            <x-table :headers="['Order', 'Date', 'Status', 'Total', '']" id="orders-table">
                @forelse ($orders as $order)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3"><x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge></td>
                        <td class="px-4 py-3 text-gray-800">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('account.orders.show', $order) }}" class="text-brand-600 font-medium hover:underline text-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No orders yet.</td>
                    </tr>
                @endforelse
            </x-table>

            <x-pagination :paginator="$orders" />
        </x-card>
    </div>
@endsection
