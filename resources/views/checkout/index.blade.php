@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Checkout</h1>

        @error('checkout')
            <x-alert type="error" class="mb-6">{{ $message }}</x-alert>
        @enderror

        @if (empty($summary['groups']))
            <x-alert type="info">Your cart is empty. <a href="{{ route('shop') }}" class="underline">Browse the marketplace</a>.</x-alert>
        @else
            <form method="POST" action="{{ route('checkout.store') }}">
                @csrf
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <x-card title="Shipping Address">
                            @if ($addresses->isNotEmpty())
                                <div class="space-y-2 mb-4">
                                    @foreach ($addresses as $address)
                                        <label class="flex items-start gap-3 border border-gray-100 rounded-md p-3 cursor-pointer">
                                            <input type="radio" name="shipping_address_id" value="{{ $address->id }}" class="mt-1 accent-brand-500" @checked($loop->first)>
                                            <span class="text-sm text-gray-700">
                                                <strong>{{ $address->name }}</strong> · {{ $address->phone }}<br>
                                                {{ $address->oneLine() }}
                                            </span>
                                        </label>
                                    @endforeach
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="radio" name="shipping_address_id" value="" class="accent-brand-500">
                                        Use a new address
                                    </label>
                                </div>
                            @endif

                            <div class="grid sm:grid-cols-2 gap-4">
                                <x-input label="Full name" name="shipping[name]" type="text" />
                                <x-input label="Phone" name="shipping[phone]" type="tel" />
                                <x-input label="Address line 1" name="shipping[line1]" type="text" class="sm:col-span-2" />
                                <x-input label="Address line 2 (optional)" name="shipping[line2]" type="text" class="sm:col-span-2" />
                                <x-input label="City" name="shipping[city]" type="text" />
                                <x-input label="State/Area (optional)" name="shipping[state]" type="text" />
                                <x-input label="Country" name="shipping[country]" type="text" />
                                <x-input label="Postal code (optional)" name="shipping[postal_code]" type="text" />
                            </div>
                        </x-card>

                        <x-card title="Payment">
                            @error('payment_method')
                                <x-alert type="error" class="mb-4">{{ $message }}</x-alert>
                            @enderror

                            @if (empty($paymentMethods))
                                <p class="text-sm text-gray-500">No payment method is available for this order yet. Please contact support.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach ($paymentMethods as $method)
                                        <label class="flex items-center gap-2 text-sm text-gray-700 border border-gray-100 rounded-md p-3 cursor-pointer">
                                            <input type="radio" name="payment_method" value="{{ $method->value }}" class="accent-brand-500" @checked($loop->first)>
                                            {{ $method->label() }}
                                        </label>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-xs text-gray-400 mt-3">Card payments (Stripe/PayPal) will appear here automatically once the platform gateway is configured.</p>
                        </x-card>
                    </div>

                    <div>
                        <div class="bg-white border border-gray-100 rounded-md p-6 sticky top-20">
                            <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                            @foreach ($summary['groups'] as $group)
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>{{ $group['seller']->name }} ({{ $group['route'] === 'openbox' ? 'Openbox' : 'Seller' }} payment)</span>
                                    <span>${{ number_format($group['subtotal'] + $group['shipping'], 2) }}</span>
                                </div>
                            @endforeach

                            <hr class="border-gray-100 my-4">

                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Subtotal</span>
                                <span>${{ number_format($summary['subtotal'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500 mb-4">
                                <span>Shipping</span>
                                <span>${{ number_format($summary['shipping'], 2) }}</span>
                            </div>

                            <div class="flex justify-between text-base font-bold text-gray-900 mb-6">
                                <span>Total</span>
                                <span>${{ number_format($summary['total'], 2) }}</span>
                            </div>

                            <x-button type="submit" class="w-full justify-center" :disabled="empty($paymentMethods)">Place Order</x-button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
