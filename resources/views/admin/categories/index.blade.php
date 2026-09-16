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

        <x-table :headers="['Name', 'Parent', 'Status', 'Sort', 'Attributes', 'Actions']" id="categories-table">
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
                        <div class="inline-flex items-center justify-end gap-1">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Edit category">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete this category?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete category">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
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
