@extends('layouts.admin')

@section('title', 'Verification Locations')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Verification Locations</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-location-modal" size="sm">+ Add Location</x-button>
        </x-slot:action>

        <x-table :headers="['Location Name', 'Address', 'City / Country', 'Contact', 'Verifiers', 'Status', 'Actions']" id="locations-table">
            @forelse ($locations as $location)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span>{{ $location->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $location->address }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $location->city }}, {{ $location->country }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if ($location->phone)
                            <div>{{ $location->phone }}</div>
                        @endif
                        @if ($location->email)
                            <div class="text-gray-400">{{ $location->email }}</div>
                        @endif
                        @if (!$location->phone && !$location->email)
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge color="blue">{{ $location->verifiers_count }} {{ $location->verifiers_count === 1 ? 'verifier' : 'verifiers' }}</x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$location->is_active ? 'green' : 'gray'">{{ $location->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Status Icon --}}
                            <form method="POST" action="{{ route('admin.verification-locations.toggle-active', $location) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($location->is_active)
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
                            <button type="button" data-modal-open="edit-location-modal-{{ $location->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit location">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.verification-locations.destroy', $location) }}" data-confirm="Remove this location?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete location">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No verification locations found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$locations" />
    </x-card>

    {{-- Add Location Modal --}}
    <x-modal id="add-location-modal" title="Add Verification Location" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.verification-locations.store') }}" class="space-y-4">
            @csrf
            <x-input label="Location Name" name="name" type="text" :value="old('name')" placeholder="e.g. Dhaka Central Hub, Inspection Facility" required />

            <x-input label="Street Address" name="address" type="text" :value="old('address')" placeholder="e.g. 123 Commercial Avenue, Level 4" required />

            <div class="grid grid-cols-2 gap-3">
                <x-input label="City" name="city" type="text" :value="old('city')" placeholder="e.g. Dhaka" required />
                <x-input label="Country" name="country" type="text" :value="old('country', 'Bangladesh')" placeholder="e.g. Bangladesh" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Phone (Optional)" name="phone" type="text" :value="old('phone')" placeholder="+8801700000000" />
                <x-input label="Email (Optional)" name="email" type="email" :value="old('email')" placeholder="hub@openbox.com" />
            </div>

            <div>
                <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', '1')" />
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Location</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Location Modals --}}
    @foreach ($locations as $location)
        <x-modal id="edit-location-modal-{{ $location->id }}" title="Edit Verification Location" maxWidth="max-w-xl">
            <form method="POST" action="{{ route('admin.verification-locations.update', $location) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Location Name" name="name" type="text" :value="old('name', $location->name)" required />

                <x-input label="Street Address" name="address" type="text" :value="old('address', $location->address)" required />

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="City" name="city" type="text" :value="old('city', $location->city)" required />
                    <x-input label="Country" name="country" type="text" :value="old('country', $location->country)" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Phone (Optional)" name="phone" type="text" :value="old('phone', $location->phone)" />
                    <x-input label="Email (Optional)" name="email" type="email" :value="old('email', $location->email)" />
                </div>

                <div>
                    <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', (string)(int)$location->is_active)" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Location</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-location-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
