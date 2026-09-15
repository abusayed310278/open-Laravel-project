@extends('layouts.admin')

@section('title', 'Warehouses')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Warehouse">
        <form method="POST" action="{{ route('admin.warehouses.store') }}" class="grid sm:grid-cols-3 gap-4">
            @csrf
            <x-input label="Name" name="name" type="text" />
            <x-input label="Address" name="address" type="text" />
            <x-input label="City" name="city" type="text" />
            <x-input label="Country" name="country" type="text" />
            <x-select label="Manager (optional)" name="manager_id" placeholder="Unassigned" :options="$admins->pluck('name', 'id')" />
            <x-input label="Storage capacity (optional)" name="storage_capacity" type="number" />
            <x-button type="submit" class="sm:col-span-3">Add Warehouse</x-button>
        </form>
    </x-card>

    <x-card title="Warehouses">
        <x-table :headers="['Name', 'Location', 'Manager', 'Deposits', 'Status', '']" id="warehouses-table">
            @forelse ($warehouses as $warehouse)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="hover:text-brand-600">{{ $warehouse->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $warehouse->city }}, {{ $warehouse->country }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $warehouse->manager?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $warehouse->warehouse_products_count }}</td>
                    <td class="px-4 py-3"><x-badge :color="$warehouse->is_active ? 'green' : 'gray'">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="text-brand-600 font-medium hover:underline text-sm">Manage Slots</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No warehouses yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
