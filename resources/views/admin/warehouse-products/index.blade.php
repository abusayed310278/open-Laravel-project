@extends('layouts.admin')

@section('title', 'Warehouse Deposits')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Deposits</x-slot:title>

        <form method="GET" class="mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Product', 'Seller', 'Warehouse', 'Slot', 'Status', '']" id="warehouse-products-table">
            @forelse ($entries as $entry)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $entry->product->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->seller->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $entry->warehouse->name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">{{ $entry->location?->label() ?? '—' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$entry->storage_status->badgeColor()">{{ $entry->storage_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        @if ($entry->storage_status->value === 'pending_delivery')
                            <a href="{{ route('admin.warehouse-products.receive', $entry) }}" class="text-brand-600 font-medium hover:underline text-sm">Receive</a>
                        @elseif ($entry->storage_status->value === 'stored')
                            <button type="button" data-modal-open="release-{{ $entry->id }}" class="text-red-500 font-medium hover:text-red-700 text-sm">Release</button>
                            <x-modal :id="'release-'.$entry->id" title="Release from storage">
                                <form method="POST" action="{{ route('admin.warehouse-products.release', $entry) }}" class="space-y-4">
                                    @csrf
                                    <x-textarea name="reason" label="Reason" rows="3" />
                                    <x-button type="submit" variant="danger">Release</x-button>
                                </form>
                            </x-modal>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No deposits yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$entries" />
    </x-card>
@endsection
