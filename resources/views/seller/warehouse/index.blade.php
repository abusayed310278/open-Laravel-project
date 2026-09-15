@extends('layouts.saler')

@section('title', 'Warehouse')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @if ($eligibleProducts->isNotEmpty() && $warehouses->isNotEmpty())
        <x-card title="Deposit a Verified Product">
            <p class="text-sm text-gray-500 mb-4">Only verified products that aren't already deposited can be sent to a warehouse.</p>
            <form method="POST" action="{{ route('saler.warehouse.deposit') }}" class="grid sm:grid-cols-3 gap-4 items-end">
                @csrf
                <x-select label="Product" name="product_id" :options="$eligibleProducts->pluck('title', 'id')" />
                <x-select label="Warehouse" name="warehouse_id" :options="$warehouses->pluck('name', 'id')" />
                <x-button type="submit">Request Deposit</x-button>
            </form>
        </x-card>
    @endif

    <x-card title="Your Deposits">
        <x-table :headers="['Product', 'Warehouse', 'Slot', 'Status']" id="warehouse-table">
            @forelse ($entries as $entry)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $entry->product->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->warehouse->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">{{ $entry->location?->label() ?? '—' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$entry->storage_status->badgeColor()">{{ $entry->storage_status->label() }}</x-badge></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No warehouse deposits yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$entries" />
    </x-card>
@endsection
