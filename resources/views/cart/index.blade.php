@extends('layouts.app')

@section('title', 'Your Cart')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-2xl font-bold text-gray-900 mb-10">Your Cart</h1>

        @session('status')
            <x-alert type="success" class="mb-6">{{ $value }}</x-alert>
        @endsession

        <div id="cart-empty-state" class="{{ empty($groups) ? '' : 'hidden' }} bg-gray-50 border border-gray-100 rounded-md p-16 text-center">
            <p class="text-gray-400 mb-5">Your cart is empty.</p>
            <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
        </div>

        @if (!empty($groups))
            @php
                $grandTotal = collect($groups)->sum(fn ($g) => $g['subtotal'] + $g['shipping']);
            @endphp

            <div id="cart-content-container" class="grid lg:grid-cols-3 gap-10">
                <div class="lg:col-span-2 space-y-8" id="cart-groups-container">
                    @foreach ($groups as $group)
                        <div id="seller-card-{{ $group['seller']->id }}" class="seller-card bg-white border border-gray-100 rounded-md overflow-hidden">
                            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-800">Sold by {{ $group['seller']->name }}</span>
                                <x-badge :color="$group['route'] === 'openbox' ? 'amber' : 'blue'">{{ $group['route'] === 'openbox' ? 'Openbox Payment' : 'Seller Payment' }}</x-badge>
                            </div>

                            <div class="divide-y divide-gray-50" id="seller-items-{{ $group['seller']->id }}">
                                @foreach ($group['items'] as $item)
                                    <div id="cart-item-row-{{ $item->id }}" data-item-id="{{ $item->id }}" data-seller-id="{{ $group['seller']->id }}" class="flex items-center gap-5 px-6 py-5 transition-opacity duration-200">
                                        <div class="w-16 h-16 rounded-md bg-gray-50 flex-shrink-0 overflow-hidden">
                                            <img src="{{ $item->product->primaryImageUrl() }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover">
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <a href="{{ route('products.show', $item->product) }}" class="text-sm font-medium text-gray-900 hover:text-brand-600 line-clamp-1">{{ $item->product->title }}</a>
                                            <p class="text-xs text-gray-400 mt-1">${{ number_format($item->price, 2) }} each</p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    onclick="updateItemQuantity({{ $item->id }}, -1, '{{ route('cart.update', $item) }}', '{{ route('cart.remove', $item) }}')"
                                                    class="qty-btn w-7 h-7 border border-gray-200 rounded-md text-gray-500 hover:bg-gray-50 flex items-center justify-center transition-colors disabled:opacity-50 select-none">−</button>
                                            <input id="qty-input-{{ $item->id }}"
                                                   class="qty-input w-10 text-center border border-gray-200 rounded-md text-sm py-1 font-medium bg-white"
                                                   value="{{ $item->quantity }}"
                                                   readonly>
                                            <button type="button"
                                                    onclick="updateItemQuantity({{ $item->id }}, 1, '{{ route('cart.update', $item) }}', '{{ route('cart.remove', $item) }}')"
                                                    class="qty-btn w-7 h-7 border border-gray-200 rounded-md text-gray-500 hover:bg-gray-50 flex items-center justify-center transition-colors disabled:opacity-50 select-none">+</button>
                                        </div>

                                        <span id="line-total-{{ $item->id }}" class="text-sm font-semibold text-gray-900 w-20 text-right">${{ number_format($item->lineTotal(), 2) }}</span>

                                        <button type="button"
                                                onclick="removeCartItem({{ $item->id }}, '{{ route('cart.remove', $item) }}')"
                                                class="text-gray-300 hover:text-red-500 transition-colors p-1"
                                                title="Remove item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <div class="bg-white border border-gray-100 rounded-md p-7 sticky top-20">
                        <h2 class="font-semibold text-gray-900 mb-5">Order Summary</h2>
                        <div class="space-y-2.5 text-sm text-gray-600" id="summary-sellers-list">
                            @foreach ($groups as $group)
                                <div id="summary-seller-row-{{ $group['seller']->id }}" class="flex justify-between">
                                    <span>{{ $group['seller']->name }}</span>
                                    <span id="summary-seller-val-{{ $group['seller']->id }}">${{ number_format($group['subtotal'] + $group['shipping'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <hr class="border-gray-100 my-5">

                        <div class="flex justify-between text-base font-bold text-gray-900 mb-7">
                            <span>Total</span>
                            <span class="text-gray-900 font-bold">$<span id="cart-grand-total">{{ number_format($grandTotal, 2) }}</span></span>
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

    <script>
        let isCartBusy = false;

        async function updateItemQuantity(itemId, step, updateUrl, removeUrl) {
            if (isCartBusy) return;
            const input = document.getElementById(`qty-input-${itemId}`);
            if (!input) return;

            const currentQty = parseInt(input.value) || 1;
            const newQty = currentQty + step;

            if (newQty <= 0) {
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    removeCartItem(itemId, removeUrl);
                }
                return;
            }

            const row = document.getElementById(`cart-item-row-${itemId}`);
            const btns = row ? row.querySelectorAll('button') : [];

            isCartBusy = true;
            btns.forEach(b => b.disabled = true);
            if (row) row.classList.add('opacity-50');

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(updateUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ quantity: newQty })
                });

                if (!res.ok) {
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                handleCartResponse(data, itemId, newQty);
            } catch (err) {
                console.error('Cart update error:', err);
                window.location.reload();
            } finally {
                isCartBusy = false;
                btns.forEach(b => b.disabled = false);
                if (row) row.classList.remove('opacity-50');
            }
        }

        async function removeCartItem(itemId, removeUrl) {
            if (isCartBusy) return;
            const row = document.getElementById(`cart-item-row-${itemId}`);
            const btns = row ? row.querySelectorAll('button') : [];

            isCartBusy = true;
            btns.forEach(b => b.disabled = true);
            if (row) row.classList.add('opacity-40');

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(removeUrl, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    }
                });

                if (!res.ok) {
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                handleCartResponse(data, itemId, 0);
                if (window.showToast) {
                    window.showToast('Item removed from cart.', 'info');
                }
            } catch (err) {
                console.error('Cart remove error:', err);
                window.location.reload();
            } finally {
                isCartBusy = false;
            }
        }

        function handleCartResponse(data, itemId, newQty) {
            if (data.cart_empty) {
                const content = document.getElementById('cart-content-container');
                const empty = document.getElementById('cart-empty-state');
                if (content) content.classList.add('hidden');
                if (empty) empty.classList.remove('hidden');
                if (window.updateCartBadge) window.updateCartBadge(0);
                return;
            }

            const row = document.getElementById(`cart-item-row-${itemId}`);
            if (newQty <= 0) {
                if (row) {
                    const sellerId = row.dataset.sellerId;
                    row.remove();

                    // Check if seller container still has any items
                    const sellerItems = document.getElementById(`seller-items-${sellerId}`);
                    if (sellerItems && sellerItems.querySelectorAll('[data-item-id]').length === 0) {
                        const sellerCard = document.getElementById(`seller-card-${sellerId}`);
                        if (sellerCard) sellerCard.remove();

                        const summarySellerRow = document.getElementById(`summary-seller-row-${sellerId}`);
                        if (summarySellerRow) summarySellerRow.remove();
                    }
                }
            } else {
                const input = document.getElementById(`qty-input-${itemId}`);
                if (input) input.value = data.quantity;

                const lineTotal = document.getElementById(`line-total-${itemId}`);
                if (lineTotal) lineTotal.textContent = data.line_total;
            }

            // Update seller subtotals in summary
            if (data.groups && Array.isArray(data.groups)) {
                data.groups.forEach(g => {
                    const sellerVal = document.getElementById(`summary-seller-val-${g.seller_id}`);
                    if (sellerVal) {
                        sellerVal.textContent = '$' + g.total_formatted;
                    }
                });
            }

            // Update Grand Total in summary
            const grandTotalEl = document.getElementById('cart-grand-total');
            if (grandTotalEl && data.grand_total_formatted) {
                grandTotalEl.textContent = data.grand_total_formatted;
            }

            // Update header badge with distinct item count
            if (window.updateCartBadge && typeof data.cart_count !== 'undefined') {
                window.updateCartBadge(data.cart_count);
            }
        }
    </script>
@endsection
