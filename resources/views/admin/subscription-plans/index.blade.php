@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Plan">
        <form method="POST" action="{{ route('admin.subscriptions.plans.store') }}" class="grid sm:grid-cols-3 gap-4">
            @csrf
            <x-input label="Name" name="name" type="text" />
            <x-select label="Type" name="type" :options="['saler' => 'Saler (credits)', 'business' => 'Business (recurring)']" />
            <x-select label="Billing cycle" name="billing_cycle" :options="['one_time' => 'One-time', 'monthly' => 'Monthly', 'yearly' => 'Yearly']" />
            <x-input label="Price ($)" name="price" type="number" step="0.01" />
            <x-input label="Listing credits (saler only)" name="listing_credits" type="number" />
            <x-input label="Duration in days (saler only)" name="duration_days" type="number" />
            <x-input label="Max products (business only, blank = unlimited)" name="max_products" type="number" />
            <x-button type="submit" class="sm:col-span-3">Add Plan</x-button>
        </form>
    </x-card>

    <x-card title="Saler Plans">
        <x-table :headers="['Name', 'Price', 'Credits', 'Duration', 'Status', '']" id="saler-plans-table">
            @forelse ($salerPlans as $plan)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($plan->price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $plan->listing_credits }} listings</td>
                    <td class="px-4 py-3 text-gray-500">{{ $plan->duration_days }} days</td>
                    <td class="px-4 py-3"><x-badge :color="$plan->is_active ? 'green' : 'gray'">{{ $plan->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" data-confirm="Remove this plan?">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No saler plans yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    <x-card title="Business Plans">
        <x-table :headers="['Name', 'Price', 'Cycle', 'Max Products', 'Status', '']" id="business-plans-table">
            @forelse ($businessPlans as $plan)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($plan->price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $plan->billing_cycle->label() }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $plan->max_products ?? 'Unlimited' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$plan->is_active ? 'green' : 'gray'">{{ $plan->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.subscriptions.plans.destroy', $plan) }}" data-confirm="Remove this plan?">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No business plans yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
