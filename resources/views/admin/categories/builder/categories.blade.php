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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Tree View --}}
        <div class="lg:col-span-8 space-y-4">
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

                <div class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto pr-1" id="category-tree-list">
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
                                   class="text-xs font-medium text-gray-500 hover:text-brand-600 px-1.5 py-1">
                                    Edit
                                </a>

                                @if ($node['can_delete'])
                                    <form method="POST" action="{{ route('admin.categories.destroy', $node['id']) }}" data-confirm="Delete this category?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700 px-1.5 py-1">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="py-12 text-center text-sm text-gray-400">No categories found. Use the form on the right to create the first one.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        {{-- Right: Quick Add Category --}}
        <div class="lg:col-span-4">
            <x-card title="Quick Add Category">
                <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('admin.categories.builder.categories') }}">

                    <x-input label="Category Name" name="name" type="text" :value="old('name')" required />

                    <x-select label="Parent Category (Optional)" name="parent_id"
                              :options="collect($parents)->mapWithKeys(fn ($c) => [$c->id => $c->name])"
                              placeholder="None (Root Category)"
                              :selected="old('parent_id')" />

                    <div class="grid grid-cols-2 gap-3">
                        <x-select label="Status" name="status" :options="['active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived']" :selected="old('status', 'active')" />
                        <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', 0)" min="0" />
                    </div>

                    <x-textarea label="Description (Optional)" name="description" rows="3">{{ old('description') }}</x-textarea>

                    <x-button type="submit" class="w-full">Create Category</x-button>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        document.getElementById('category-search')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.category-tree-row').forEach(function(row) {
                const name = row.dataset.name || '';
                row.style.display = query === '' || name.includes(query) ? 'flex' : 'none';
            });
        });
    </script>
@endsection
