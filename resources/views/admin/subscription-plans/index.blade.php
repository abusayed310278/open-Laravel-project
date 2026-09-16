@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.subscriptions.index') }}" class="text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← View Active Subscriptions
            </a>
        </div>
        <div>
            <x-button type="button" data-modal-open="add-plan-modal" size="sm">+ Add Plan</x-button>
        </div>
    </div>

    {{-- Saler Plans Card --}}
    <x-card class="mb-6">
        <x-slot:title>
            <div class="flex items-center gap-2">
                <span class="p-1 rounded bg-amber-50 text-amber-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </span>
                <span>Individual Seller Plans (Credits Based)</span>
            </div>
        </x-slot:title>

        <x-table :headers="['Plan Name', 'Price', 'Credits', 'Duration', 'Status', 'Actions']" id="saler-plans-table">
            @forelse ($salerPlans as $plan)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">${{ number_format($plan->price, 2) }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="blue">{{ $plan->listing_credits }} {{ $plan->listing_credits === 1 ? 'listing' : 'listings' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $plan->duration_days }} days</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$plan->is_active ? 'green' : 'gray'">{{ $plan->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Status Icon --}}
                            <form method="POST" action="{{ route('admin.subscriptions.plans.toggle-active', $plan) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($plan->is_active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-plan-modal-{{ $plan->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit plan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" data-confirm="Remove this plan?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No seller plans yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    {{-- Business Plans Card --}}
    <x-card>
        <x-slot:title>
            <div class="flex items-center gap-2">
                <span class="p-1 rounded bg-blue-50 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                </span>
                <span>Business Store Plans (Recurring Subscriptions)</span>
            </div>
        </x-slot:title>

        <x-table :headers="['Plan Name', 'Price', 'Billing Cycle', 'Max Products', 'Status', 'Actions']" id="business-plans-table">
            @forelse ($businessPlans as $plan)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">${{ number_format($plan->price, 2) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <span class="capitalize">{{ $plan->billing_cycle->label() }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if ($plan->max_products)
                            <x-badge color="blue">{{ number_format($plan->max_products) }} products</x-badge>
                        @else
                            <x-badge color="green">Unlimited</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$plan->is_active ? 'green' : 'gray'">{{ $plan->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Status Icon --}}
                            <form method="POST" action="{{ route('admin.subscriptions.plans.toggle-active', $plan) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($plan->is_active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-plan-modal-{{ $plan->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit plan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" data-confirm="Remove this plan?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No business plans yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    {{-- Add Plan Modal --}}
    <x-modal id="add-plan-modal" title="Add Subscription Plan" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}" class="space-y-4">
            @csrf
            <x-input label="Plan Name" name="name" type="text" :value="old('name')" placeholder="e.g. Starter Pack, Growth Tier, Enterprise Pro" required />

            <div class="grid grid-cols-2 gap-3">
                <x-select label="Account Target" name="type" :options="['saler' => 'Saler (Credits / Listings)', 'business' => 'Business (Store Membership)']" :selected="old('type', 'saler')" required />
                <x-select label="Billing Cycle" name="billing_cycle" :options="['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly']" :selected="old('billing_cycle', 'one_time')" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Price ($)" name="price" type="number" step="0.01" :value="old('price')" placeholder="e.g. 19.99" required />
                <x-input label="Listing Credits (Saler)" name="listing_credits" type="number" :value="old('listing_credits')" placeholder="e.g. 50" min="1" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Duration in Days (Saler)" name="duration_days" type="number" :value="old('duration_days', 30)" placeholder="e.g. 30" min="1" />
                <x-input label="Max Products (Business, Blank = Unlimited)" name="max_products" type="number" :value="old('max_products')" placeholder="e.g. 500" min="1" />
            </div>

            <div>
                <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', '1')" />
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Plan</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Plan Modals --}}
    @foreach ($salerPlans->merge($businessPlans) as $plan)
        <x-modal id="edit-plan-modal-{{ $plan->id }}" title="Edit Plan: {{ $plan->name }}" maxWidth="max-w-xl">
            <form method="POST" action="{{ route('admin.subscriptions.plans.update', $plan) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Plan Name" name="name" type="text" :value="old('name', $plan->name)" required />

                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Account Target" name="type" :options="['saler' => 'Saler (Credits / Listings)', 'business' => 'Business (Store Membership)']" :selected="old('type', $plan->type->value)" required />
                    <x-select label="Billing Cycle" name="billing_cycle" :options="['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly']" :selected="old('billing_cycle', $plan->billing_cycle->value)" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Price ($)" name="price" type="number" step="0.01" :value="old('price', $plan->price)" required />
                    <x-input label="Listing Credits (Saler)" name="listing_credits" type="number" :value="old('listing_credits', $plan->listing_credits)" min="1" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Duration in Days (Saler)" name="duration_days" type="number" :value="old('duration_days', $plan->duration_days)" min="1" />
                    <x-input label="Max Products (Business, Blank = Unlimited)" name="max_products" type="number" :value="old('max_products', $plan->max_products)" min="1" />
                </div>

                <div>
                    <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', (string)(int)$plan->is_active)" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Plan</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-plan-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
