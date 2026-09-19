@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Orders</h1>

        <x-card>
            <x-table :headers="['Order', 'Date', 'Status', 'Total', '']" id="orders-table">
                @forelse ($orders as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3"><x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge></td>
                        <td class="px-4 py-3 font-semibold text-gray-900 text-sm">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                @php($invoices = $order->vendorOrders->map(fn($vo) => $vo->invoice)->filter())
                                @if ($invoices->count() === 1)
                                    @php($inv = $invoices->first())
                                    <a href="{{ route('account.invoices.show', $inv) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition-colors" title="View Invoice">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <span>Invoice</span>
                                    </a>
                                @elseif ($invoices->count() > 1)
                                    @foreach ($invoices as $idx => $inv)
                                        <a href="{{ route('account.invoices.show', $inv) }}" class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition-colors" title="Invoice #{{ $inv->invoice_number }}">
                                            <span>Inv {{ $idx + 1 }}</span>
                                        </a>
                                    @endforeach
                                @endif

                                <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200/80 transition-colors">
                                    <span>View Order</span>
                                </a>
                            </div>
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
