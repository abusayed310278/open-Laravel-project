@extends('layouts.admin')

@section('title', 'Verification Checklists')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Verification Checklists</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-checklist-modal" size="sm">+ Add Checklist Item</x-button>
        </x-slot:action>

        <x-table :headers="['Item Name', 'Category', 'Requirement', 'Actions']" id="checklists-table">
            @forelse ($items as $item)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-start gap-2.5">
                            <div class="p-1.5 rounded bg-brand-50 text-brand-600 mt-0.5 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                                @if ($item->description)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($item->category)
                            <x-badge color="blue">{{ $item->category->name }}</x-badge>
                        @else
                            <x-badge color="gray">All categories</x-badge>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$item->is_required ? 'amber' : 'gray'">{{ $item->is_required ? 'Required' : 'Optional' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Required / Optional Icon --}}
                            <form method="POST" action="{{ route('admin.verification-checklists.toggle-required', $item) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($item->is_required)
                                    <button type="submit" class="p-1.5 text-amber-600 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Required (Click to make Optional)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Optional (Click to make Required)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <button type="button" data-modal-open="edit-checklist-modal-{{ $item->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit checklist item">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.verification-checklists.destroy', $item) }}" data-confirm="Remove this checklist item?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete checklist item">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No checklist items found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$items" />
    </x-card>

    {{-- Add Checklist Modal --}}
    <x-modal id="add-checklist-modal" title="Add Verification Checklist Item" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.verification-checklists.store') }}" class="space-y-4">
            @csrf
            <x-input label="Item Name" name="item_name" type="text" :value="old('item_name')" placeholder="e.g. Screen & Touch Sensitivity Test, IMEI Check" required />

            <x-select label="Category (Optional)" name="category_id" placeholder="All categories (Universal)" :options="$categories->pluck('name', 'id')" :selected="old('category_id')" />

            <x-textarea label="Description (Optional)" name="description" rows="3" placeholder="Explain what the verifier needs to inspect or verify...">{{ old('description') }}</x-textarea>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="add_is_required" name="is_required" value="1" @checked(old('is_required', '1') == '1') class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                <label for="add_is_required" class="text-sm font-medium text-gray-700 cursor-pointer">
                    Mark as mandatory requirement for verification
                </label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Save Checklist Item</x-button>
            </div>
        </form>
    </x-modal>

    {{-- Edit Checklist Modals --}}
    @foreach ($items as $item)
        <x-modal id="edit-checklist-modal-{{ $item->id }}" title="Edit Checklist Item" maxWidth="max-w-lg">
            <form method="POST" action="{{ route('admin.verification-checklists.update', $item) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <x-input label="Item Name" name="item_name" type="text" :value="old('item_name', $item->item_name)" required />

                <x-select label="Category (Optional)" name="category_id" placeholder="All categories (Universal)" :options="$categories->pluck('name', 'id')" :selected="old('category_id', (string)$item->category_id)" />

                <x-textarea label="Description (Optional)" name="description" rows="3">{{ old('description', $item->description) }}</x-textarea>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit_is_required_{{ $item->id }}" name="is_required" value="1" @checked(old('is_required', $item->is_required ? '1' : '0') == '1') class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                    <label for="edit_is_required_{{ $item->id }}" class="text-sm font-medium text-gray-700 cursor-pointer">
                        Mark as mandatory requirement for verification
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Update Item</x-button>
                </div>
            </form>
        </x-modal>
    @endforeach

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-checklist-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
