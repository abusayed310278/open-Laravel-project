@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Invoices</h1>

        <x-card>
            <x-table :headers="['Order #', 'Date', 'Status', 'Items', 'Total', '']" id="invoices-table">
                @forelse ($orders as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $order->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3"><x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge></td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}</td>
                        <td class="px-4 py-3 text-gray-800 font-semibold font-mono">${{ number_format($order->total, 2) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-2">
                                <a href="{{ route('account.orders.invoice', $order) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition-colors" title="View Invoice">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>View</span>
                                </a>
                                <a href="{{ route('account.orders.invoice.download', $order) }}" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200/80 transition-colors" title="Download PDF">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Download</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No invoices yet.</td>
                    </tr>
                @endforelse
            </x-table>

            @if ($orders->hasPages())
                <div class="mt-4 pt-2">
                    <x-pagination :paginator="$orders" />
                </div>
            @elseif ($orders->total() > 0)
                <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>Showing all <strong class="font-semibold text-gray-700">{{ $orders->total() }}</strong> {{ \Illuminate\Support\Str::plural('invoice', $orders->total()) }}</span>
                </div>
            @endif
        </x-card>
    </div>
@endsection
