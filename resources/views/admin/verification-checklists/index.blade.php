@extends('layouts.admin')

@section('title', 'Verification Checklists')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Checklist Item">
        <form method="POST" action="{{ route('admin.verification-checklists.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
            @csrf
            <x-select label="Category" name="category_id" placeholder="All categories" :options="$categories->pluck('name', 'id')" />
            <x-input label="Item name" name="item_name" type="text" />
            <x-input label="Description (optional)" name="description" type="text" />
            <label class="flex items-center gap-2 text-sm text-gray-600 pb-2.5">
                <input type="checkbox" name="is_required" value="1" checked class="w-4 h-4 rounded accent-brand-500">
                Required
            </label>
            <x-button type="submit" class="sm:col-span-4">Add Item</x-button>
        </form>
    </x-card>

    <x-card title="Checklist">
        <x-table :headers="['Item', 'Category', 'Required', '']" id="checklists-table">
            @forelse ($items as $item)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                        @if ($item->description)
                            <p class="text-xs text-gray-400">{{ $item->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->category?->name ?? 'All categories' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $item->is_required ? 'Yes' : 'Optional' }}</td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.verification-checklists.destroy', $item) }}" data-confirm="Remove this checklist item?">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No checklist items yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
