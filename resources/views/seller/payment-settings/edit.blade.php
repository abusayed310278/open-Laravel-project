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

    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900 tracking-tight">Payment Gateways & Direct Customer Payouts</h1>
        <p class="text-xs text-gray-500 mt-1">
            Connect your merchant API keys so customer payments for your listings route directly to your Stripe or PayPal account.
        </p>
    </div>

    {{-- Connected Online Payment Systems Cards --}}
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        {{-- Stripe Merchant Connection Card --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697.4 12.876.4 7.625.4 3.97 3.16 3.97 7.428c0 5.166 4.707 6.304 8.163 7.573 2.502.916 3.356 1.706 3.356 2.73 0 1.053-.942 1.631-2.464 1.631-2.172 0-5.18-1.053-7.23-2.199l-.97 5.577c2.144 1.136 5.374 1.859 8.283 1.859 5.567 0 9.472-2.603 9.472-7.318 0-4.945-4.485-6.523-8.604-8.131z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Stripe Merchant Account</h3>
                            <p class="text-xs text-gray-500">Credit / Debit Card Checkout</p>
                        </div>
                    </div>
                    @if ($settings->hasStripeConnected() && $settings->stripe_enabled)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            ✓ Connected
                        </span>
                    @elseif ($settings->hasStripeConnected())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            Keys Saved (Disabled)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                            Not Connected
                        </span>
                    @endif
                </div>

                <div class="py-3 text-xs text-gray-600 space-y-2">
                    @if ($settings->hasStripeConnected())
                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-400">Publishable Key:</span>
                                <span class="font-mono text-gray-800 font-semibold truncate max-w-[200px]">{{ Str::limit($settings->stripe_publishable_key, 24) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-400">Secret Key:</span>
                                <span class="font-mono text-gray-800 font-semibold">••••••••••••••••</span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">Connect your Stripe Publishable & Secret Keys so customers can pay directly into your Stripe account.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <button type="button" data-modal-open="connect-stripe-modal" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>{{ $settings->hasStripeConnected() ? 'Configure Keys' : 'Connect Stripe' }}</span>
                </button>

                @if ($settings->hasStripeConnected())
                    <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="m-0 inline-block">
                        @csrf
                        <input type="hidden" name="action" value="disconnect_stripe">
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer">
                            Disconnect
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- PayPal Merchant Connection Card --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72a.78.78 0 0 1 .77-.655h6.91c2.4 0 4.257.545 5.215 1.533.906.934 1.156 2.302.744 4.066-.632 2.709-2.392 4.415-4.832 4.686-.33.037-.665.056-1.004.056H9.72l-1.4 8.283a.64.64 0 0 1-.633.541z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">PayPal Merchant Account</h3>
                            <p class="text-xs text-gray-500">PayPal Wallet & Express Checkout</p>
                        </div>
                    </div>
                    @if ($settings->hasPaypalConnected() && $settings->paypal_enabled)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            ✓ Connected
                        </span>
                    @elseif ($settings->hasPaypalConnected())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            Keys Saved (Disabled)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                            Not Connected
                        </span>
                    @endif
                </div>

                <div class="py-3 text-xs text-gray-600 space-y-2">
                    @if ($settings->hasPaypalConnected())
                        <div class="bg-gray-50 border border-gray-100 rounded-lg p-3 space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-400">Client ID:</span>
                                <span class="font-mono text-gray-800 font-semibold truncate max-w-[200px]">{{ Str::limit($settings->paypal_client_id, 24) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-gray-400">Client Secret:</span>
                                <span class="font-mono text-gray-800 font-semibold">••••••••••••••••</span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">Connect your PayPal Client ID & Secret so buyers can pay directly to your PayPal account.</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                <button type="button" data-modal-open="connect-paypal-modal" style="background-color: #2563eb !important; color: #ffffff !important;" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span class="text-white font-bold">{{ $settings->hasPaypalConnected() ? 'Configure Keys' : 'Connect PayPal' }}</span>
                </button>

                @if ($settings->hasPaypalConnected())
                    <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="m-0 inline-block">
                        @csrf
                        <input type="hidden" name="action" value="disconnect_paypal">
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer">
                            Disconnect
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Manual Bank & COD Settings Form --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-xs space-y-6">
        <div>
            <h2 class="text-base font-bold text-gray-900">Accepted Checkout Payment Methods</h2>
            <p class="text-xs text-gray-500 mt-0.5">Toggle which payment methods buyers can select at checkout for your listings.</p>
        </div>

        <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="action" value="save_all">

            <label class="flex items-center justify-between py-3 border-b border-gray-100 cursor-pointer">
                <div>
                    <span class="text-sm font-medium text-gray-800 block">Stripe Card Checkout</span>
                    <span class="text-xs text-gray-400">Allow buyers to pay via Stripe card gateway</span>
                </div>
                <input type="checkbox" name="stripe_enabled" value="1" @checked($settings->stripe_enabled) class="w-4 h-4 rounded accent-indigo-600">
            </label>

            <label class="flex items-center justify-between py-3 border-b border-gray-100 cursor-pointer">
                <div>
                    <span class="text-sm font-medium text-gray-800 block">PayPal Express Checkout</span>
                    <span class="text-xs text-gray-400">Allow buyers to pay via PayPal wallet</span>
                </div>
                <input type="checkbox" name="paypal_enabled" value="1" @checked($settings->paypal_enabled) class="w-4 h-4 rounded accent-blue-600">
            </label>

            <label class="flex items-center justify-between py-3 border-b border-gray-100 cursor-pointer">
                <div>
                    <span class="text-sm font-medium text-gray-800 block">Cash on Delivery (COD)</span>
                    <span class="text-xs text-gray-400">Collect cash payment upon product delivery</span>
                </div>
                <input type="checkbox" name="cod_enabled" value="1" @checked($settings->cod_enabled) class="w-4 h-4 rounded accent-brand-500">
            </label>

            <label class="flex items-center justify-between py-3 border-b border-gray-100 cursor-pointer">
                <div>
                    <span class="text-sm font-medium text-gray-800 block">Manual Bank Transfer</span>
                    <span class="text-xs text-gray-400">Direct wire deposit to your bank account</span>
                </div>
                <input type="checkbox" name="manual_bank_enabled" value="1" @checked($settings->manual_bank_enabled) class="w-4 h-4 rounded accent-emerald-600">
            </label>

            <div class="space-y-3 pt-2">
                <h4 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Your Bank Transfer Details</h4>
                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="Bank Name" name="bank_details[bank_name]" type="text" :value="old('bank_details.bank_name', $bankDetails['bank_name'] ?? null)" placeholder="e.g. Chase Bank, HSBC" />
                    <x-input label="Account Holder Name" name="bank_details[account_name]" type="text" :value="old('bank_details.account_name', $bankDetails['account_name'] ?? null)" placeholder="e.g. John Doe Store LLC" />
                    <x-input label="Account Number" name="bank_details[account_number]" type="text" :value="old('bank_details.account_number', $bankDetails['account_number'] ?? null)" placeholder="e.g. 987654321" />
                    <x-input label="Routing / SWIFT / IBAN" name="bank_details[routing_number]" type="text" :value="old('bank_details.routing_number', $bankDetails['routing_number'] ?? null)" placeholder="e.g. CHASUS33XXX" />
                </div>
            </div>

            <div class="pt-2">
                <x-button type="submit">Save Payment Settings</x-button>
            </div>
        </form>
    </div>

    {{-- Connect Stripe Modal --}}
    <x-modal id="connect-stripe-modal" title="Connect Stripe Merchant Account" maxWidth="max-w-md">
        <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="connect_stripe">

            <p class="text-xs text-gray-500">
                Enter your Stripe Publishable & Secret keys from your <a href="https://dashboard.stripe.com/apikeys" target="_blank" class="text-indigo-600 underline font-semibold">Stripe Dashboard</a>.
            </p>

            <x-input label="Publishable Key (pk_live_... / pk_test_...)" name="stripe_publishable_key" type="text" :value="old('stripe_publishable_key', $settings->stripe_publishable_key)" placeholder="pk_test_..." required />
            <x-input label="Secret Key (sk_live_... / sk_test_...)" name="stripe_secret_key" type="password" :value="old('stripe_secret_key', $settings->stripe_secret_key)" placeholder="sk_test_..." required />

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold">Save & Connect Stripe</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Connect PayPal Modal --}}
    <x-modal id="connect-paypal-modal" title="Connect PayPal Merchant Account" maxWidth="max-w-md">
        <form method="POST" action="{{ route($routePrefix.'payment-settings.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="action" value="connect_paypal">

            <p class="text-xs text-gray-500">
                Enter your PayPal REST API Client ID & Secret from your <a href="https://developer.paypal.com/dashboard/applications" target="_blank" class="text-blue-600 underline font-semibold">PayPal Developer Dashboard</a>.
            </p>

            <x-input label="Client ID" name="paypal_client_id" type="text" :value="old('paypal_client_id', $settings->paypal_client_id)" placeholder="Client ID from PayPal Developer Portal" required />
            <x-input label="Client Secret" name="paypal_client_secret" type="password" :value="old('paypal_client_secret', $settings->paypal_client_secret)" placeholder="Client Secret from PayPal Developer Portal" required />

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" style="background-color: #2563eb !important; color: #ffffff !important;" class="bg-blue-600 hover:bg-blue-700 text-white font-bold">Save & Connect PayPal</x-button>
            </div>
        </form>
    </x-modal>

@endsection
