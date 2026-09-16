@extends('layouts.admin')

@section('title', 'Warehouses')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Warehouses</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-warehouse-modal" size="sm">+ Add Warehouse</x-button>
        </x-slot:action>

        <x-table :headers="['Warehouse', 'Location', 'Manager', 'Capacity', 'Deposits', 'Status', 'Actions']" id="warehouses-table">
            @forelse ($warehouses as $warehouse)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="font-medium text-gray-900 hover:text-brand-600 flex items-center gap-2 group">
                            <div class="p-1.5 rounded bg-brand-50 text-brand-600 group-hover:bg-brand-100 transition shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <span class="underline-offset-2 group-hover:underline">{{ $warehouse->name }}</span>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <div>{{ $warehouse->city }}, {{ $warehouse->country }}</div>
                        <div class="text-xs text-gray-400 truncate max-w-xs">{{ $warehouse->address }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if ($warehouse->manager)
                            <div class="font-medium text-gray-900">{{ $warehouse->manager->name }}</div>
                            <div class="text-xs text-gray-400">{{ $warehouse->manager->email }}</div>
                        @else
                            <span class="text-gray-400 text-xs italic">Unassigned</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        @if ($warehouse->storage_capacity)
                            <span class="font-semibold text-gray-900">{{ number_format($warehouse->storage_capacity) }}</span> units
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge color="blue">{{ $warehouse->warehouse_products_count }} {{ $warehouse->warehouse_products_count === 1 ? 'deposit' : 'deposits' }}</x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$warehouse->is_active ? 'green' : 'gray'">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Manage Slots Icon --}}
                            <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Manage storage slots">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                            </a>

                            {{-- Toggle Active / Inactive Icon --}}
                            <form method="POST" action="{{ route('admin.warehouses.toggle-active', $warehouse) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($warehouse->is_active)
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
                            <button type="button" data-modal-open="edit-warehouse-modal-{{ $warehouse->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit warehouse">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.warehouses.destroy', $warehouse) }}" data-confirm="Remove this warehouse?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete warehouse">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No warehouses found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$warehouses" />
    </x-card>

    {{-- Add Warehouse Modal --}}
    <x-modal id="add-warehouse-modal" title="Add Warehouse Facility" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.warehouses.store') }}" class="space-y-4">
            @csrf
            <x-input label="Warehouse Name" name="name" type="text" :value="old('name')" placeholder="e.g. Central Fulfillment Center 1" required />

            <x-input label="Street Address" name="address" type="text" :value="old('address')" placeholder="e.g. Plot 45, Industrial Area, Tejgaon" required />

            <div class="grid grid-cols-2 gap-3">
                <x-input label="City" name="city" type="text" :value="old('city')" placeholder="e.g. Dhaka" required />
                <x-input label="Country" name="country" type="text" :value="old('country', 'Bangladesh')" placeholder="e.g. Bangladesh" required />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-select label="Warehouse Manager (Optional)" name="manager_id" placeholder="Unassigned" :options="$admins->pluck('name', 'id')" :selected="old('manager_id')" />
                <x-input label="Storage Capacity (Units)" name="storage_capacity" type="number" :value="old('storage_capacity')" placeholder="e.g. 5000" min="0" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <x-input label="Contact Phone (Optional)" name="phone" type="text" :value="old('phone')" placeholder="+8801700000000" />
                <x-input label="Contact Email (Optional)" name="email" type="email" :value="old('email')" placeholder="warehouse@openbox.com" />
            </div>

            <div>
                <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', '1')" />
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Warehouse</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Warehouse Modals --}}
    @foreach ($warehouses as $warehouse)
        <x-modal id="edit-warehouse-modal-{{ $warehouse->id }}" title="Edit Warehouse: {{ $warehouse->name }}" maxWidth="max-w-xl">
            <form method="POST" action="{{ route('admin.warehouses.update', $warehouse) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Warehouse Name" name="name" type="text" :value="old('name', $warehouse->name)" required />

                <x-input label="Street Address" name="address" type="text" :value="old('address', $warehouse->address)" required />

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="City" name="city" type="text" :value="old('city', $warehouse->city)" required />
                    <x-input label="Country" name="country" type="text" :value="old('country', $warehouse->country)" required />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-select label="Warehouse Manager (Optional)" name="manager_id" placeholder="Unassigned" :options="$admins->pluck('name', 'id')" :selected="old('manager_id', (string)$warehouse->manager_id)" />
                    <x-input label="Storage Capacity (Units)" name="storage_capacity" type="number" :value="old('storage_capacity', $warehouse->storage_capacity)" min="0" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Contact Phone (Optional)" name="phone" type="text" :value="old('phone', $warehouse->phone)" />
                    <x-input label="Contact Email (Optional)" name="email" type="email" :value="old('email', $warehouse->email)" />
                </div>

                <div>
                    <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', (string)(int)$warehouse->is_active)" />
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Warehouse</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-warehouse-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
