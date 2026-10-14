@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <x-breadcrumb :items="['Orders' => route('account.orders.index'), 'Order '.$order->order_number => route('account.orders.show', $order), 'Invoice' => null]" class="mb-1" />
                <h1 class="text-xl font-bold text-gray-900">Invoice #{{ $order->order_number }}</h1>
            </div>
            <div class="flex items-center gap-2">
                <x-button as="a" :href="route('account.orders.invoice.download', $order)" class="inline-flex items-center gap-1.5 shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Download PDF</span>
                </x-button>
                <a href="{{ route('account.orders.show', $order) }}" class="inline-flex items-center gap-1 px-3 py-2 text-xs font-semibold rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                    <span>View Order</span>
                </a>
            </div>
        </div>

        <x-card :title="'Invoice '.$order->order_number">
            <x-slot:action>
                <x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge>
            </x-slot:action>

            <div class="grid sm:grid-cols-2 gap-6 mb-6 text-sm border-b border-gray-100 pb-6">
                <div>
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-1 font-semibold">Order Information</p>
                    <p class="font-mono font-medium text-gray-900">{{ $order->order_number }}</p>
                    <p class="text-gray-500 mt-1 text-xs">Issued on {{ $order->created_at->format('M j, Y') }}</p>
                    <p class="text-gray-500 text-xs">Payment Method: {{ $order->vendorOrders->first()?->payment_method?->label() ?? 'Online' }}</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-gray-400 text-xs uppercase tracking-wide mb-1 font-semibold">Billed To</p>
                    <p class="font-semibold text-gray-900">{{ $order->customer->name }}</p>
                    <p class="text-gray-500 text-xs">{{ $order->customer->email }}</p>
                    @if ($order->billingAddress)
                        <p class="text-gray-500 text-xs mt-1">
                            {{ $order->billingAddress->address_line1 }}@if ($order->billingAddress->address_line2), {{ $order->billingAddress->address_line2 }}@endif<br>
                            {{ $order->billingAddress->city }}, {{ $order->billingAddress->state }} {{ $order->billingAddress->postal_code }}<br>
                            {{ $order->billingAddress->country }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between py-3.5 text-sm">
                        <div class="min-w-0 flex-1 pr-4">
                            <p class="font-semibold text-gray-900 truncate">{{ $item->product_title }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Sold by: <span class="text-gray-600 font-medium">{{ $item->vendorOrder?->vendor?->name ?? 'Openbox Official' }}</span>
                                · {{ $item->sku ?? 'SKU: —' }}
                                · Qty {{ $item->quantity }}
                                · ${{ number_format($item->unit_price, 2) }} each
                            </p>
                        </div>
                        <span class="font-bold text-gray-900 shrink-0 font-mono">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <div class="space-y-2 text-sm mt-6 pt-4 border-t border-gray-100">
                <div class="flex justify-between text-gray-500"><span>Subtotal</span><span class="font-mono">${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Shipping</span><span class="font-mono">${{ number_format($order->shipping_total, 2) }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Tax</span><span class="font-mono">${{ number_format($order->tax_total, 2) }}</span></div>
                @if ($order->discount > 0 || $order->coupon_discount > 0)
                    <div class="flex justify-between text-emerald-600"><span>Discount</span><span class="font-mono">-${{ number_format($order->discount + $order->coupon_discount, 2) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-base text-gray-950 pt-3 border-t border-gray-200 mt-2">
                    <span>Total Amount</span>
                    <span class="font-mono">${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </x-card>
    </div>
@endsection
