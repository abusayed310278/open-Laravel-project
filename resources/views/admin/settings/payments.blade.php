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
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-3">
                    <input type="checkbox" name="stripe_enabled" value="1" @checked($values['stripe_enabled']) class="w-4 h-4 rounded accent-brand-500">
                    Enable Stripe
                </label>
                <div class="grid sm:grid-cols-2 gap-5">
                    <x-input label="Publishable key" name="stripe_publishable_key" type="text" :value="old('stripe_publishable_key', $values['stripe_publishable_key'])" />
                    <x-input label="Secret key" name="stripe_secret_key" type="password" :placeholder="$hasStripeSecret ? '••••••••  (leave blank to keep current)' : 'Not set'" />
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
@endsection
