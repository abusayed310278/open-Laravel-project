@extends('layouts.admin')

@section('title', 'Subscriptions')

@section('content')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="danger" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-gray-950 tracking-tight">Subscriptions</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Manage seller and business plan subscriptions across the marketplace.</p>
        </div>
        <div class="flex items-center gap-2">
            <x-button type="button" data-modal-open="grant-subscription-modal" size="sm">
                + Grant Subscription
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card
            label="Active Subscriptions"
            :value="number_format($statusCounts->get('active', 0))"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />

        <x-stat-card
            label="Monthly Recurring Value"
            :value="'Tk '.number_format($activeRevenue, 2)"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Pending"
            :value="number_format($statusCounts->get('pending', 0))"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Expired / Cancelled"
            :value="number_format($statusCounts->get('expired', 0) + $statusCounts->get('cancelled', 0))"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12"/></svg>'
        />
    </div>

    <x-card title="All Subscriptions" class="shadow-2xs">
        <x-slot:action>
            <form method="GET" class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="border border-gray-200/80 rounded-xl px-3.5 py-2 text-xs font-semibold bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\SubscriptionStatus::cases() as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:action>

        <div class="overflow-x-auto -mx-5 -my-4">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/80 text-gray-500 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3">Plan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Started</th>
                        <th class="px-5 py-3">Ends</th>
                        <th class="px-5 py-3 text-center">Auto-renew</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-brand-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($subscription->user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <p class="font-bold text-gray-950 leading-tight">{{ $subscription->user->name }}</p>
                                            <x-badge color="gray" class="text-[9px] px-1.5 py-0">{{ $subscription->user->role->label() }}</x-badge>
                                        </div>
                                        <p class="text-[10px] text-gray-400">{{ $subscription->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-700">{{ $subscription->plan->name }}</td>
                            <td class="px-5 py-3.5">
                                <x-badge :color="$subscription->status->badgeColor()">{{ $subscription->status->label() }}</x-badge>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 whitespace-nowrap">{{ $subscription->starts_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-5 py-3.5 text-gray-400 whitespace-nowrap">{{ $subscription->ends_at?->format('M j, Y') ?? 'Never' }}</td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if ($subscription->auto_renew)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-600">Yes</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-500">No</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.subscriptions.toggle-status', $subscription) }}" class="inline-block m-0">
                                    @csrf
                                    @method('PATCH')
                                    @if ($subscription->status === \App\Enums\SubscriptionStatus::Active)
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </button>
                                    @else
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate & Grant Checkmark)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-10 text-center text-gray-400 text-xs">No subscriptions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$subscriptions" />
    </x-card>

    {{-- Grant Subscription Modal --}}
    <x-modal id="grant-subscription-modal" title="Grant Subscription Plan & Verified Status" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Select User / Seller <span class="text-red-500">*</span></label>
                <select name="user_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs cursor-pointer p-2.5">
                    <option value="">Select a user account...</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">
                            {{ $u->name }} ({{ $u->email }}) — {{ $u->role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Select Subscription Plan <span class="text-red-500">*</span></label>
                <select name="plan_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs cursor-pointer p-2.5">
                    <option value="">Select a plan...</option>
                    @foreach ($plans as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->name }} (${{ number_format($p->price, 2) }}) — Target: {{ ucfirst($p->type->value) }} — {{ $p->listing_credits ? $p->listing_credits . ' credits' : ($p->max_products ? $p->max_products . ' products' : 'Unlimited') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <p class="text-xs text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100">
                Granting a subscription plan will activate the seller/store profile, enable listing credits, and display the verified checkmark badge across their store and products.
            </p>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Grant Plan & Activate Checkmark</x-button>
            </div>
        </form>
    </x-modal>
@endsection
