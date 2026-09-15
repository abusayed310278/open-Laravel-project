@extends('layouts.app')

@section('title', 'Order Confirmed')

@section('content')
    <div class="max-w-lg mx-auto px-4 sm:px-6 py-16 text-center">
        <svg class="w-16 h-16 text-green-500 mx-auto mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75l2.25 2.25L15 9m6 3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Order Placed!</h1>
        <p class="text-gray-500 mb-8">Order #{{ $order->order_number }} — we'll email you as each seller ships your items.</p>

        <div class="bg-white border border-gray-100 rounded-md p-6 text-left mb-8">
            @foreach ($order->vendorOrders as $vendorOrder)
                <div class="mb-4 last:mb-0">
                    <p class="text-xs text-gray-400 mb-1">#{{ $vendorOrder->vendor_order_number }}</p>
                    @foreach ($vendorOrder->items as $item)
                        <div class="flex justify-between text-sm text-gray-700">
                            <span>{{ $item->product_title }} × {{ $item->quantity }}</span>
                            <span>${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <hr class="border-gray-100 my-4">
            <div class="flex justify-between text-base font-bold text-gray-900">
                <span>Total</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="flex items-center justify-center gap-3">
            <x-button as="a" :href="route('account.orders.show', $order)">Track Order</x-button>
            <x-button variant="secondary" as="a" :href="route('shop')">Continue Shopping</x-button>
        </div>
    </div>
@endsection
