@extends('layouts.admin')

@section('title', 'Verification Locations')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Location">
        <form method="POST" action="{{ route('admin.verification-locations.store') }}" class="grid sm:grid-cols-3 gap-4">
            @csrf
            <x-input label="Name" name="name" type="text" />
            <x-input label="Address" name="address" type="text" />
            <x-input label="City" name="city" type="text" />
            <x-input label="Country" name="country" type="text" />
            <x-input label="Phone (optional)" name="phone" type="text" />
            <x-input label="Email (optional)" name="email" type="email" />
            <x-button type="submit" class="sm:col-span-3">Add Location</x-button>
        </form>
    </x-card>

    <x-card title="Locations">
        <x-table :headers="['Name', 'City', 'Verifiers', 'Status', '']" id="locations-table">
            @forelse ($locations as $location)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $location->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $location->city }}, {{ $location->country }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $location->verifiers_count }}</td>
                    <td class="px-4 py-3"><x-badge :color="$location->is_active ? 'green' : 'gray'">{{ $location->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.verification-locations.destroy', $location) }}" data-confirm="Remove this location?">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No locations yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
