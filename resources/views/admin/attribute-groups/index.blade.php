@extends('layouts.admin')

@section('title', 'Attribute Groups')

@section('content')
    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.builder.attribute-groups') }}" class="text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← Open in Category Builder
            </a>
        </div>
    </div>

    <x-card>
        <x-slot:title>Attribute Groups</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-attribute-group-modal" size="sm">+ Add Attribute Group</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Slug', 'Attributes', 'Status', 'Sort', 'Actions']" id="attribute-groups-table">
            @forelse ($groups as $group)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $group->name }}
                        @if ($group->description)
                            <div class="text-xs text-gray-400 truncate max-w-xs">{{ $group->description }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">{{ $group->slug }}</td>
                    <td class="px-4 py-3">
                        <x-badge color="blue">{{ $group->attributes_count }} {{ $group->attributes_count === 1 ? 'attribute' : 'attributes' }}</x-badge>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$group->is_active ? 'green' : 'gray'">{{ $group->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $group->sort_order }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Active / Inactive Icon --}}
                            <form method="POST" action="{{ route('admin.attribute-groups.toggle-active', $group) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($group->is_active)
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
                            <a href="{{ route('admin.attribute-groups.edit', $group) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit attribute group">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            @if ($group->attributes_count === 0)
                                <form method="POST" action="{{ route('admin.attribute-groups.destroy', $group) }}" data-confirm="Delete this group?" class="inline-block m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete attribute group">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No attribute groups found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$groups" />
    </x-card>

    {{-- Add Attribute Group Modal --}}
    <x-modal id="add-attribute-group-modal" title="Add Attribute Group" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.attribute-groups.store') }}" class="space-y-4">
            @csrf
            <x-input label="Group Name" name="name" type="text" :value="old('name')" placeholder="e.g. Technical Specifications, Display, Dimensions" required />

            <x-input label="Slug (Optional)" name="slug" type="text" :value="old('slug')" placeholder="Auto-generated if left blank" />

            <div class="grid grid-cols-2 gap-3">
                <x-select label="Status" name="is_active" :options="['1' => 'Active', '0' => 'Inactive']" :selected="old('is_active', '1')" />
                <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', 0)" min="0" />
            </div>

            <x-textarea label="Description (Optional)" name="description" rows="3" placeholder="Brief description of this specification group...">{{ old('description') }}</x-textarea>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Create Group</x-button>
            </div>
        </form>
    </x-modal>

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-attribute-group-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
