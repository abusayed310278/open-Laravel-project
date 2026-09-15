@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Welcome back!</h1>
            <p class="text-sm text-gray-500 mt-1">{{ auth()->user()->name }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $icon = \App\Support\Icons::PATHS;
                $stats = [
                    ['label' => 'Orders', 'value' => $ordersCount, 'icon' => $icon['shopping-bag']],
                    ['label' => 'Wishlist', 'value' => $wishlistCount, 'icon' => $icon['heart']],
                    ['label' => 'Messages', 'value' => $unreadMessagesCount, 'icon' => $icon['chat']],
                    ['label' => 'Reviews', 'value' => $reviewsCount, 'icon' => $icon['star']],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="bg-white border border-gray-100 rounded-xl p-5 text-center">
                    <div class="w-10 h-10 mx-auto rounded-md bg-brand-50 flex items-center justify-center text-brand-500 mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        <x-card title="Recent Orders">
            <div class="divide-y divide-gray-50">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('account.orders.show', $order) }}" class="flex items-center justify-between py-3 hover:opacity-75">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('M j, Y') }}</p>
                        </div>
                        <div class="text-right">
                            <x-badge :color="$order->status->badgeColor()">{{ $order->status->label() }}</x-badge>
                            <p class="text-sm font-bold text-gray-900 mt-1">${{ number_format($order->total, 2) }}</p>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 py-6 text-center">No orders yet.</p>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
