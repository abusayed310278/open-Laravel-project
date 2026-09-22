@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl font-bold text-gray-900 mb-10">Your Cart</h1>

        @session('status')
            <x-alert type="success" class="mb-6">{{ $value }}</x-alert>
        @endsession

        @if (empty($groups))
            <div class="bg-gray-50 border border-gray-100 rounded-md p-16 text-center">
                <p class="text-gray-400 mb-5">Your cart is empty.</p>
                <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
            </div>
        @else
            @php
                $grandTotal = collect($groups)->sum(fn ($g) => $g['subtotal'] + $g['shipping']);
            @endphp

            <div class="grid lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2 space-y-8">
                    @foreach ($groups as $group)
                        <div class="bg-white border border-gray-100 rounded-md overflow-hidden">
                            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-800">Sold by {{ $group['seller']->name }}</span>
                                <x-badge :color="$group['route'] === 'openbox' ? 'amber' : 'blue'">{{ $group['route'] === 'openbox' ? 'Openbox Payment' : 'Seller Payment' }}</x-badge>
                            </div>

                            <div class="divide-y divide-gray-50">
                                @foreach ($group['items'] as $item)
                                    <div class="flex items-center gap-5 px-6 py-5">
                                        <div class="w-16 h-16 rounded-md bg-gray-50 flex-shrink-0 overflow-hidden">
                                            <img src="{{ $item->product->primaryImageUrl() }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('products.show', $item->product) }}" class="text-sm font-medium text-gray-900 hover:text-brand-600 line-clamp-1">{{ $item->product->title }}</a>
                                            <p class="text-xs text-gray-400 mt-1">${{ number_format($item->price, 2) }} each</p>
                                        </div>

                                        <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
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
                    <div class="bg-white border border-gray-100 rounded-md p-7 sticky top-20" x-data="{
                        open: false,
                        activeTab: 'coupon',
                        code: '',
                        appliedCode: null,
                        discountAmount: 0,
                        appliedType: '',
                        error: '',
                        success: '',
                        grandTotal: {{ (float) $grandTotal }},
                        get currentTotal() {
                            let finalAmt = Math.max(0, this.grandTotal - this.discountAmount);
                            return finalAmt.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        },
                        applyCode() {
                            this.error = '';
                            this.success = '';
                            let cleaned = this.code.trim().toUpperCase();
                            if (!cleaned) {
                                this.error = 'Please enter a code.';
                                return;
                            }

                            if (cleaned === 'WELCOME10' || cleaned === 'OPENBOX10' || cleaned === 'PROMO10') {
                                this.appliedCode = cleaned;
                                this.discountAmount = 10.00;
                                this.appliedType = 'Coupon Code';
                                this.success = 'Coupon applied successfully! ($10 off)';
                                this.code = '';
                            } else if (cleaned === 'SAVE20' || cleaned === 'OPENBOX20') {
                                this.appliedCode = cleaned;
                                this.discountAmount = 20.00;
                                this.appliedType = 'Promo Code';
                                this.success = 'Promo code applied! ($20 off)';
                                this.code = '';
                            } else if (cleaned.startsWith('GIFT') || cleaned === 'GIFT50') {
                                this.appliedCode = cleaned;
                                this.discountAmount = 50.00;
                                this.appliedType = 'Gift Card';
                                this.success = 'Gift card applied! ($50 off)';
                                this.code = '';
                            } else {
                                this.appliedCode = cleaned;
                                this.discountAmount = 15.00;
                                this.appliedType = this.activeTab === 'giftcard' ? 'Gift Card' : 'Promo Code';
                                this.success = `${this.appliedType} '${cleaned}' applied! ($15 off)`;
                                this.code = '';
                            }
                        },
                        removeCode() {
                            this.appliedCode = null;
                            this.discountAmount = 0;
                            this.appliedType = '';
                            this.success = '';
                            this.error = '';
                        }
                    }">
                        <h2 class="font-semibold text-gray-900 mb-5">Order Summary</h2>
                        <div class="space-y-2.5 text-sm text-gray-600">
                            @foreach ($groups as $group)
                                <div class="flex justify-between">
                                    <span>{{ $group['seller']->name }}</span>
                                    <span>${{ number_format($group['subtotal'] + $group['shipping'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Promo Code / Coupon Code / Gift Card Section --}}
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between text-xs font-semibold text-gray-700 hover:text-gray-950 transition-colors py-1 group focus:outline-none">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-brand-500 group-hover:text-brand-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <span>Promo Code, Coupon or Gift Card</span>
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open" x-collapse x-cloak class="mt-3 space-y-3">
                                <div class="flex border-b border-gray-100 text-xs font-medium">
                                    <button type="button" @click="activeTab = 'coupon'" :class="activeTab === 'coupon' ? 'border-b-2 border-brand-500 text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-700'" class="pb-1.5 px-2 transition-colors">
                                        Promo / Coupon
                                    </button>
                                    <button type="button" @click="activeTab = 'giftcard'" :class="activeTab === 'giftcard' ? 'border-b-2 border-brand-500 text-brand-600 font-semibold' : 'text-gray-500 hover:text-gray-700'" class="pb-1.5 px-2 transition-colors">
                                        Gift Card
                                    </button>
                                </div>

                                <template x-if="appliedCode">
                                    <div class="flex items-center justify-between p-2.5 bg-emerald-50 border border-emerald-200 rounded-md text-xs text-emerald-800">
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                            <span class="truncate font-semibold"><span x-text="appliedType"></span>: <span class="tracking-wider" x-text="appliedCode"></span></span>
                                            <span class="text-emerald-700 text-[11px] font-bold">(-$<span x-text="discountAmount.toFixed(2)"></span>)</span>
                                        </div>
                                        <button type="button" @click="removeCode()" class="text-emerald-700 hover:text-red-600 font-bold ml-2 shrink-0">Remove</button>
                                    </div>
                                </template>

                                <template x-if="!appliedCode">
                                    <div>
                                        <form @submit.prevent="applyCode()" class="flex gap-2">
                                            <input type="text"
                                                   x-model="code"
                                                   :placeholder="activeTab === 'giftcard' ? 'Enter gift card code' : 'Enter promo or coupon code'"
                                                   class="flex-1 text-xs border border-gray-200 rounded-md px-3 py-2 focus:ring-1 focus:ring-brand-500 focus:border-brand-500 uppercase tracking-wider font-mono bg-white">
                                            <button type="submit" class="px-3 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-md transition-colors shrink-0">
                                                Apply
                                            </button>
                                        </form>

                                        <div x-show="error" x-text="error" class="text-[11px] text-red-500 mt-1"></div>
                                        <div x-show="success" x-text="success" class="text-[11px] text-emerald-600 mt-1"></div>

                                        <div class="mt-2.5 pt-2 border-t border-gray-50 flex items-center gap-1.5 flex-wrap">
                                            <span class="text-[10px] text-gray-400 font-medium">Try:</span>
                                            <button type="button" @click="code = 'WELCOME10'; applyCode()" class="text-[10px] font-mono px-1.5 py-0.5 bg-gray-100 hover:bg-brand-50 hover:text-brand-600 rounded text-gray-600 transition-colors">WELCOME10</button>
                                            <button type="button" @click="code = 'OPENBOX20'; applyCode()" class="text-[10px] font-mono px-1.5 py-0.5 bg-gray-100 hover:bg-brand-50 hover:text-brand-600 rounded text-gray-600 transition-colors">OPENBOX20</button>
                                            <button type="button" @click="code = 'GIFT50'; activeTab = 'giftcard'; applyCode()" class="text-[10px] font-mono px-1.5 py-0.5 bg-gray-100 hover:bg-brand-50 hover:text-brand-600 rounded text-gray-600 transition-colors">GIFT50</button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <hr class="border-gray-100 my-5">

                        <template x-if="appliedCode">
                            <div class="flex justify-between text-xs text-emerald-600 font-semibold mb-3.5">
                                <span>Discount (<span x-text="appliedCode"></span>)</span>
                                <span>-$<span x-text="discountAmount.toFixed(2)"></span></span>
                            </div>
                        </template>

                        <div class="flex justify-between text-base font-bold text-gray-900 mb-7">
                            <span>Total</span>
                            <span>$<span x-text="currentTotal"></span></span>
                        </div>
                        @if (auth()->check() && !auth()->user()->isCustomer())
                            <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-lg text-xs text-amber-800 font-medium flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Purchasing is restricted to Customer accounts. (Logged in as {{ auth()->user()->role->label() }})</span>
                            </div>
                        @else
                            <x-button as="a" :href="route('checkout')" class="w-full justify-center">Proceed to Checkout</x-button>
                        @endif

                        <a href="{{ route('shop') }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 hover:text-gray-950 font-semibold rounded-md text-xs transition-colors mt-3">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
