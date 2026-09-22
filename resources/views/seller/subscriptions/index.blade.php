@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Subscription')

@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.subscription' : 'saler.subscriptions';
@endphp

@section('content')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @error('product')
        <x-alert type="error" class="mb-5">{{ $message }}</x-alert>
    @enderror

    @if ($current)
        <div class="relative overflow-hidden bg-gray-900 rounded-2xl p-6 sm:p-7 mb-8">
            <div class="absolute -top-16 -right-16 w-56 h-56 bg-brand-500/20 rounded-full blur-3xl"></div>
            <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 shrink-0 bg-brand-500/15 rounded-xl flex items-center justify-center text-brand-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.504-1.125-1.125-1.125h-.75a1.125 1.125 0 01-1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-3.75a1.125 1.125 0 00-1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125h-.75c-.621 0-1.125.504-1.125 1.125V18.75m9 0h-9M9 6.75V4.5a2.25 2.25 0 014.5 0v2.25m-4.5 0h4.5m-4.5 0a2.25 2.25 0 00-2.25 2.25v.75h9v-.75A2.25 2.25 0 0013.5 6.75" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-brand-400 uppercase tracking-wide">Current Plan</p>
                        <p class="text-xl font-bold text-white mt-0.5">{{ $current->plan->name }}</p>
                        <p class="text-sm text-gray-400 mt-1">
                            @if ($current->plan->type->value === 'saler')
                                {{ $current->credits?->remaining_credits ?? 0 }} of {{ $current->credits?->total_credits ?? 0 }} listing credits remaining
                            @else
                                Renews {{ $current->ends_at?->format('M j, Y') ?? '—' }}
                            @endif
                        </p>
                    </div>
                </div>
                @if ($current->auto_renew)
                    <form method="POST" action="{{ route($routePrefix.'.cancel') }}" data-confirm="Cancel your subscription?">
                        @csrf
                        <x-button type="submit" variant="secondary" class="!border-white/15 !text-gray-200 hover:!bg-white/5">Cancel Plan</x-button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    <x-card title="Available Plans">
        @if ($plans->isEmpty())
            <p class="text-sm text-gray-400">No plans have been configured yet.</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7 items-start">
                @foreach ($plans as $plan)
                    @php $isCurrent = $current?->plan_id === $plan->id; @endphp
                    <div @class([
                        'group relative flex flex-col bg-white rounded-2xl border overflow-hidden transition-all duration-200',
                        'border-brand-500 border-2 shadow-xl' => $isCurrent,
                        'border-gray-100 shadow-2xs hover:border-brand-200 hover:shadow-xl hover:-translate-y-1' => ! $isCurrent,
                    ])>
                        <div class="h-1.5 bg-gradient-to-r from-brand-400 via-brand-500 to-brand-600"></div>

                        <div class="p-7 pb-6">
                            <div class="flex items-start justify-between mb-5">
                                <div class="w-12 h-12 bg-gradient-to-br from-brand-50 to-brand-100 rounded-xl flex items-center justify-center text-brand-600 transition-transform duration-200 group-hover:scale-105">
                                    @if ($plan->type->value === 'business')
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a2.25 2.25 0 003.182 0l4.318-4.318a2.25 2.25 0 000-3.182L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                        </svg>
                                    @endif
                                </div>

                                @if ($isCurrent)
                                    <span class="inline-flex items-center gap-1 bg-brand-500 text-white text-[11px] font-semibold px-2.5 py-1 rounded-full">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        Active
                                    </span>
                                @else
                                    <span class="bg-gray-50 text-gray-500 text-[11px] font-semibold px-2.5 py-1 rounded-full">
                                        {{ $plan->type->value === 'business' ? 'Store Owner' : 'Individual Seller' }}
                                    </span>
                                @endif
                            </div>

                            <p class="font-bold text-gray-900 text-xl tracking-tight">{{ $plan->name }}</p>
                            <p class="text-xs text-gray-400 mt-1">Billed {{ strtolower($plan->billing_cycle->label()) }}</p>

                            <div class="flex items-baseline gap-1 mt-5">
                                <span class="text-4xl font-black text-gray-950 tracking-tight">${{ number_format($plan->price, 2) }}</span>
                                @if ($plan->billing_cycle->value !== 'one_time')
                                    <span class="text-sm text-gray-400 font-medium">/{{ $plan->billing_cycle->value === 'yearly' ? 'yr' : 'mo' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col border-t border-gray-100 px-7 pt-6 pb-7">
                            <ul class="text-sm text-gray-600 space-y-3.5">
                                @forelse (($plan->features ?? []) as $feature)
                                    <li class="flex items-start gap-3">
                                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @empty
                                    @if ($plan->listing_credits)
                                        <li class="flex items-start gap-3">
                                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <span>{{ $plan->listing_credits }} listing credits</span>
                                        </li>
                                    @endif
                                    @if ($plan->duration_days)
                                        <li class="flex items-start gap-3">
                                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <span>Valid for {{ $plan->duration_days }} days</span>
                                        </li>
                                    @endif
                                    @if ($plan->max_products)
                                        <li class="flex items-start gap-3">
                                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <span>Up to {{ $plan->max_products }} products</span>
                                        </li>
                                    @elseif ($plan->type->value === 'business')
                                        <li class="flex items-start gap-3">
                                            <svg class="w-4 h-4 shrink-0 mt-0.5 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                            <span>Unlimited products</span>
                                        </li>
                                    @endif
                                @endforelse
                            </ul>

                            @if ($isCurrent)
                                <x-button variant="secondary" class="w-full justify-center mt-7 !rounded-xl !border-brand-200 !bg-brand-50 !text-brand-600" disabled>Current Plan</x-button>
                            @else
                                <x-button type="button" data-modal-open="subscribe-modal-{{ $plan->id }}" class="w-full justify-center mt-7 !rounded-xl !shadow-md hover:!shadow-lg">
                                    Choose Plan & Pay Admin
                                </x-button>
                            @endif
                        </div>
                    </div>

                    {{-- Subscription Payment Modal --}}
                    <x-modal id="subscribe-modal-{{ $plan->id }}" title="Subscribe to {{ $plan->name }}" maxWidth="max-w-lg">
                        <form method="POST" action="{{ route($routePrefix.'.store', $plan) }}" class="space-y-4">
                            @csrf

                            <div class="bg-brand-50/60 border border-brand-100 rounded-xl p-4 flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-brand-700 font-bold uppercase tracking-wider">Plan Summary</p>
                                    <p class="text-base font-extrabold text-gray-950 mt-0.5">{{ $plan->name }}</p>
                                    <p class="text-xs text-gray-500">Billed {{ strtolower($plan->billing_cycle->label()) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-black text-gray-950">${{ number_format($plan->price, 2) }}</p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                🔒 <strong>Payment Routing:</strong> Subscription payments are collected directly by <strong>Openbox Admin</strong> via Admin's configured payment gateways.
                            </p>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Select Admin Payment Gateway <span class="text-red-500">*</span></label>
                                <div class="space-y-2">
                                    @if ($adminPaymentMethods['stripe'] ?? false)
                                        <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-brand-300 bg-white cursor-pointer transition">
                                            <div class="flex items-center gap-2.5">
                                                <input type="radio" name="payment_method" value="stripe" checked class="w-4 h-4 text-brand-500 focus:ring-brand-400">
                                                <span class="text-xs font-bold text-gray-900">Credit / Debit Card (Stripe)</span>
                                            </div>
                                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Instant</span>
                                        </label>
                                    @endif

                                    @if ($adminPaymentMethods['paypal'] ?? false)
                                        <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-brand-300 bg-white cursor-pointer transition">
                                            <div class="flex items-center gap-2.5">
                                                <input type="radio" name="payment_method" value="paypal" @checked(!($adminPaymentMethods['stripe'] ?? false)) class="w-4 h-4 text-brand-500 focus:ring-brand-400">
                                                <span class="text-xs font-bold text-gray-900">PayPal Express</span>
                                            </div>
                                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Instant</span>
                                        </label>
                                    @endif

                                    @if ($adminPaymentMethods['manual_bank'] ?? false)
                                        <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-brand-300 bg-white cursor-pointer transition">
                                            <div class="flex items-center gap-2.5">
                                                <input type="radio" name="payment_method" value="manual_bank" @checked(!($adminPaymentMethods['stripe'] ?? false) && !($adminPaymentMethods['paypal'] ?? false)) class="w-4 h-4 text-brand-500 focus:ring-brand-400" onchange="document.getElementById('bank-ref-{{ $plan->id }}').classList.toggle('hidden', !this.checked)">
                                                <span class="text-xs font-bold text-gray-900">Direct Bank Transfer to Admin</span>
                                            </div>
                                            <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Bank Deposit</span>
                                        </label>

                                        <div id="bank-ref-{{ $plan->id }}" class="hidden p-3 bg-amber-50/60 rounded-xl border border-amber-200/80 space-y-2 mt-2">
                                            <p class="text-xs text-amber-900 font-medium"><strong>Admin Bank Details:</strong></p>
                                            <p class="text-xs text-amber-800 whitespace-pre-line font-mono">{{ $adminBankDetails ?? 'Contact Admin for bank details' }}</p>
                                            <div>
                                                <label class="block text-[11px] font-bold text-amber-900 uppercase mb-1">Deposit Slip / Reference No.</label>
                                                <input type="text" name="bank_reference" placeholder="e.g. TRX-98765432" class="w-full bg-white border border-amber-300 rounded-lg text-xs p-2 focus:ring-2 focus:ring-brand-400">
                                            </div>
                                        </div>
                                    @endif

                                    <label class="flex items-center justify-between p-3 rounded-xl border border-gray-200 hover:border-brand-300 bg-white cursor-pointer transition">
                                        <div class="flex items-center gap-2.5">
                                            <input type="radio" name="payment_method" value="cod" @checked(!($adminPaymentMethods['stripe'] ?? false) && !($adminPaymentMethods['paypal'] ?? false) && !($adminPaymentMethods['manual_bank'] ?? false)) class="w-4 h-4 text-brand-500 focus:ring-brand-400">
                                            <span class="text-xs font-bold text-gray-900">Cash / Direct Admin Settlement</span>
                                        </div>
                                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">Manual</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                                    Cancel
                                </button>
                                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Pay Admin & Activate Plan</x-button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach
            </div>
        @endif
    </x-card>

    @if ($history->isNotEmpty())
        <x-card title="Billing History" class="mt-8">
            <x-table :headers="['Plan', 'Status', 'Started', 'Ends']" id="history-table">
                @foreach ($history as $subscription)
                    <tr class="border-b border-gray-50">
                        <td class="px-4 py-3 text-gray-800">{{ $subscription->plan->name }}</td>
                        <td class="px-4 py-3"><x-badge :color="$subscription->status->badgeColor()">{{ $subscription->status->label() }}</x-badge></td>
                        <td class="px-4 py-3 text-gray-500">{{ $subscription->starts_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $subscription->ends_at?->format('M j, Y') ?? 'Never' }}</td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    @endif
@endsection
