@extends('layouts.admin')

@section('title', 'Receive Product')

@section('content')
    <x-breadcrumb :items="['Warehouse Deposits' => route('admin.warehouse-products.index'), $entry->product->title => null]" />

    <x-card class="max-w-2xl">
        <x-slot:title>Receive &amp; Store</x-slot:title>

        <p class="text-sm text-gray-500 mb-5">
            {{ $entry->product->title }} · from {{ $entry->seller->name }} · destined for {{ $entry->warehouse->name }}
        </p>

        @if ($availableLocations->isEmpty())
            <x-alert type="error">No available slots at this warehouse. Add one before receiving this item.</x-alert>
        @else
            <form method="POST" action="{{ route('admin.warehouse-products.receive.store', $entry) }}" class="space-y-5">
                @csrf

                <x-select
                    label="Assign storage slot" name="warehouse_location_id"
                    :options="$availableLocations->mapWithKeys(fn ($loc) => [$loc->id => $loc->label()])"
                />

                <x-select label="Condition on arrival" name="condition_at_receipt" placeholder="Not assessed" :options="['A' => 'Grade A', 'B' => 'Grade B', 'C' => 'Grade C']" />

                <x-textarea label="Condition notes" name="condition_notes" rows="3" />

                <x-button type="submit">Confirm Receipt</x-button>
            </form>
        @endif
    </x-card>
@endsection
