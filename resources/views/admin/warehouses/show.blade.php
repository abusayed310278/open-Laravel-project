@extends('layouts.admin')

@section('title', $warehouse->name)

@section('content')
    <x-breadcrumb :items="['Warehouses' => route('admin.warehouses.index'), $warehouse->name => null]" />

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('location')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card title="Add Storage Slot">
        <form method="POST" action="{{ route('admin.warehouses.locations.store', $warehouse) }}" class="grid sm:grid-cols-5 gap-4 items-end">
            @csrf
            <x-input label="Zone" name="zone" type="text" placeholder="A" />
            <x-input label="Row" name="row" type="text" placeholder="1" />
            <x-input label="Shelf" name="shelf" type="text" placeholder="2" />
            <x-input label="Slot" name="slot" type="text" placeholder="3" />
            <x-button type="submit">Add Slot</x-button>
        </form>
    </x-card>

    <x-card title="Storage Slots">
        <x-table :headers="['Slot', 'Status', '']" id="locations-table">
            @forelse ($warehouse->locations as $location)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-800">{{ $location->label() }}</td>
                    <td class="px-4 py-3"><x-badge :color="$location->is_occupied ? 'amber' : 'green'">{{ $location->is_occupied ? 'Occupied' : 'Available' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        @unless ($location->is_occupied)
                            <form method="POST" action="{{ route('admin.warehouses.locations.destroy', [$warehouse, $location]) }}" data-confirm="Remove this slot?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No slots yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
