@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Category Builder — Attribute Groups')

@php
    $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
@endphp

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

                            <div class="flex items-center gap-1 shrink-0">
                                <form method="POST" action="{{ route($portalPrefix . 'attribute-groups.toggle-active', $group) }}" class="inline-block m-0">
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

                                <a href="{{ route($portalPrefix . 'attribute-groups.edit', $group) }}"
                                   class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer"
                                   title="Edit attribute group">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>

                                @if ($group->attributes_count === 0)
                                    <form method="POST" action="{{ route($portalPrefix . 'attribute-groups.destroy', $group) }}" data-confirm="Delete this attribute group?" class="inline-block m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete attribute group">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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
                <form method="POST" action="{{ route($portalPrefix . 'attribute-groups.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route($portalPrefix . 'categories.builder.attribute-groups') }}">

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
