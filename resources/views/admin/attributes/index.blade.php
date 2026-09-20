@extends(request()->routeIs('business.*') ? 'layouts.business' : (request()->routeIs('saler.*') ? 'layouts.saler' : 'layouts.admin'))

@section('title', 'Attributes')

@php
    $portalPrefix = request()->routeIs('business.*') ? 'business.' : (request()->routeIs('saler.*') ? 'saler.' : 'admin.');
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route($portalPrefix . 'categories.builder.attributes') }}" class="text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← Open in Category Builder
            </a>
            <a href="{{ route($portalPrefix . 'attribute-groups.index') }}" class="text-xs font-medium px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-50 transition">
                Manage Attribute Groups
            </a>
        </div>
    </div>

    <x-card>
        <x-slot:title>All Attributes</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-attribute-modal" size="sm">+ Add Attribute</x-button>
        </x-slot:action>

        <x-table :headers="['Name', 'Group', 'Type', 'Unit', 'Flags', 'Values', 'Actions']" id="attributes-table">
            @forelse ($attributes as $attribute)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $attribute->name }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $attribute->attributeGroup?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        <x-badge color="gray">{{ $attribute->type->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $attribute->unit ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @if ($attribute->is_filterable)
                                <x-badge color="green">Filterable</x-badge>
                            @endif
                            @if ($attribute->is_variant)
                                <x-badge color="blue">Variant</x-badge>
                            @endif
                            @if (! $attribute->is_active)
                                <x-badge color="gray">Inactive</x-badge>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $attribute->values_count }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Edit / Manage Icon --}}
                            <a href="{{ route($portalPrefix . 'attributes.show', $attribute) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Manage & Edit attribute">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            @if (auth()->user()?->isAdmin())
                                <form method="POST" action="{{ route($portalPrefix . 'attributes.destroy', $attribute) }}" data-confirm="Delete this attribute?" class="inline-block m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete attribute">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No attributes yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$attributes" />
    </x-card>

    {{-- Add Attribute Modal --}}
    <x-modal id="add-attribute-modal" title="Add Attribute" maxWidth="max-w-lg">
        <form method="POST" action="{{ route($portalPrefix . 'attributes.store') }}" class="space-y-4">
            @csrf
            <x-input label="Attribute Name" name="name" type="text" :value="old('name')" placeholder="e.g. RAM, Storage, Screen Size" required />

            <x-select label="Attribute Group" name="attribute_group_id"
                      :options="collect($attributeGroups)->mapWithKeys(fn ($g) => [$g->id => $g->name])"
                      placeholder="General / Other"
                      :selected="old('attribute_group_id')" />

            <x-select label="Type" name="type"
                      :options="collect(\App\Enums\AttributeType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                      :selected="old('type', 'text')" />

            <x-textarea label="Unit (Optional)" name="unit" rows="2" placeholder="e.g. GB, TB, GHz, kg, cm, in, mAh, Watts (or specifications note)">{{ old('unit') }}</x-textarea>

            <div class="flex items-center gap-6 pt-1">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_filterable" value="1" class="w-4 h-4 rounded accent-brand-500" {{ old('is_filterable', true) ? 'checked' : '' }}>
                    <span>Filterable</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_variant" value="1" class="w-4 h-4 rounded accent-brand-500" {{ old('is_variant') ? 'checked' : '' }}>
                    <span>Variant</span>
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="is_required" value="1" class="w-4 h-4 rounded accent-brand-500" {{ old('is_required') ? 'checked' : '' }}>
                    <span>Required</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">Create Attribute</x-button>
            </div>
        </form>
    </x-modal>

    <script>
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('add-attribute-modal')?.classList.remove('hidden');
            });
        @endif
    </script>
@endsection
