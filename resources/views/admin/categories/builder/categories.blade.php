@extends('layouts.admin')

@section('title', 'Category Builder — Categories Tree')

@section('content')
    @php($active = 'categories')
    @include('admin.categories.builder._tabs')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('category')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <div class="space-y-4">
        <x-card>
            <x-slot:title>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <span>Category Hierarchy</span>
                        <span class="text-xs font-normal text-gray-500">({{ count($tree) }} total)</span>
                    </div>
                </div>
            </x-slot:title>

            <div class="mb-4">
                <input type="text" id="category-search" placeholder="Search categories..."
                       class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>

            <div class="divide-y divide-gray-50 max-h-[650px] overflow-y-auto pr-1" id="category-tree-list">
                @forelse ($tree as $node)
                    <div class="py-2.5 px-2 hover:bg-gray-50 rounded flex items-center justify-between gap-3 text-sm category-tree-row"
                         data-name="{{ strtolower($node['name']) }}"
                         data-parent-id="{{ $node['parent_id'] ?? '' }}"
                         data-id="{{ $node['id'] }}">
                        <div class="flex items-center gap-2 min-w-0" style="padding-left: {{ $node['depth'] * 24 }}px">
                            @if ($node['depth'] > 0)
                                <span class="text-gray-300 select-none font-mono">└─</span>
                            @endif
                            <span class="font-medium text-gray-900 truncate">{{ $node['name'] }}</span>

                            @if ($node['status'] !== 'active')
                                <x-badge color="gray">Inactive</x-badge>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('admin.categories.attributes', $node['id']) }}"
                               class="text-xs font-medium px-2 py-0.5 rounded-full bg-brand-50 text-brand-600 hover:bg-brand-100 transition"
                               title="Manage category specification attributes">
                                {{ $node['attributes_count'] ?? 0 }} {{ ($node['attributes_count'] ?? 0) === 1 ? 'attr' : 'attrs' }}
                            </a>

                            <span class="text-xs text-gray-400">
                                {{ $node['products_count'] }} {{ $node['products_count'] === 1 ? 'prod' : 'prods' }}
                            </span>

                            <a href="{{ route('admin.categories.edit', $node['id']) }}"
                               class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition"
                               title="Edit category">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            @if ($node['can_delete'])
                                <form method="POST" action="{{ route('admin.categories.destroy', $node['id']) }}" data-confirm="Delete this category?" class="inline-block m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete category">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-12 text-center text-sm text-gray-400">No categories found. Click "Quick Add Category" above to create the first one.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    {{-- Quick Add Category Modal --}}
    <x-modal id="add-category-modal" title="Quick Add Category" maxWidth="max-w-lg">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="redirect_to" value="{{ route('admin.categories.builder.categories') }}">

            <x-input label="Category Name" name="name" type="text" :value="old('name')" required placeholder="e.g. Laptops & Notebooks" />

            <x-select label="Parent Category (Optional)" name="parent_id"
                      :options="collect($parents)->mapWithKeys(fn ($c) => [$c->id => $c->name])"
                      placeholder="None (Root Category)"
                      :selected="old('parent_id')" />

            <div class="grid grid-cols-2 gap-3">
                <x-select label="Status" name="status" :options="['active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived']" :selected="old('status', 'active')" />
                <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', 0)" min="0" />
            </div>

            <x-textarea label="Description (Optional)" name="description" rows="3" placeholder="Short description of this category...">{{ old('description') }}</x-textarea>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Create Category</x-button>
            </div>
        </form>
    </x-modal>

    <script>
        document.getElementById('category-search')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.category-tree-row').forEach(function(row) {
                const name = row.dataset.name || '';
                row.style.display = query === '' || name.includes(query) ? 'flex' : 'none';
            });
        });

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-category-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
