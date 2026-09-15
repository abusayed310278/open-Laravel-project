@extends('layouts.admin')

@section('title', $attribute->name)

@section('content')
    <x-breadcrumb :items="['Attributes' => route('admin.attributes.index'), $attribute->name => null]" />

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="grid md:grid-cols-2 gap-5">
        <x-card title="Attribute Details">
            <form method="POST" action="{{ route('admin.attributes.update', $attribute) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Name" name="name" type="text" :value="old('name', $attribute->name)" required />

                <x-select label="Specification Group" name="attribute_group_id"
                          :options="collect($attributeGroups)->mapWithKeys(fn ($g) => [$g->id => $g->name])"
                          placeholder="None / General"
                          :selected="old('attribute_group_id', $attribute->attribute_group_id)" />

                <x-select label="Type" name="type"
                          :options="collect(\App\Enums\AttributeType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                          :selected="old('type', $attribute->type->value)" />

                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Unit (optional)" name="unit" type="text" :value="old('unit', $attribute->unit)" placeholder="GB, kg, in" />
                    <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', $attribute->sort_order)" min="0" />
                </div>

                <x-input label="Placeholder" name="placeholder" type="text" :value="old('placeholder', $attribute->placeholder)" placeholder="e.g. Select color..." />

                <div class="space-y-2 pt-2 border-t border-gray-100 text-sm text-gray-700">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $attribute->is_filterable)) class="w-4 h-4 rounded accent-brand-500">
                        Filterable on shop page
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_variant" value="1" @checked(old('is_variant', $attribute->is_variant)) class="w-4 h-4 rounded accent-brand-500">
                        Can be used for product variants
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $attribute->is_required)) class="w-4 h-4 rounded accent-brand-500">
                        Required by default
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $attribute->is_active)) class="w-4 h-4 rounded accent-brand-500">
                        Active attribute
                    </label>
                </div>

                <x-button type="submit">Save Changes</x-button>
            </form>
        </x-card>

        @if ($attribute->type->usesValueList())
            <x-card title="Predefined Options & Values">
                <form method="POST" action="{{ route('admin.attributes.values.store', $attribute) }}" class="flex items-end gap-3 mb-5">
                    @csrf
                    <div class="flex-1">
                        <x-input label="New value / option" name="value" type="text" placeholder="e.g. Red, XL, 128GB" required />
                    </div>
                    @if ($attribute->type === \App\Enums\AttributeType::Color)
                        <div class="w-28">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Color Hex</label>
                            <input type="color" name="color_hex" class="w-full h-9 p-0.5 rounded border border-gray-200 cursor-pointer">
                        </div>
                    @endif
                    <x-button type="submit" size="sm">Add Value</x-button>
                </form>

                <div class="divide-y divide-gray-50 max-h-[480px] overflow-y-auto">
                    @forelse ($attribute->values as $value)
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-2">
                                @if ($value->color_hex)
                                    <span class="w-4 h-4 rounded-full border border-gray-200 inline-block shrink-0 shadow-xs" style="background-color: {{ $value->color_hex }}"></span>
                                @endif
                                <span class="text-sm font-medium text-gray-800">{{ $value->value }}</span>
                                @if ($value->color_hex)
                                    <span class="text-xs text-gray-400 font-mono">{{ $value->color_hex }}</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.attributes.values.destroy', [$attribute, $value]) }}" data-confirm="Remove this value?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 py-6 text-center">No options defined yet. Add choices like sizes, colors, or storage capacities above.</p>
                    @endforelse
                </div>
            </x-card>
        @endif
    </div>
@endsection
