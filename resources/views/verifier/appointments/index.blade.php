@extends('layouts.verifier')

@section('title', 'Appointments')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Upcoming Inspections">
        <x-table :headers="['Product', 'Seller', 'Location', 'Scheduled', 'Status', '']" id="appointments-table">
            @forelse ($appointments as $appointment)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $appointment->product->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $appointment->seller->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $appointment->location->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $appointment->scheduled_at?->format('M j, Y g:i A') }}</td>
                    <td class="px-4 py-3"><x-badge :color="$appointment->status->badgeColor()">{{ $appointment->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('verifier.appointments.inspect', $appointment) }}" class="text-brand-600 font-medium hover:underline text-sm">
                            {{ $appointment->status->value === 'inspecting' ? 'Continue' : 'Inspect' }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No appointments scheduled.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$appointments" />
    </x-card>
@endsection
