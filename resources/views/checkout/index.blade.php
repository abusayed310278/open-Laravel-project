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
            <form method="POST" action="{{ route('checkout.store') }}" x-data="{ selectedMethod: '{{ old('payment_method', $paymentMethods[0]->value ?? 'cod') }}' }">
                @csrf
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        @php
                            $hasAddresses = $addresses->isNotEmpty();
                            $defaultAddressId = $hasAddresses ? (string)$addresses->first()->id : 'new';
                            if (old('shipping_address_id') !== null && old('shipping_address_id') !== '' && old('shipping_address_id') !== 'new') {
                                $defaultAddressId = (string)old('shipping_address_id');
                            } elseif (old('shipping_address_id') === 'new' || old('shipping.line1') !== null) {
                                $defaultAddressId = 'new';
                            }
                        @endphp

                        <x-card title="Shipping Address" x-data="{ selectedAddress: '{{ $defaultAddressId }}' }">
                            @if ($hasAddresses)
                                <div class="space-y-3 mb-4">
                                     @foreach ($addresses as $address)
                                        <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-3.5 cursor-pointer transition-all duration-150"
                                               @click="selectedAddress = '{{ $address->id }}'"
                                               :class="{ 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/10': selectedAddress == '{{ $address->id }}' }">
                                            <input type="radio" name="shipping_address_id" value="{{ $address->id }}" 
                                                   x-model="selectedAddress"
                                                   class="mt-1 accent-brand-500">
                                            <div class="flex-1 text-sm text-gray-700">
                                                <div class="flex items-center gap-2">
                                                    <strong class="text-gray-900 font-semibold">{{ $address->name }}</strong>
                                                    @if($address->is_default)
                                                        <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200">DEFAULT</span>
                                                    @endif
                                                </div>
                                                <span class="text-xs text-gray-500 block mt-0.5">{{ $address->phone }}</span>
                                                <span class="text-xs text-gray-600 block mt-1">{{ $address->oneLine() }}</span>
                                            </div>
                                        </label>
                                    @endforeach

                                    <label class="flex items-center gap-3 border border-dashed border-gray-300 rounded-lg p-3.5 cursor-pointer transition-all duration-150 hover:bg-gray-50"
                                           @click="selectedAddress = 'new'"
                                           :class="{ 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/10': selectedAddress === 'new' || selectedAddress === '' }">
                                        <input type="radio" name="shipping_address_id" value="new" 
                                               x-model="selectedAddress"
                                               class="accent-brand-500">
                                        <span class="text-sm font-medium text-gray-800">+ Use a new address</span>
                                    </label>
                                </div>
                            @endif

                            <div x-show="selectedAddress === 'new' || selectedAddress === '' || !{{ $hasAddresses ? 'true' : 'false' }}" x-cloak class="grid sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                                <x-input label="Full name" name="shipping[name]" type="text" placeholder="Enter your full name" :value="old('shipping.name')" />
                                <x-input label="Phone" name="shipping[phone]" type="tel" placeholder="e.g. +880 1700-000000" :value="old('shipping.phone')" />
                                <x-input label="Address line 1" name="shipping[line1]" type="text" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('shipping.line1')" />
                                <x-input label="Address line 2 (optional)" name="shipping[line2]" type="text" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('shipping.line2')" />
                                <x-input label="City" name="shipping[city]" type="text" placeholder="e.g. Dhaka" :value="old('shipping.city')" />
                                <x-input label="State/Area (optional)" name="shipping[state]" type="text" placeholder="e.g. Dhaka Division" :value="old('shipping.state')" />
                                <x-input label="Country" name="shipping[country]" type="text" placeholder="e.g. Bangladesh" :value="old('shipping.country', 'United States')" />
                                <x-input label="Postal code (optional)" name="shipping[postal_code]" type="text" placeholder="e.g. 1207" :value="old('shipping.postal_code')" />
                            </div>
                        </x-card>

                        <x-card title="Payment">
                            @error('payment_method')
                                <x-alert type="error" class="mb-4">{{ $message }}</x-alert>
                            @enderror

                            @if (empty($paymentMethods))
                                <p class="text-sm text-gray-500">No payment method is available for this order yet. Please contact support.</p>
                            @else
                                <div class="space-y-3">
                                    @foreach ($paymentMethods as $method)
                                        <div class="border border-gray-200 rounded-lg overflow-hidden transition-all duration-150" :class="{ 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/10': selectedMethod === '{{ $method->value }}' }">
                                            <label class="flex items-center gap-3 p-4 cursor-pointer">
                                                <input type="radio" name="payment_method" value="{{ $method->value }}" 
                                                    x-model="selectedMethod"
                                                    class="accent-brand-500" @checked($loop->first)>
                                                <div class="flex-1 flex items-center justify-between">
                                                    <div>
                                                        <span class="font-medium text-gray-900 text-sm block">{{ $method->label() }}</span>
                                                        @if($method->value === 'stripe')
                                                            <span class="text-xs text-gray-500">Redirects to Stripe Web Portal for secure card payment</span>
                                                        @elseif($method->value === 'paypal')
                                                            <span class="text-xs text-gray-500">Pay via your PayPal account or card</span>
                                                        @elseif($method->value === 'cod')
                                                            <span class="text-xs text-gray-500">Pay with cash upon delivery</span>
                                                        @elseif($method->value === 'manual_bank')
                                                            <span class="text-xs text-gray-500">Direct wire / bank transfer</span>
                                                        @endif
                                                    </div>
                                                    @if($method->value === 'stripe')
                                                        <div class="flex items-center gap-1.5 opacity-90 shrink-0">
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">Visa</span>
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">MC</span>
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">Amex</span>
                                                        </div>
                                                    @elseif($method->value === 'paypal')
                                                        <span class="text-[10px] font-bold tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase shrink-0">PayPal</span>
                                                    @endif
                                                </div>
                                            </label>

                                            @if($method->value === 'stripe')
                                                <div x-show="selectedMethod === 'stripe'" class="p-4 bg-indigo-50/50 border-t border-indigo-100 space-y-2">
                                                    <div class="text-xs text-indigo-900 font-medium flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        <span>Hosted Stripe Web Portal Gateway</span>
                                                    </div>
                                                    <p class="text-xs text-indigo-700/80 leading-relaxed">
                                                        When you place your order, you will be redirected to the official Stripe Checkout portal (<code class="text-[11px] bg-indigo-100 px-1 py-0.5 rounded text-indigo-800">pay.stripe.com</code>) to complete your card payment with 256-bit SSL encryption.
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-xs text-gray-400 mt-3">Select your preferred payment method above to complete your order.</p>
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

                            <x-button type="submit" class="w-full justify-center gap-2" :disabled="empty($paymentMethods)">
                                <svg x-show="selectedMethod === 'stripe'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span x-text="selectedMethod === 'stripe' ? 'Proceed to Stripe Payment' : 'Place Order'">Place Order</span>
                            </x-button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
