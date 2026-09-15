@extends('layouts.admin')

@section('title', 'Category Builder — Attributes')

@section('content')
    @php($active = 'attributes')
    @include('admin.categories.builder._tabs')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Attributes Table --}}
        <div class="lg:col-span-8 space-y-4">
            <x-card>
                <x-slot:title>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <span>Catalog Attributes</span>
                            <span class="text-xs font-normal text-gray-500">({{ $attributes->count() }} total)</span>
                        </div>
                    </div>
                </x-slot:title>

                <div class="grid sm:grid-cols-3 gap-3 mb-4">
                    <div class="sm:col-span-2">
                        <input type="text" id="attr-search" placeholder="Search attributes..."
                               class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    </div>
                    <div>
                        <select id="attr-group-filter" class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <option value="">All Groups</option>
                            @foreach ($attributeGroups as $grp)
                                <option value="{{ $grp->id }}">{{ $grp->name }}</option>
                            @endforeach
                            <option value="none">No Group (General)</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                    <table class="w-full text-left text-sm" id="attr-table">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 sticky top-0">
                            <tr>
                                <th class="px-3 py-2.5 font-semibold">Name</th>
                                <th class="px-3 py-2.5 font-semibold">Group</th>
                                <th class="px-3 py-2.5 font-semibold">Type</th>
                                <th class="px-3 py-2.5 font-semibold">Values</th>
                                <th class="px-3 py-2.5 font-semibold">Flags</th>
                                <th class="px-3 py-2.5 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($attributes as $attr)
                                <tr class="hover:bg-gray-50 transition attr-row"
                                    data-name="{{ strtolower($attr->name) }}"
                                    data-group-id="{{ $attr->attribute_group_id ?? 'none' }}">
                                    <td class="px-3 py-2.5">
                                        <div class="font-medium text-gray-900">{{ $attr->name }}</div>
                                        @if ($attr->unit)
                                            <div class="text-[11px] text-gray-400">Unit: {{ $attr->unit }}</div>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-gray-600">
                                        {{ $attr->attributeGroup?->name ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700">
                                            {{ $attr->type->label() }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-gray-600">
                                        @if ($attr->hasOptions())
                                            <a href="{{ route('admin.attributes.show', $attr) }}" class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100">
                                                {{ $attr->values_count }} {{ $attr->values_count === 1 ? 'option' : 'options' }}
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs">Free-form</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($attr->is_filterable)
                                                <span class="text-[10px] font-semibold bg-green-50 text-green-600 px-1.5 py-0.5 rounded">Filter</span>
                                            @endif
                                            @if ($attr->is_variant)
                                                <span class="text-[10px] font-semibold bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded">Variant</span>
                                            @endif
                                            @if ($attr->is_required)
                                                <span class="text-[10px] font-semibold bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded">Req</span>
                                            @endif
                                            @if (! $attr->is_active)
                                                <span class="text-[10px] font-semibold bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Off</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.attributes.show', $attr) }}" class="text-xs font-medium text-brand-600 hover:underline">
                                                Manage
                                            </a>
                                            <form method="POST" action="{{ route('admin.attributes.destroy', $attr) }}" data-confirm="Delete this attribute?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-sm text-gray-400">No attributes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        {{-- Right: Quick Add Attribute --}}
        <div class="lg:col-span-4">
            <x-card title="Quick Add Attribute">
                <form method="POST" action="{{ route('admin.attributes.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('admin.categories.builder.attributes') }}">

                    <x-input label="Attribute Name" name="name" type="text" placeholder="e.g. RAM, Color, Battery Life" :value="old('name')" required />

                    <x-select label="Specification Group" name="attribute_group_id"
                              :options="collect($attributeGroups)->mapWithKeys(fn ($g) => [$g->id => $g->name])"
                              placeholder="None / General Specifications"
                              :selected="old('attribute_group_id')" />

                    <x-select label="Input Type" name="type"
                              :options="collect($types)->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                              :selected="old('type', 'text')" />

                    <div class="grid grid-cols-2 gap-3">
                        <x-input label="Unit (optional)" name="unit" type="text" placeholder="GB, mAh, kg" :value="old('unit')" />
                        <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', 0)" min="0" />
                    </div>

                    <x-input label="Placeholder (optional)" name="placeholder" type="text" placeholder="e.g. Enter screen size..." :value="old('placeholder')" />

                    <div class="space-y-2 pt-1 border-t border-gray-100">
                        <label class="flex items-center gap-2 text-xs text-gray-700">
                            <input type="checkbox" name="is_filterable" value="1" class="w-4 h-4 rounded accent-brand-500" checked>
                            Filterable in storefront search
                        </label>
                        <label class="flex items-center gap-2 text-xs text-gray-700">
                            <input type="checkbox" name="is_variant" value="1" class="w-4 h-4 rounded accent-brand-500">
                            Usable for product variants (e.g. size/color)
                        </label>
                        <label class="flex items-center gap-2 text-xs text-gray-700">
                            <input type="checkbox" name="is_required" value="1" class="w-4 h-4 rounded accent-brand-500">
                            Required on product forms by default
                        </label>
                        <label class="flex items-center gap-2 text-xs text-gray-700">
                            <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded accent-brand-500" checked>
                            Active attribute
                        </label>
                    </div>

                    <x-button type="submit" class="w-full">Create Attribute</x-button>
                </form>
            </x-card>
        </div>
    </div>

    <script>
        const searchInput = document.getElementById('attr-search');
        const groupFilter = document.getElementById('attr-group-filter');

        function filterAttrs() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const group = groupFilter?.value || '';

            document.querySelectorAll('.attr-row').forEach(function(row) {
                const name = row.dataset.name || '';
                const rowGroup = row.dataset.groupId || '';

                const matchesName = query === '' || name.includes(query);
                const matchesGroup = group === '' || (group === 'none' ? rowGroup === 'none' : rowGroup === group);

                row.style.display = matchesName && matchesGroup ? '' : 'none';
            });
        }

        searchInput?.addEventListener('input', filterAttrs);
        groupFilter?.addEventListener('change', filterAttrs);
    </script>
@endsection
