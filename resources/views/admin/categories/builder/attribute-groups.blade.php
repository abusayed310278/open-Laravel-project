@extends('layouts.admin')

@section('title', 'Category Builder — Attribute Groups')

@section('content')
    @php($active = 'attribute-groups')
    @include('admin.categories.builder._tabs')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Groups List --}}
        <div class="lg:col-span-8 space-y-4">
            <x-card>
                <x-slot:title>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <span>Specification Groups</span>
                            <span class="text-xs font-normal text-gray-500">({{ $groups->count() }} total)</span>
                        </div>
                    </div>
                </x-slot:title>

                <div class="mb-4">
                    <input type="text" id="group-search" placeholder="Search attribute groups..."
                           class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <div class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto pr-1" id="group-list">
                    @forelse ($groups as $group)
                        <div class="py-3 px-2 hover:bg-gray-50 rounded flex items-center justify-between gap-3 text-sm group-row"
                             data-name="{{ strtolower($group->name) }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 truncate">{{ $group->name }}</span>
                                    @if (! $group->is_active)
                                        <x-badge color="gray">Inactive</x-badge>
                                    @endif
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                                        {{ $group->attributes_count }} {{ $group->attributes_count === 1 ? 'attribute' : 'attributes' }}
                                    </span>
                                </div>
                                @if ($group->description)
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-1">{{ $group->description }}</p>
                                @endif

                                @if ($group->attributes->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach ($group->attributes->take(5) as $attr)
                                            <span class="inline-flex items-center text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                                {{ $attr->name }}
                                            </span>
                                        @endforeach
                                        @if ($group->attributes->count() > 5)
                                            <span class="text-[10px] text-gray-400 self-center">+{{ $group->attributes->count() - 5 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('admin.attribute-groups.toggle-active', $group) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-xs font-medium px-2 py-1 rounded {{ $group->is_active ? 'text-gray-500 hover:text-gray-700 bg-gray-50' : 'text-green-600 bg-green-50' }}">
                                        {{ $group->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <a href="{{ route('admin.attribute-groups.edit', $group) }}"
                                   class="text-xs font-medium text-gray-500 hover:text-brand-600 px-1.5 py-1">
                                    Edit
                                </a>

                                @if ($group->attributes_count === 0)
                                    <form method="POST" action="{{ route('admin.attribute-groups.destroy', $group) }}" data-confirm="Delete this attribute group?">
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
                        <p class="py-12 text-center text-sm text-gray-400">No attribute groups found. Add groups like "Technical Specs", "Dimensions", or "Display" using the form.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        {{-- Right: Quick Add Attribute Group --}}
        <div class="lg:col-span-4">
            <x-card title="Quick Add Group">
                <form method="POST" action="{{ route('admin.attribute-groups.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('admin.categories.builder.attribute-groups') }}">

                    <x-input label="Group Name" name="name" type="text" placeholder="e.g. Technical Specifications" :value="old('name')" required />
                    <x-input label="Slug (optional)" name="slug" type="text" placeholder="auto-generated if empty" :value="old('slug')" />
                    <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', 0)" min="0" />
                    <x-textarea label="Description (optional)" name="description" rows="3">{{ old('description') }}</x-textarea>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded accent-brand-500">
                        Active specification group
                    </label>

                    <x-button type="submit" class="w-full">Create Attribute Group</x-button>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        document.getElementById('group-search')?.addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.group-row').forEach(function(row) {
                const name = row.dataset.name || '';
                row.style.display = query === '' || name.includes(query) ? 'flex' : 'none';
            });
        });
    </script>
@endsection
