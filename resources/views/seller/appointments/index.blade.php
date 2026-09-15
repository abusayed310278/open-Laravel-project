@extends('layouts.saler')

@section('title', 'Verification Appointments')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @if ($eligibleProducts->isNotEmpty() && $locations->isNotEmpty())
        <x-card title="Request Verification">
            <form method="POST" action="{{ route('saler.appointments.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
                @csrf
                <x-select label="Product" name="product_id" :options="$eligibleProducts->pluck('title', 'id')" />
                <x-select label="Location" name="location_id" :options="$locations->pluck('name', 'id')" />
                <x-input label="Date" name="appointment_date" type="date" />
                <x-input label="Time" name="appointment_time" type="time" />
                <x-button type="submit" class="sm:col-span-4">Book Appointment</x-button>
            </form>
        </x-card>
    @elseif ($locations->isEmpty())
        <x-alert type="info">No verification locations are configured yet — check back soon.</x-alert>
    @endif

    <x-card title="Your Products">
        <x-table :headers="['Product', 'Verification Status', 'Appointment', 'Location']" id="appointments-table">
            @forelse ($products as $product)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $product->title }}</td>
                    <td class="px-4 py-3"><x-badge :color="$product->verification_status->badgeColor()">{{ $product->verification_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $product->latestVerificationRequest?->scheduled_at?->format('M j, Y g:i A') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->latestVerificationRequest?->location?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No products yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$products" />
    </x-card>
@endsection
