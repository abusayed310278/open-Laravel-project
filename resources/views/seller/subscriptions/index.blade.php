@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Subscription')

@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.subscription' : 'saler.subscriptions';
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('product')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    @if ($current)
        <x-card title="Current Plan">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-lg font-bold text-gray-900">{{ $current->plan->name }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        @if ($current->plan->type->value === 'saler')
                            {{ $current->credits?->remaining_credits ?? 0 }} of {{ $current->credits?->total_credits ?? 0 }} listing credits remaining
                        @else
                            Renews {{ $current->ends_at?->format('M j, Y') ?? '—' }}
                        @endif
                    </p>
                </div>
                @if ($current->auto_renew)
                    <form method="POST" action="{{ route($routePrefix.'.cancel') }}" data-confirm="Cancel your subscription?">
                        @csrf
                        <x-button type="submit" variant="secondary">Cancel Plan</x-button>
                    </form>
                @endif
            </div>
        </x-card>
    @endif

    <x-card title="Available Plans">
        @if ($plans->isEmpty())
            <p class="text-sm text-gray-400">No plans have been configured yet.</p>
        @else
            <div class="grid sm:grid-cols-3 gap-5">
                @foreach ($plans as $plan)
                    <div @class(['border rounded-md p-5', 'border-brand-500 bg-brand-50' => $current?->plan_id === $plan->id, 'border-gray-100' => $current?->plan_id !== $plan->id])>
                        <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">${{ number_format($plan->price, 2) }}</p>
                        <p class="text-xs text-gray-400 mb-4">{{ $plan->billing_cycle->label() }}</p>

                        <ul class="text-sm text-gray-500 space-y-1.5 mb-5">
                            @if ($plan->listing_credits)
                                <li>{{ $plan->listing_credits }} listing credits</li>
                            @endif
                            @if ($plan->duration_days)
                                <li>Valid for {{ $plan->duration_days }} days</li>
                            @endif
                            @if ($plan->max_products)
                                <li>Up to {{ $plan->max_products }} products</li>
                            @elseif ($plan->type->value === 'business')
                                <li>Unlimited products</li>
                            @endif
                        </ul>

                        @if ($current?->plan_id === $plan->id)
                            <x-button variant="secondary" class="w-full justify-center" disabled>Current Plan</x-button>
                        @else
                            <form method="POST" action="{{ route($routePrefix.'.store', $plan) }}">
                                @csrf
                                <x-button type="submit" class="w-full justify-center">Choose Plan</x-button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    @if ($history->isNotEmpty())
        <x-card title="Billing History">
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
