@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Payment Settings')

@php
    $routePrefix = auth()->user()->isBusiness() ? 'business.' : 'saler.';
    $bankDetails = $settings->bank_details ?? [];
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Accepted Payment Methods">
        <p class="text-sm text-gray-500 mb-5">
            These control how buyers pay for your listings (only used when a product's payment route is
            <code>seller</code> — Openbox-warehoused items always settle through the Openbox gateway).
        </p>

        <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="space-y-6">
            @csrf

            <label class="flex items-center justify-between py-3 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-700">Cash on Delivery</span>
                <input type="checkbox" name="cod_enabled" value="1" @checked($settings->cod_enabled) class="w-4 h-4 rounded accent-brand-500">
            </label>

            <label class="flex items-center justify-between py-3 border-b border-gray-50">
                <span class="text-sm font-medium text-gray-700">Manual Bank Transfer</span>
                <input type="checkbox" name="manual_bank_enabled" value="1" @checked($settings->manual_bank_enabled) class="w-4 h-4 rounded accent-brand-500">
            </label>

            <div class="grid sm:grid-cols-2 gap-5">
                <x-input label="Bank name" name="bank_details[bank_name]" type="text" :value="old('bank_details.bank_name', $bankDetails['bank_name'] ?? null)" />
                <x-input label="Account name" name="bank_details[account_name]" type="text" :value="old('bank_details.account_name', $bankDetails['account_name'] ?? null)" />
                <x-input label="Account number" name="bank_details[account_number]" type="text" :value="old('bank_details.account_number', $bankDetails['account_number'] ?? null)" />
                <x-input label="Routing / IBAN" name="bank_details[routing_number]" type="text" :value="old('bank_details.routing_number', $bankDetails['routing_number'] ?? null)" />
            </div>

            <x-button type="submit">Save Payment Settings</x-button>
        </form>
    </x-card>

    <x-card title="Connected Gateways">
        <p class="text-sm text-gray-500 mb-4">Card payments via your own Stripe or PayPal account — coming soon.</p>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-4">
                <span class="text-sm font-medium text-gray-700">Stripe</span>
                <x-badge color="gray">Not connected</x-badge>
            </div>
            <div class="flex items-center justify-between border border-gray-100 rounded-md p-4">
                <span class="text-sm font-medium text-gray-700">PayPal</span>
                <x-badge color="gray">Not connected</x-badge>
            </div>
        </div>
    </x-card>
@endsection
