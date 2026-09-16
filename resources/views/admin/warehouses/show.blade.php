@extends('layouts.admin')

@section('title', $warehouse->name)

@section('content')
    <x-breadcrumb :items="['Warehouses' => route('admin.warehouses.index'), $warehouse->name => null]" />

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    @error('location')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <x-slot:title>
            <span>Storage Slots — {{ $warehouse->name }}</span>
        </x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-slot-modal" size="sm">+ Add Storage Slot</x-button>
        </x-slot:action>

        <x-table :headers="['Slot Label', 'Zone', 'Row', 'Shelf', 'Slot', 'Status', 'Actions']" id="locations-table">
            @forelse ($warehouse->locations as $location)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-mono font-semibold text-gray-900">{{ $location->label() }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $location->zone ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $location->row ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $location->shelf ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $location->slot ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$location->is_occupied ? 'amber' : 'green'">
                            {{ $location->is_occupied ? 'Occupied' : 'Available' }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        @unless ($location->is_occupied)
                            <form method="POST" action="{{ route('admin.warehouses.locations.destroy', [$warehouse, $location]) }}" data-confirm="Remove this slot?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Remove slot">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No storage slots configured yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    {{-- Add Storage Slot Modal --}}
    <x-modal id="add-slot-modal" title="Add Warehouse Storage Slot" maxWidth="max-w-md">
        <form method="POST" action="{{ route('admin.warehouses.locations.store', $warehouse) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Zone" name="zone" type="text" placeholder="e.g. A" required />
                <x-input label="Row" name="row" type="text" placeholder="e.g. 1" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Shelf" name="shelf" type="text" placeholder="e.g. 2" required />
                <x-input label="Slot" name="slot" type="text" placeholder="e.g. 3" required />
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Add Slot</x-button>
            </div>
        </form>
    </x-modal>

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-slot-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
