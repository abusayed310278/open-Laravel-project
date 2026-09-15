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
            <x-button as="a" :href="route('admin.attribute-groups.create')" size="sm">Add Attribute Group</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Slug', 'Attributes', 'Status', 'Sort', '']" id="attribute-groups-table">
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
                        <div class="flex items-center justify-end gap-3 text-xs">
                            <form method="POST" action="{{ route('admin.attribute-groups.toggle-active', $group) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-gray-600 hover:text-gray-900">
                                    {{ $group->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            <a href="{{ route('admin.attribute-groups.edit', $group) }}" class="text-brand-600 font-medium hover:underline">Edit</a>

                            @if ($group->attributes_count === 0)
                                <form method="POST" action="{{ route('admin.attribute-groups.destroy', $group) }}" data-confirm="Delete this group?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
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

        @if ($groups->hasPages())
            <div class="mt-4">
                {{ $groups->links() }}
            </div>
        @endif
    </x-card>
@endsection
