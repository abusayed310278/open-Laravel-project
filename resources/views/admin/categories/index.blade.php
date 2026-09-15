@extends('layouts.admin')

@section('title', 'Categories')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('category')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.builder.categories') }}" class="text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← Open in Category Builder Workspace
            </a>
            <a href="{{ route('admin.categories.builder.assign') }}" class="text-xs font-medium px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-50 transition">
                Specification Assignment Matrix
            </a>
        </div>
    </div>

    <x-card>
        <x-slot:title>Categories</x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route('admin.categories.create')" size="sm">Add Category</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Parent', 'Status', 'Sort', 'Attributes', '']" id="categories-table">
            @forelse ($categories as $category)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        @if ($category->parent_id)
                            <span class="text-gray-300 select-none">└</span>
                        @endif
                        {{ $category->name }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $category->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$category->status->value === 'active' ? 'green' : 'gray'">{{ $category->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $category->sort_order }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.categories.attributes', $category) }}"
                           class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 hover:bg-brand-100 transition">
                            Manage Specs
                        </a>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-3 text-xs">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-brand-600 font-medium hover:underline">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete this category?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No categories yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
