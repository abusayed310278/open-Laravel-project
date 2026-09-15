@extends('layouts.admin')

@section('title', 'Attributes')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.builder.attributes') }}" class="text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← Open in Category Builder
            </a>
            <a href="{{ route('admin.attribute-groups.index') }}" class="text-xs font-medium px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-md hover:bg-gray-50 transition">
                Manage Attribute Groups
            </a>
        </div>
    </div>

    <x-card title="Add Attribute">
        <form method="POST" action="{{ route('admin.attributes.store') }}" class="grid sm:grid-cols-5 gap-4 items-end">
            @csrf
            <x-input label="Name" name="name" type="text" :value="old('name')" required />
            <x-select label="Group" name="attribute_group_id" :options="collect($attributeGroups)->mapWithKeys(fn ($g) => [$g->id => $g->name])" placeholder="General / Other" />
            <x-select label="Type" name="type" :options="collect(\App\Enums\AttributeType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])" />
            <x-input label="Unit (optional)" name="unit" type="text" placeholder="GB, kg, in" />
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_filterable" value="1" class="w-4 h-4 rounded accent-brand-500" checked>
                    Filterable
                </label>
                <x-button type="submit">Add</x-button>
            </div>
        </form>
    </x-card>

    <x-card title="All Attributes">
        <x-table :headers="['Name', 'Group', 'Type', 'Unit', 'Flags', 'Values', '']" id="attributes-table">
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
                        <div class="flex items-center justify-end gap-3 text-xs">
                            <a href="{{ route('admin.attributes.show', $attribute) }}" class="text-brand-600 font-medium hover:underline">Manage</a>
                            <form method="POST" action="{{ route('admin.attributes.destroy', $attribute) }}" data-confirm="Delete this attribute?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No attributes yet.</td>
                </tr>
            @endforelse
        </x-table>

        @if ($attributes->hasPages())
            <div class="mt-4">
                {{ $attributes->links() }}
            </div>
        @endif
    </x-card>
@endsection
