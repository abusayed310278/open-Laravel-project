@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Your Cart</h1>

        @session('status')
            <x-alert type="success" class="mb-6">{{ $value }}</x-alert>
        @endsession

        @if (empty($groups))
            <div class="bg-gray-50 border border-gray-100 rounded-md p-12 text-center">
                <p class="text-gray-400 mb-4">Your cart is empty.</p>
                <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
            </div>
        @else
            @php
                $grandTotal = collect($groups)->sum(fn ($g) => $g['subtotal'] + $g['shipping']);
            @endphp

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    @foreach ($groups as $group)
                        <div class="bg-white border border-gray-100 rounded-md overflow-hidden">
                            <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-800">Sold by {{ $group['seller']->name }}</span>
                                <x-badge :color="$group['route'] === 'openbox' ? 'amber' : 'blue'">{{ $group['route'] === 'openbox' ? 'Openbox Payment' : 'Seller Payment' }}</x-badge>
                            </div>

                            <div class="divide-y divide-gray-50">
                                @foreach ($group['items'] as $item)
                                    <div class="flex items-center gap-4 px-5 py-4">
                                        <div class="w-16 h-16 rounded-md bg-gray-50 flex-shrink-0 overflow-hidden">
                                            @if ($item->product->images->isNotEmpty())
                                                <img src="{{ $item->product->images->first()->url() }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('products.show', $item->product) }}" class="text-sm font-medium text-gray-900 hover:text-brand-600 line-clamp-1">{{ $item->product->title }}</a>
                                            <p class="text-xs text-gray-400 mt-0.5">${{ number_format($item->price, 2) }} each</p>
                                        </div>

                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-1.5">
                                            @csrf @method('PATCH')
                                            <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" class="qty-btn w-7 h-7 border border-gray-200 rounded-md text-gray-500 hover:bg-gray-50">−</button>
                                            <input class="qty-input w-10 text-center border border-gray-200 rounded-md text-sm py-1" value="{{ $item->quantity }}" disabled>
                                            <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="qty-btn w-7 h-7 border border-gray-200 rounded-md text-gray-500 hover:bg-gray-50">+</button>
                                        </form>

                                        <span class="text-sm font-semibold text-gray-900 w-20 text-right">${{ number_format($item->lineTotal(), 2) }}</span>

                                        <form method="POST" action="{{ route('cart.remove', $item) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-300 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <div class="bg-white border border-gray-100 rounded-md p-6 sticky top-20">
                        <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>
                        <div class="space-y-2 text-sm text-gray-600">
                            @foreach ($groups as $group)
                                <div class="flex justify-between">
                                    <span>{{ $group['seller']->name }}</span>
                                    <span>${{ number_format($group['subtotal'] + $group['shipping'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <hr class="border-gray-100 my-4">
                        <div class="flex justify-between text-base font-bold text-gray-900 mb-6">
                            <span>Total</span>
                            <span>${{ number_format($grandTotal, 2) }}</span>
                        </div>
                        <x-button as="a" :href="route('checkout')" class="w-full justify-center">Proceed to Checkout</x-button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
