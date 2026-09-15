@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h1>
            <x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge>
        </div>

        <x-card title="Customer">
            <p class="text-sm text-gray-700">{{ $order->customer->name }}</p>
            <p class="text-xs text-gray-400">{{ $order->customer->email }}</p>
        </x-card>

        @foreach ($order->vendorOrders as $vendorOrder)
            <x-card :title="'#'.$vendorOrder->vendor_order_number.' — '.$vendorOrder->vendor->name">
                <x-slot:action>
                    <x-badge :color="$vendorOrder->status->badgeColor()">{{ $vendorOrder->status->label() }}</x-badge>
                </x-slot:action>

                <div class="divide-y divide-gray-50">
                    @foreach ($vendorOrder->items as $item)
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $item->product_title }}</p>
                                <p class="text-xs text-gray-400">Qty {{ $item->quantity }} · ${{ number_format($item->unit_price, 2) }} each</p>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</span>
                        </div>
                    @endforeach
                </div>

                @if ($vendorOrder->tracking_number)
                    <p class="text-xs text-gray-400 mt-3">Tracking: {{ $vendorOrder->tracking_number }}</p>
                @endif
            </x-card>
        @endforeach

        <x-card title="Shipping Address">
            @if ($order->shippingAddress)
                <p class="text-sm text-gray-700">{{ $order->shippingAddress->name }} · {{ $order->shippingAddress->phone }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $order->shippingAddress->oneLine() }}</p>
            @endif
        </x-card>

        <x-card title="Summary">
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500"><span>Subtotal</span><span>${{ number_format($order->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-gray-500"><span>Shipping</span><span>${{ number_format($order->shipping_total, 2) }}</span></div>
                <div class="flex justify-between font-bold text-gray-900 pt-2 border-t border-gray-100 mt-2"><span>Total</span><span>${{ number_format($order->total, 2) }}</span></div>
            </div>
        </x-card>
    </div>
@endsection
