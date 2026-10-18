@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>


        @if (session('error'))
            <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
        @endif

        @error('checkout')
            <x-alert type="error" class="mb-6">{{ $message }}</x-alert>
        @enderror

        @if ($errors->any() && !$errors->has('checkout'))
            <x-alert type="error" class="mb-6">
                <p class="font-semibold mb-1">Please review the form for the following issues:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if (empty($summary['groups']))
            <x-alert type="info">Your cart is empty. <a href="{{ route('shop') }}" class="underline">Browse the marketplace</a>.</x-alert>
        @else
            <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
                @csrf
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
        @if ($isGuest)
            {{-- Guests can fill in the form, but must sign in or register to place the order. --}}
            <div class="grid md:grid-cols-2 gap-4">
                {{-- New customer: always the selected option --}}
                <div class="rounded-2xl border-2 border-brand-400 bg-white p-5 ring-4 ring-brand-100/70">
                    <div class="flex items-start gap-4">
                        <span class="w-12 h-12 shrink-0 rounded-full bg-brand-100 text-gray-900 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900">New to {{ config('app.name') }}? <a href="{{ route('register') }}" class="font-extrabold text-gray-950 underline decoration-2 decoration-brand-500 underline-offset-4 hover:decoration-gray-950 transition-colors">Create Account</a></h2>
                            <p class="text-sm text-gray-500 mt-0.5">Create an account to place your order and track it.</p>
                        </div>
                    </div>
                </div>

                {{-- Returning customer --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-start gap-4">
                        <span class="w-12 h-12 shrink-0 rounded-full bg-brand-100 text-gray-900 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0v1H4v-1z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-gray-900">Returning customer? <a href="{{ route('login') }}" class="font-extrabold text-gray-950 underline decoration-2 decoration-brand-500 underline-offset-4 hover:decoration-gray-950 transition-colors">Sign in</a></h2>
                            <p class="text-sm text-gray-500 mt-0.5">Sign in to place your order with your saved details.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

                        @php
                            $hasAddresses = $addresses->isNotEmpty();
                            $defaultAddressId = $hasAddresses ? (string)$addresses->first()->id : 'new';
                            if (old('shipping_address_id') !== null && old('shipping_address_id') !== '' && old('shipping_address_id') !== 'new') {
                                $defaultAddressId = (string)old('shipping_address_id');
                            } elseif (old('shipping_address_id') === 'new' || old('shipping.line1') !== null) {
                                $defaultAddressId = 'new';
                            }
                            $selectedMethod = old('payment_method', $paymentMethods[0]->value ?? 'cod');
                        @endphp

                        <x-card title="Shipping Address">
                            @if ($hasAddresses)
                                <div class="space-y-3 mb-4" id="shipping-addresses-container">
                                     @foreach ($addresses as $address)
                                        @php
                                            $isSelected = (string)$address->id === (string)$defaultAddressId;
                                        @endphp
                                        <div id="address-card-{{ $address->id }}" class="address-option-label relative flex items-start justify-between gap-3 border rounded-xl p-4 transition-all duration-150 {{ $isSelected ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/10' : 'border-gray-200 hover:border-gray-300' }}">
                                            <label class="flex items-start gap-3 flex-1 min-w-0 cursor-pointer">
                                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" 
                                                       class="shipping-address-radio mt-1 accent-amber-500"
                                                       @checked($isSelected)>
                                                <div class="flex-1 text-sm text-gray-700 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        @if($address->label)
                                                            <span id="address-label-badge-{{ $address->id }}" class="text-xs font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md">{{ $address->label }}</span>
                                                        @endif
                                                        <strong id="address-name-{{ $address->id }}" class="text-gray-900 font-semibold">{{ $address->name }}</strong>
                                                        @if($address->is_default)
                                                            <span id="address-primary-badge-{{ $address->id }}" class="text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300">PRIMARY</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-gray-600 block mt-1"><span class="font-semibold text-gray-700">Phone:</span> <span id="address-phone-{{ $address->id }}">{{ $address->phone }}</span></span>
                                                    <span class="text-xs text-gray-600 block mt-0.5"><span class="font-semibold text-gray-700">Address:</span> <span id="address-oneline-{{ $address->id }}">{{ $address->oneLine() }}</span></span>
                                                </div>
                                            </label>

                                            <div class="shrink-0 flex items-center pt-0.5">
                                                <button type="button" 
                                                        onclick="openEditAddressModal({{ $address->id }})"
                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-800 hover:text-amber-950 bg-amber-50 hover:bg-amber-100 border border-amber-200/90 px-2.5 py-1.5 rounded-lg transition-colors shadow-2xs group"
                                                        title="Edit Address">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 group-hover:text-amber-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>Edit</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    @php
                                        $isNewSelected = $defaultAddressId === 'new';
                                    @endphp
                                    <label class="address-option-label flex items-center gap-3 border border-dashed rounded-xl p-4 cursor-pointer transition-all duration-150 hover:bg-gray-50 {{ $isNewSelected ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/10' : 'border-gray-300' }}">
                                        <input type="radio" name="shipping_address_id" value="new" 
                                               class="shipping-address-radio accent-amber-500"
                                               @checked($isNewSelected)>
                                        <span class="text-sm font-semibold text-gray-800">+ Use a new address</span>
                                    </label>
                                </div>
                            @endif

                            <div id="new-address-fields" class="grid sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100 {{ (!$hasAddresses || $defaultAddressId === 'new') ? '' : 'hidden' }}">
                                <x-input label="Address Label (optional)" name="shipping[label]" type="text" placeholder="Home, Office, Warehouse..." :value="old('shipping.label')" />
                                <x-input label="Full name" name="shipping[name]" type="text" placeholder="Enter your full name" :value="old('shipping.name', Auth::user()?->name)" />
                                <x-input label="Phone" name="shipping[phone]" type="tel" placeholder="e.g. +880 1700-000000" :value="old('shipping.phone', Auth::user()?->phone)" />
                                <x-input label="Address line 1" name="shipping[line1]" type="text" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('shipping.line1')" />
                                <x-input label="Address line 2 (optional)" name="shipping[line2]" type="text" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('shipping.line2')" />
                                <x-select label="Country" name="shipping[country]" id="checkout_country_select" placeholder="Select Country" :selected="old('shipping.country', 'United States')" />
                                <x-select label="State/Area (optional)" name="shipping[state]" id="checkout_state_select" placeholder="Select State/Area" :selected="old('shipping.state')" />
                                <x-select label="City" name="shipping[city]" id="checkout_city_select" placeholder="Select City" :selected="old('shipping.city')" />
                                <x-input label="Postal code (optional)" name="shipping[postal_code]" type="text" placeholder="e.g. 1207" :value="old('shipping.postal_code')" />
                                
                                <div class="sm:col-span-2 pt-1">
                                    <x-checkbox name="shipping[is_default]" :checked="!$hasAddresses">Save and set as primary address</x-checkbox>
                                </div>
                            </div>
                        </x-card>

                        <x-card title="Payment">
                            @error('payment_method')
                                <x-alert type="error" class="mb-4">{{ $message }}</x-alert>
                            @enderror

                            @if (empty($paymentMethods))
                                <p class="text-sm text-gray-500">No payment method is available for this order yet. Please contact support.</p>
                            @else
                                <div class="space-y-3" id="payment-methods-container">
                                    @foreach ($paymentMethods as $method)
                                        @php
                                            $isMethodSelected = $selectedMethod === $method->value;
                                        @endphp
                                        <div class="payment-method-card border rounded-lg overflow-hidden transition-all duration-150 {{ $isMethodSelected ? 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/10' : 'border-gray-200' }}">
                                            <label class="flex items-center gap-3 p-4 cursor-pointer">
                                                <input type="radio" name="payment_method" value="{{ $method->value }}" 
                                                    class="payment-method-radio accent-brand-500" @checked($isMethodSelected)>
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
                                                <div id="stripe-info-box" class="p-4 bg-indigo-50/50 border-t border-indigo-100 space-y-2 {{ $isMethodSelected ? '' : 'hidden' }}">
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

                            <x-button type="submit" id="place-order-btn" class="w-full justify-center gap-2" :disabled="empty($paymentMethods)">
                                <svg id="stripe-btn-icon" class="w-4 h-4 {{ $selectedMethod === 'stripe' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span id="place-order-btn-text">{{ $selectedMethod === 'stripe' ? 'Proceed to Stripe Payment' : 'Place Order' }}</span>
                            </x-button>
                        </div>
                    </div>
                </div>
            </form>
        @endif

        <!-- Edit Address Modal Popup -->
        <dialog id="checkout-edit-address-modal" class="rounded-2xl p-0 backdrop:bg-gray-900/50 backdrop:backdrop-blur-xs max-w-xl w-full border-0 shadow-2xl">
            <div class="p-6 bg-white rounded-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Edit Shipping Address</h3>
                        <p class="text-xs text-gray-500">Update recipient details and delivery location.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('checkout-edit-address-modal').close()" class="text-gray-400 hover:text-gray-600 text-xl font-bold px-2">✕</button>
                </div>

                <form id="checkout-edit-address-form" method="POST" action="" class="space-y-4" onsubmit="handleCheckoutAddressUpdate(event)">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="edit_modal_address_id" value="">

                    <div id="edit-address-error" class="hidden p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg"></div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-input label="Address Label (optional)" name="label" id="edit_modal_label" placeholder="Home, Office, Warehouse..." />
                        <x-input label="Full Name" name="name" id="edit_modal_name" placeholder="Full recipient name" />
                        <x-input label="Phone" name="phone" id="edit_modal_phone" placeholder="e.g. +880 1700-000000" />
                        <x-input label="Address line 1" name="line1" id="edit_modal_line1" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" />
                        <x-input label="Address line 2 (optional)" name="line2" id="edit_modal_line2" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" />
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Country</label>
                            <select name="country" id="edit_modal_country" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">State/Area (optional)</label>
                            <select name="state" id="edit_modal_state" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">City</label>
                            <select name="city" id="edit_modal_city" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <x-input label="Postal code (optional)" name="postal_code" id="edit_modal_postal_code" placeholder="e.g. 1207" />
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer select-none">
                            <input type="checkbox" name="is_default" id="edit_modal_is_default" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                            <span>Set as primary shipping address</span>
                        </label>
                    </div>

                    <div class="pt-3 flex justify-end gap-3 border-t border-gray-100">
                        <x-button type="button" variant="secondary" onclick="document.getElementById('checkout-edit-address-modal').close()">Cancel</x-button>
                        <x-button type="submit" id="edit-address-submit-btn">Save Changes</x-button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addressRadios = document.querySelectorAll('.shipping-address-radio');
            const newAddressFields = document.getElementById('new-address-fields');

            function updateAddressVisibility() {
                const selectedRadio = document.querySelector('.shipping-address-radio:checked');
                const selectedValue = selectedRadio ? selectedRadio.value : 'new';

                if (newAddressFields) {
                    if (selectedValue === 'new' || !addressRadios.length) {
                        newAddressFields.classList.remove('hidden');
                    } else {
                        newAddressFields.classList.add('hidden');
                    }
                }

                addressRadios.forEach(radio => {
                    const label = radio.closest('.address-option-label');
                    if (!label) return;
                    const isDashed = label.classList.contains('border-dashed');

                    if (radio.checked) {
                        label.classList.add('border-amber-400', 'ring-2', 'ring-amber-400/20', 'bg-amber-50/10');
                        label.classList.remove('border-gray-200', 'border-gray-300');
                    } else {
                        label.classList.remove('border-amber-400', 'ring-2', 'ring-amber-400/20', 'bg-amber-50/10');
                        label.classList.add(isDashed ? 'border-gray-300' : 'border-gray-200');
                    }
                });
            }

            addressRadios.forEach(radio => {
                radio.addEventListener('change', updateAddressVisibility);
            });

            const paymentRadios = document.querySelectorAll('.payment-method-radio');
            const stripeInfoBox = document.getElementById('stripe-info-box');
            const stripeBtnIcon = document.getElementById('stripe-btn-icon');
            const placeOrderBtnText = document.getElementById('place-order-btn-text');

            function updatePaymentMethod() {
                const selectedRadio = document.querySelector('.payment-method-radio:checked');
                const selectedValue = selectedRadio ? selectedRadio.value : '';

                paymentRadios.forEach(radio => {
                    const card = radio.closest('.payment-method-card');
                    if (!card) return;

                    if (radio.checked) {
                        card.classList.add('border-brand-500', 'ring-1', 'ring-brand-500', 'bg-brand-50/10');
                        card.classList.remove('border-gray-200');
                    } else {
                        card.classList.remove('border-brand-500', 'ring-1', 'ring-brand-500', 'bg-brand-50/10');
                        card.classList.add('border-gray-200');
                    }
                });

                if (stripeInfoBox) {
                    if (selectedValue === 'stripe') {
                        stripeInfoBox.classList.remove('hidden');
                    } else {
                        stripeInfoBox.classList.add('hidden');
                    }
                }

                if (stripeBtnIcon && placeOrderBtnText) {
                    if (selectedValue === 'stripe') {
                        stripeBtnIcon.classList.remove('hidden');
                        placeOrderBtnText.textContent = 'Proceed to Stripe Payment';
                    } else {
                        stripeBtnIcon.classList.add('hidden');
                        placeOrderBtnText.textContent = 'Place Order';
                    }
                }
            }

            paymentRadios.forEach(radio => {
                radio.addEventListener('change', updatePaymentMethod);
            });

            updateAddressVisibility();
            updatePaymentMethod();
        });
    </script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Tom Select copies the select's border/padding classes onto its wrapper; keep a single border on the inner control. */
        .ts-wrapper {
            border: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .ts-control {
            border-radius: 0.375rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
        }
        .ts-control.focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4) !important;
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        .ts-dropdown .dropdown-input-wrap {
            padding: 0.5rem !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .ts-dropdown .dropdown-input-wrap .dropdown-input {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.375rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            box-shadow: none !important;
        }
        .ts-dropdown .dropdown-input-wrap .dropdown-input:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.4) !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: #fffbeb !important;
            color: #d97706 !important;
        }
        dialog::backdrop {
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fallbackCountries = [
                {
                    country: 'Bangladesh',
                    cities: ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Comilla', 'Narayanganj', 'Gazipur', 'Bogra', 'Mymensingh', 'Cox\'s Bazar', 'Feni', 'Noakhali', 'Jashore', 'Dinajpur'],
                    states: ['Dhaka Division', 'Chittagong Division', 'Sylhet Division', 'Rajshahi Division', 'Khulna Division', 'Barisal Division', 'Rangpur Division', 'Mymensingh Division']
                },
                {
                    country: 'United States',
                    cities: ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'San Francisco', 'Columbus', 'Indianapolis', 'Seattle', 'Miami'],
                    states: ['California', 'New York', 'Texas', 'Florida', 'Illinois', 'Pennsylvania', 'Ohio', 'Georgia', 'North Carolina', 'Michigan', 'New Jersey', 'Virginia', 'Washington', 'Arizona', 'Massachusetts']
                },
                {
                    country: 'United Kingdom',
                    cities: ['London', 'Birmingham', 'Glasgow', 'Manchester', 'Liverpool', 'Bristol', 'Edinburgh', 'Leeds', 'Sheffield', 'Newcastle', 'Belfast', 'Cardiff', 'Nottingham', 'Leicester'],
                    states: ['England', 'Scotland', 'Wales', 'Northern Ireland']
                },
                {
                    country: 'Canada',
                    cities: ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener'],
                    states: ['Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba', 'Saskatchewan', 'Nova Scotia', 'New Brunswick']
                },
                {
                    country: 'Australia',
                    cities: ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Canberra', 'Newcastle', 'Hobart'],
                    states: ['New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia', 'Tasmania', 'Australian Capital Territory']
                },
                {
                    country: 'India',
                    cities: ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad', 'Chennai', 'Kolkata', 'Surat', 'Pune', 'Jaipur', 'Lucknow', 'Kanpur'],
                    states: ['Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Gujarat', 'Tamil Nadu', 'West Bengal', 'Rajasthan', 'Uttar Pradesh', 'Kerala']
                },
                {
                    country: 'United Arab Emirates',
                    cities: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman', 'Ras Al Khaimah', 'Fujairah'],
                    states: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain']
                },
                {
                    country: 'Saudi Arabia',
                    cities: ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Tabuk', 'Abha'],
                    states: ['Riyadh Region', 'Makkah Region', 'Eastern Province', 'Madinah Region', 'Asir Region']
                },
                {
                    country: 'Pakistan',
                    cities: ['Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi', 'Gujranwala', 'Peshawar', 'Multan', 'Islamabad', 'Quetta'],
                    states: ['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory']
                }
            ];

            let countriesData = JSON.parse(JSON.stringify(fallbackCountries));
            let statesData = fallbackCountries.map(c => ({ name: c.country, states: c.states }));

            const initialCountry = @json(old('shipping.country', 'United States'));
            const initialCity = @json(old('shipping.city', ''));
            const initialState = @json(old('shipping.state', ''));

            const countrySelect = document.getElementById('checkout_country_select');
            const citySelect = document.getElementById('checkout_city_select');
            const stateSelect = document.getElementById('checkout_state_select');

            let countryTomSelect = null;
            let cityTomSelect = null;
            let stateTomSelect = null;

            function initTomSelectInstances() {
                if (typeof TomSelect === 'undefined') return;

                if (countrySelect && !countryTomSelect) {
                    countryTomSelect = new TomSelect(countrySelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select country...',
                        allowEmptyOption: true,
                        onChange: function(val) {
                            populateCitiesAndStates(val, '', '');
                        }
                    });
                }

                if (citySelect && !cityTomSelect) {
                    cityTomSelect = new TomSelect(citySelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select city...',
                        allowEmptyOption: true,
                    });
                }

                if (stateSelect && !stateTomSelect) {
                    stateTomSelect = new TomSelect(stateSelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select state/area...',
                        allowEmptyOption: true,
                    });
                }
            }

            function populateCountries(selectedCountryName) {
                if (!countrySelect) return;

                // Sync native select element options
                countrySelect.innerHTML = '';
                const defaultCountryOpt = document.createElement('option');
                defaultCountryOpt.value = '';
                defaultCountryOpt.textContent = 'Search & select country...';
                countrySelect.appendChild(defaultCountryOpt);

                countriesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                        opt.selected = true;
                    }
                    countrySelect.appendChild(opt);
                });

                if (countryTomSelect) {
                    countryTomSelect.clearOptions();
                    countryTomSelect.addOption({ value: '', text: 'Search & select country...' });

                    let matchFound = false;
                    countriesData.forEach(item => {
                        countryTomSelect.addOption({ value: item.country, text: item.country });
                        if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                            matchFound = true;
                            selectedCountryName = item.country;
                        }
                    });

                    if (selectedCountryName && !matchFound) {
                        countryTomSelect.addOption({ value: selectedCountryName, text: selectedCountryName });
                    }

                    countryTomSelect.refreshOptions(false);
                    countryTomSelect.setValue(selectedCountryName || '', true);
                }
            }

            function populateCitiesAndStates(countryName, selectedCityName, selectedStateName) {
                if (citySelect) {
                    const countryObj = countryName ? countriesData.find(item => item.country.toLowerCase() === countryName.toLowerCase()) : null;
                    const cities = countryObj ? (countryObj.cities || []) : [];

                    // Sync native select options
                    citySelect.innerHTML = '';
                    const defaultCityOpt = document.createElement('option');
                    defaultCityOpt.value = '';
                    defaultCityOpt.textContent = countryName ? 'Search & select city...' : 'Select a country first...';
                    citySelect.appendChild(defaultCityOpt);

                    cities.forEach(cityName => {
                        const opt = document.createElement('option');
                        opt.value = cityName;
                        opt.textContent = cityName;
                        if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                            opt.selected = true;
                        }
                        citySelect.appendChild(opt);
                    });

                    if (cityTomSelect) {
                        cityTomSelect.clearOptions();
                        cityTomSelect.addOption({ value: '', text: countryName ? 'Search & select city...' : 'Select a country first...' });

                        let matchFound = false;
                        cities.forEach(cityName => {
                            cityTomSelect.addOption({ value: cityName, text: cityName });
                            if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                                matchFound = true;
                                selectedCityName = cityName;
                            }
                        });

                        if (selectedCityName && !matchFound) {
                            cityTomSelect.addOption({ value: selectedCityName, text: selectedCityName });
                        }

                        cityTomSelect.refreshOptions(false);
                        cityTomSelect.setValue(selectedCityName || '', true);
                    }
                }

                if (stateSelect) {
                    const stateObj = countryName ? statesData.find(item => (item.name || item.country || '').toLowerCase() === countryName.toLowerCase()) : null;
                    const states = stateObj ? (stateObj.states || []) : [];

                    // Sync native select options
                    stateSelect.innerHTML = '';
                    const defaultStateOpt = document.createElement('option');
                    defaultStateOpt.value = '';
                    defaultStateOpt.textContent = countryName ? 'Search & select state/area...' : 'Select a country first...';
                    stateSelect.appendChild(defaultStateOpt);

                    states.forEach(st => {
                        const sName = typeof st === 'string' ? st : (st.name || st);
                        const opt = document.createElement('option');
                        opt.value = sName;
                        opt.textContent = sName;
                        if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                            opt.selected = true;
                        }
                        stateSelect.appendChild(opt);
                    });

                    if (stateTomSelect) {
                        stateTomSelect.clearOptions();
                        stateTomSelect.addOption({ value: '', text: countryName ? 'Search & select state/area...' : 'Select a country first...' });

                        let matchFound = false;
                        states.forEach(st => {
                            const sName = typeof st === 'string' ? st : (st.name || st);
                            stateTomSelect.addOption({ value: sName, text: sName });
                            if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                                matchFound = true;
                                selectedStateName = sName;
                            }
                        });

                        if (selectedStateName && !matchFound) {
                            stateTomSelect.addOption({ value: selectedStateName, text: selectedStateName });
                        }

                        stateTomSelect.refreshOptions(false);
                        stateTomSelect.setValue(selectedStateName || '', true);
                    }
                }
            }

            // Initialize IMMEDIATELY on load
            initTomSelectInstances();
            populateCountries(initialCountry);
            populateCitiesAndStates(initialCountry, initialCity, initialState);

            // Asynchronously fetch full database in background
            async function fetchFullDatabaseInBackground() {
                try {
                    const [resCountries, resStates] = await Promise.all([
                        fetch('https://countriesnow.space/api/v0.1/countries'),
                        fetch('https://countriesnow.space/api/v0.1/countries/states')
                    ]);

                    const jsonCountries = await resCountries.json();
                    const jsonStates = await resStates.json();

                    if (jsonCountries && jsonCountries.data && jsonCountries.data.length > 0) {
                        countriesData = jsonCountries.data;
                    }

                    if (jsonStates && jsonStates.data && jsonStates.data.length > 0) {
                        statesData = jsonStates.data;
                    }

                    const currentSelectedCountry = countryTomSelect ? countryTomSelect.getValue() : initialCountry;
                    const currentSelectedCity = cityTomSelect ? cityTomSelect.getValue() : initialCity;
                    const currentSelectedState = stateTomSelect ? stateTomSelect.getValue() : initialState;

                    populateCountries(currentSelectedCountry);
                    populateCitiesAndStates(currentSelectedCountry, currentSelectedCity, currentSelectedState);
                } catch (err) {
                    // Silently retain fallback data
                }
            }

            fetchFullDatabaseInBackground();

            // Checkout Address Editing Logic
            const savedCheckoutAddresses = @json($addresses->keyBy('id'));

            window.populateEditModalGeographic = function(selectedCountry, selectedState, selectedCity) {
                const countryEl = document.getElementById('edit_modal_country');
                const stateEl = document.getElementById('edit_modal_state');
                const cityEl = document.getElementById('edit_modal_city');
                if (!countryEl || !stateEl || !cityEl) return;

                const cList = (countriesData && countriesData.length > 0) ? countriesData : fallbackCountries;

                countryEl.innerHTML = '<option value="">Select Country</option>';
                let countryFound = false;
                cList.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountry && item.country.toLowerCase() === selectedCountry.toLowerCase()) {
                        opt.selected = true;
                        countryFound = true;
                    }
                    countryEl.appendChild(opt);
                });

                if (selectedCountry && !countryFound) {
                    const opt = document.createElement('option');
                    opt.value = selectedCountry;
                    opt.textContent = selectedCountry;
                    opt.selected = true;
                    countryEl.appendChild(opt);
                }

                function syncModalStateAndCity() {
                    const cVal = countryEl.value;
                    const countryObj = cVal ? cList.find(item => item.country.toLowerCase() === cVal.toLowerCase()) : null;

                    // States
                    stateEl.innerHTML = '<option value="">Select State/Area (optional)</option>';
                    const sList = countryObj ? (countryObj.states || []) : [];
                    let stateFound = false;
                    sList.forEach(st => {
                        const sName = typeof st === 'string' ? st : (st.name || st);
                        const opt = document.createElement('option');
                        opt.value = sName;
                        opt.textContent = sName;
                        if (selectedState && sName.toLowerCase() === selectedState.toLowerCase()) {
                            opt.selected = true;
                            stateFound = true;
                        }
                        stateEl.appendChild(opt);
                    });
                    if (selectedState && !stateFound) {
                        const opt = document.createElement('option');
                        opt.value = selectedState;
                        opt.textContent = selectedState;
                        opt.selected = true;
                        stateEl.appendChild(opt);
                    }

                    // Cities
                    cityEl.innerHTML = '<option value="">Select City</option>';
                    const ctList = countryObj ? (countryObj.cities || []) : [];
                    let cityFound = false;
                    ctList.forEach(ct => {
                        const opt = document.createElement('option');
                        opt.value = ct;
                        opt.textContent = ct;
                        if (selectedCity && ct.toLowerCase() === selectedCity.toLowerCase()) {
                            opt.selected = true;
                            cityFound = true;
                        }
                        cityEl.appendChild(opt);
                    });
                    if (selectedCity && !cityFound) {
                        const opt = document.createElement('option');
                        opt.value = selectedCity;
                        opt.textContent = selectedCity;
                        opt.selected = true;
                        cityEl.appendChild(opt);
                    }
                }

                countryEl.onchange = function() {
                    selectedState = '';
                    selectedCity = '';
                    syncModalStateAndCity();
                };

                syncModalStateAndCity();
            };

            window.openEditAddressModal = function(addressId) {
                const addr = savedCheckoutAddresses[addressId];
                if (!addr) return;

                const modal = document.getElementById('checkout-edit-address-modal');
                const form = document.getElementById('checkout-edit-address-form');
                const errBox = document.getElementById('edit-address-error');
                if (errBox) {
                    errBox.classList.add('hidden');
                    errBox.innerHTML = '';
                }

                form.action = `/account/addresses/${addressId}`;
                document.getElementById('edit_modal_address_id').value = addressId;
                document.getElementById('edit_modal_label').value = addr.label || '';
                document.getElementById('edit_modal_name').value = addr.name || '';
                document.getElementById('edit_modal_phone').value = addr.phone || '';
                document.getElementById('edit_modal_line1').value = addr.line1 || '';
                document.getElementById('edit_modal_line2').value = addr.line2 || '';
                document.getElementById('edit_modal_postal_code').value = addr.postal_code || '';
                document.getElementById('edit_modal_is_default').checked = !!addr.is_default;

                populateEditModalGeographic(addr.country, addr.state, addr.city);

                modal.showModal();
            };

            window.handleCheckoutAddressUpdate = async function(e) {
                e.preventDefault();
                const form = e.target;
                const btn = document.getElementById('edit-address-submit-btn');
                const errBox = document.getElementById('edit-address-error');
                const addressId = document.getElementById('edit_modal_address_id').value;

                if (errBox) {
                    errBox.classList.add('hidden');
                    errBox.innerHTML = '';
                }
                btn.disabled = true;
                btn.textContent = 'Saving...';

                const formData = new FormData(form);

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        let msg = data.message || 'Validation failed. Please check the fields.';
                        if (data.errors) {
                            const list = Object.values(data.errors).flat().join('<br>');
                            msg += `<div class="mt-1 font-normal">${list}</div>`;
                        }
                        if (errBox) {
                            errBox.innerHTML = msg;
                            errBox.classList.remove('hidden');
                        }
                        return;
                    }

                    const addr = data.address;
                    if (addr) {
                        savedCheckoutAddresses[addr.id] = addr;

                        const nameEl = document.getElementById(`address-name-${addr.id}`);
                        if (nameEl) nameEl.textContent = addr.name;

                        const phoneEl = document.getElementById(`address-phone-${addr.id}`);
                        if (phoneEl) phoneEl.textContent = addr.phone;

                        const onelineEl = document.getElementById(`address-oneline-${addr.id}`);
                        if (onelineEl) onelineEl.textContent = addr.one_line;

                        let badgeEl = document.getElementById(`address-label-badge-${addr.id}`);
                        if (addr.label) {
                            if (badgeEl) {
                                badgeEl.textContent = addr.label;
                            } else if (nameEl && nameEl.parentElement) {
                                const badge = document.createElement('span');
                                badge.id = `address-label-badge-${addr.id}`;
                                badge.className = 'text-xs font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md';
                                badge.textContent = addr.label;
                                nameEl.parentElement.prepend(badge);
                            }
                        } else if (badgeEl) {
                            badgeEl.remove();
                        }

                        if (addr.is_default) {
                            document.querySelectorAll('[id^="address-primary-badge-"]').forEach(el => el.remove());
                            if (nameEl && nameEl.parentElement) {
                                const primarySpan = document.createElement('span');
                                primarySpan.id = `address-primary-badge-${addr.id}`;
                                primarySpan.className = 'text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300';
                                primarySpan.textContent = 'PRIMARY';
                                nameEl.parentElement.appendChild(primarySpan);
                            }
                        }
                    }

                    document.getElementById('checkout-edit-address-modal').close();
                    if (window.showToast) {
                        window.showToast('Shipping address updated successfully.', 'success');
                    }
                } catch (err) {
                    console.error('Error updating address:', err);
                    form.submit();
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Save Changes';
                }
            };
        });
    </script>
@endsection
