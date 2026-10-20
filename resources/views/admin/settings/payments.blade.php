@extends('layouts.admin')

@section('title', 'Settings — Payments')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Openbox Platform Gateway">
        <p class="text-sm text-gray-500 mb-5">
            These credentials process payments for Openbox-owned and warehouse-stored inventory
            (<code>payment_route = openbox</code>). Sellers connect their own accounts separately from their
            portal's Payment Settings page.
        </p>

        <form method="POST" action="{{ route('admin.settings.payments.update') }}" class="space-y-8">
            @csrf

            <div>
                <div class="flex items-center justify-between mb-3">
                    <label class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                        <input type="checkbox" name="stripe_enabled" value="1" @checked($values['stripe_enabled']) class="w-4 h-4 rounded accent-brand-500">
                        Enable Stripe
                    </label>
                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full">
                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Live Stripe API Verification Active
                    </span>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <x-input label="Publishable key (pk_live_... / pk_test_...)" name="stripe_publishable_key" id="stripe_pub_input" type="text" :value="old('stripe_publishable_key', $values['stripe_publishable_key'])" placeholder="pk_live_... or pk_test_..." />
                        <p class="text-[11px] text-gray-500 mt-1 flex items-center justify-between">
                            <span>Must start with <code class="text-indigo-600 font-mono">pk_live_</code> or <code class="text-indigo-600 font-mono">pk_test_</code></span>
                            <span id="stripe-pub-status" class="hidden text-[11px] font-semibold"></span>
                        </p>
                        @error('stripe_publishable_key')
                            <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-input label="Secret key (sk_live_... / sk_test_...)" name="stripe_secret_key" id="stripe_sec_input" type="password" :placeholder="$hasStripeSecret ? '••••••••  (leave blank to keep current)' : 'sk_live_... or sk_test_...'" />
                        <p class="text-[11px] text-gray-500 mt-1 flex items-center justify-between">
                            <span>Must start with <code class="text-indigo-600 font-mono">sk_live_</code> or <code class="text-indigo-600 font-mono">sk_test_</code></span>
                            <span id="stripe-sec-status" class="hidden text-[11px] font-semibold"></span>
                        </p>
                        @error('stripe_secret_key')
                            <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-3">
                    <input type="checkbox" name="paypal_enabled" value="1" @checked($values['paypal_enabled']) class="w-4 h-4 rounded accent-brand-500">
                    Enable PayPal
                </label>
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Client ID" name="paypal_client_id" type="text" :value="old('paypal_client_id', $values['paypal_client_id'])" />
                    <x-input label="Client secret" name="paypal_client_secret" type="password" :placeholder="$hasPaypalSecret ? '••••••••  (leave blank to keep current)' : 'Not set'" />
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <p class="text-sm font-semibold text-gray-800 mb-3">Manual Bank Transfer</p>
                <x-textarea label="Bank details shown to customers at checkout" name="bank_details" rows="4" :value="old('bank_details', $values['bank_details'])" placeholder="Bank name, account name, account number, routing/IBAN..." />
            </div>

            <x-button type="submit">Save Payment Settings</x-button>
        </form>
    </x-card>

    <x-card title="Status">
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-3">
                <span class="text-gray-700">Cash on Delivery</span>
                <x-badge color="green">Active</x-badge>
            </div>
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-3">
                <span class="text-gray-700">Manual Bank Transfer</span>
                <x-badge :color="$values['bank_details'] ? 'green' : 'gray'">{{ $values['bank_details'] ? 'Active' : 'Not configured' }}</x-badge>
            </div>
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-3">
                <span class="text-gray-700">Stripe</span>
                <x-badge :color="$values['stripe_enabled'] && $hasStripeSecret ? 'green' : 'gray'">{{ $values['stripe_enabled'] && $hasStripeSecret ? 'Active' : 'Not configured' }}</x-badge>
            </div>
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-3">
                <span class="text-gray-700">PayPal</span>
                <x-badge :color="$values['paypal_enabled'] && $hasPaypalSecret ? 'green' : 'gray'">{{ $values['paypal_enabled'] && $hasPaypalSecret ? 'Active' : 'Not configured' }}</x-badge>
            </div>
        </div>
    </x-card>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pubInput = document.getElementById('stripe_pub_input') || document.querySelector('[name="stripe_publishable_key"]');
            const secInput = document.getElementById('stripe_sec_input') || document.querySelector('[name="stripe_secret_key"]');
            const pubStatus = document.getElementById('stripe-pub-status');
            const secStatus = document.getElementById('stripe-sec-status');

            const checkPub = () => {
                if (!pubInput || !pubStatus) return;
                const val = pubInput.value.trim();
                if (!val) {
                    pubStatus.className = 'hidden';
                    return;
                }
                pubStatus.classList.remove('hidden');
                if (val.startsWith('pk_live_')) {
                    pubStatus.textContent = 'Live Mode Key';
                    pubStatus.className = 'text-[11px] font-semibold text-emerald-600';
                } else if (val.startsWith('pk_test_')) {
                    pubStatus.textContent = 'Test Mode Key';
                    pubStatus.className = 'text-[11px] font-semibold text-amber-600';
                } else if (val.startsWith('mk_')) {
                    pubStatus.textContent = 'Invalid: mk_ is not a publishable key';
                    pubStatus.className = 'text-[11px] font-semibold text-red-600';
                } else {
                    pubStatus.textContent = 'Must start with pk_live_ or pk_test_';
                    pubStatus.className = 'text-[11px] font-semibold text-red-500';
                }
            };

            const checkSec = () => {
                if (!secInput || !secStatus) return;
                const val = secInput.value.trim();
                if (!val) {
                    secStatus.className = 'hidden';
                    return;
                }
                secStatus.classList.remove('hidden');
                if (val.startsWith('sk_live_') || val.startsWith('rk_live_')) {
                    secStatus.textContent = 'Live Secret Key';
                    secStatus.className = 'text-[11px] font-semibold text-emerald-600';
                } else if (val.startsWith('sk_test_') || val.startsWith('rk_test_')) {
                    secStatus.textContent = 'Test Secret Key';
                    secStatus.className = 'text-[11px] font-semibold text-amber-600';
                } else if (val.startsWith('mk_')) {
                    secStatus.textContent = 'Invalid: mk_ is not a secret API key';
                    secStatus.className = 'text-[11px] font-semibold text-red-600';
                } else {
                    secStatus.textContent = 'Must start with sk_live_ or sk_test_';
                    secStatus.className = 'text-[11px] font-semibold text-red-500';
                }
            };

            pubInput?.addEventListener('input', checkPub);
            secInput?.addEventListener('input', checkSec);
            checkPub();
            checkSec();
        });
    </script>
@endsection
