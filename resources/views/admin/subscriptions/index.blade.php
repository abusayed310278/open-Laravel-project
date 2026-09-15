@extends('layouts.admin')

@section('title', 'Subscriptions')

@section('content')

    <x-card>
        <x-slot:title>All Subscriptions</x-slot:title>

        <form method="GET" class="mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach (\App\Enums\SubscriptionStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['User', 'Plan', 'Status', 'Started', 'Ends', 'Auto-renew']" id="subscriptions-table">
            @forelse ($subscriptions as $subscription)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $subscription->user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $subscription->plan->name }}</td>
                    <td class="px-4 py-3"><x-badge :color="$subscription->status->badgeColor()">{{ $subscription->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $subscription->starts_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $subscription->ends_at?->format('M j, Y') ?? 'Never' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $subscription->auto_renew ? 'Yes' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No subscriptions yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$subscriptions" />
    </x-card>
@endsection
