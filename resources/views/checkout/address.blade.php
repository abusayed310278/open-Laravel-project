@extends('layouts.app')

@section('title', 'Delivery Address')

@section('content')
    @php
        $field = 'w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent';
        $value = fn (string $key, ?string $default = '') => old($key, $address[$key] ?? $default);
        $subtotal = array_sum(array_column($groups, 'subtotal'));
        $shipping = array_sum(array_column($groups, 'shipping'));
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
        <div class="mb-6">
            <a href="{{ route('cart') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-900">← Back to cart</a>
            <h1 class="mt-2 text-2xl font-extrabold text-gray-900">Delivery address</h1>
            <p class="text-sm text-gray-500 mt-1">Tell us where to deliver your order.</p>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-6">
            <form method="POST" action="{{ route('checkout.address.store') }}" class="bg-white border border-gray-100 rounded-2xl p-6 space-y-4">
                @csrf

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                        <input type="text" id="name" name="name" value="{{ $value('name') }}" required maxlength="255" class="{{ $field }}" autocomplete="name">
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone</label>
                        <input type="tel" id="phone" name="phone" value="{{ $value('phone') }}" required maxlength="30" class="{{ $field }}" autocomplete="tel">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ $value('email') }}" required maxlength="255" class="{{ $field }}" autocomplete="email">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line1" class="block text-sm font-medium text-gray-700 mb-1.5">Address line 1</label>
                        <input type="text" id="line1" name="line1" value="{{ $value('line1') }}" required maxlength="255" placeholder="House / building, street, area" class="{{ $field }}" autocomplete="address-line1">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="line2" class="block text-sm font-medium text-gray-700 mb-1.5">Address line 2 <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" id="line2" name="line2" value="{{ $value('line2') }}" maxlength="255" class="{{ $field }}" autocomplete="address-line2">
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1.5">Country</label>
                        <input type="text" id="country" name="country" value="{{ $value('country', 'United Arab Emirates') }}" required maxlength="100" class="{{ $field }}" autocomplete="country-name">
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1.5">State / area <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" id="state" name="state" value="{{ $value('state') }}" maxlength="100" class="{{ $field }}" autocomplete="address-level1">
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1.5">City</label>
                        <input type="text" id="city" name="city" value="{{ $value('city') }}" required maxlength="100" class="{{ $field }}" autocomplete="address-level2">
                    </div>
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">Postal code <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" id="postal_code" name="postal_code" value="{{ $value('postal_code') }}" maxlength="20" class="{{ $field }}" autocomplete="postal-code">
                    </div>
                </div>

                <x-button type="submit" class="w-full justify-center">Continue</x-button>
            </form>

            <aside class="bg-white border border-gray-100 rounded-2xl p-5 h-fit">
                <h2 class="text-sm font-bold text-gray-900 mb-3">Order summary</h2>
                <ul class="space-y-3">
                    @foreach ($groups as $group)
                        @foreach ($group['items'] as $item)
                            <li class="flex justify-between gap-3 text-sm">
                                <span class="text-gray-700 min-w-0 truncate">{{ $item->product->title ?? 'Item' }} <span class="text-gray-400">× {{ $item->quantity }}</span></span>
                                <span class="font-medium text-gray-900">${{ number_format($item->price * $item->quantity, 2) }}</span>
                            </li>
                        @endforeach
                    @endforeach
                </ul>
                <dl class="mt-4 pt-4 border-t border-gray-100 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd class="text-gray-900">${{ number_format($subtotal, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Shipping</dt><dd class="text-gray-900">${{ number_format($shipping, 2) }}</dd></div>
                    <div class="flex justify-between font-bold text-base pt-2"><dt>Total</dt><dd>${{ number_format($subtotal + $shipping, 2) }}</dd></div>
                </dl>
            </aside>
        </div>
    </div>
@endsection
