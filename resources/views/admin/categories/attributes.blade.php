@extends('layouts.admin')

@section('title', $category->name . ' — Specifications & Attributes')

@section('content')
    <x-breadcrumb :items="[
        'Categories' => route('admin.categories.index'),
        'Builder' => route('admin.categories.builder.assign', ['category' => $category->id]),
        $category->name => route('admin.categories.edit', $category),
        'Attributes' => null,
    ]" />

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="error">{{ $value }}</x-alert>
    @endsession

    @session('info')
        <x-alert type="info">{{ $value }}</x-alert>
    @endsession

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $category->name }} — Category Specifications</h1>
            <p class="text-xs text-gray-500 mt-1">
                Breadcrumb: <span class="font-medium text-gray-700">{{ $category->getBreadcrumbPath() }}</span>
                • <span class="text-brand-600 font-semibold">{{ $totalAssignedCount }} attributes assigned</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.builder.assign', ['category' => $category->id]) }}"
               class="inline-flex items-center text-xs font-semibold px-3 py-1.5 bg-brand-50 text-brand-600 rounded-md hover:bg-brand-100 transition">
                ← Open in Assign Matrix
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Left: Grouped Assigned Attributes --}}
        <div class="lg:col-span-8 space-y-6">
            @forelse ($groupedAttributes as $groupData)
                <x-card>
                    <x-slot:title>
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-gray-900">{{ $groupData['group_name'] }}</span>
                                <span class="text-xs font-normal text-gray-400">({{ count($groupData['attributes']) }})</span>
                            </div>
                        </div>
                    </x-slot:title>

                    <div class="divide-y divide-gray-100">
                        @foreach ($groupData['attributes'] as $attr)
                            <div class="py-3.5 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900 text-sm">{{ $attr->name }}</span>
                                            <span class="text-[11px] font-mono px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">
                                                {{ $attr->type->label() }}
                                            </span>
                                            @if ($attr->unit)
                                                <span class="text-xs text-gray-400">({{ $attr->unit }})</span>
                                            @endif
                                        </div>

                                        @if ($attr->hasOptions() && $attr->values->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 mt-1 text-[11px] text-gray-500">
                                                <span>Options:</span>
                                                @foreach ($attr->values->take(6) as $val)
                                                    <span class="px-1.5 py-0.5 bg-gray-50 border border-gray-100 rounded text-gray-700">
                                                        {{ $val->value }}
                                                    </span>
                                                @endforeach
                                                @if ($attr->values->count() > 6)
                                                    <span class="text-gray-400 self-center">+{{ $attr->values->count() - 6 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('admin.categories.attributes.destroy', [$category, $attr]) }}" data-confirm="Remove '{{ $attr->name }}' from {{ $category->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 hover:underline">
                                            Detach
                                        </button>
                                    </form>
                                </div>

                                {{-- Inline Pivot Flags Editor --}}
                                <form method="POST" action="{{ route('admin.categories.attributes.update', [$category, $attr]) }}"
                                      class="flex flex-wrap items-center gap-4 bg-gray-50/60 p-2.5 rounded-md border border-gray-100 text-xs">
                                    @csrf
                                    @method('PUT')

                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="is_required" value="1"
                                               @checked($attr->pivot->is_required)
                                               class="w-3.5 h-3.5 rounded accent-brand-500">
                                        <span class="font-medium text-gray-700">Required</span>
                                    </label>

                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="is_filterable" value="1"
                                               @checked($attr->pivot->is_filterable)
                                               class="w-3.5 h-3.5 rounded accent-brand-500">
                                        <span class="font-medium text-gray-700">Filterable</span>
                                    </label>

                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="checkbox" name="is_variant" value="1"
                                               @checked($attr->pivot->is_variant)
                                               class="w-3.5 h-3.5 rounded accent-brand-500">
                                        <span class="font-medium text-gray-700">Variant</span>
                                    </label>

                                    <div class="flex items-center gap-1.5 ml-auto">
                                        <span class="text-gray-500">Order:</span>
                                        <input type="number" name="sort_order" value="{{ $attr->pivot->sort_order }}" min="0"
                                               class="w-14 px-1.5 py-0.5 rounded border border-gray-200 bg-white text-xs text-center">
                                        <button type="submit" class="ml-1 text-xs font-medium text-brand-600 hover:text-brand-800 bg-white px-2 py-0.5 border border-brand-300 rounded shadow-xs">
                                            Save
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @empty
                <x-card>
                    <div class="py-12 text-center text-gray-400 space-y-2">
                        <p class="text-sm font-medium text-gray-600">No attributes currently assigned to this category.</p>
                        <p class="text-xs text-gray-400">Use the form on the right or the Assign Matrix to attach specification fields.</p>
                    </div>
                </x-card>
            @endforelse
        </div>

        {{-- Right: Attach Existing Attribute or Add New --}}
        <div class="lg:col-span-4 space-y-5">
            <x-card title="Assign Attribute">
                @if ($availableAttributes->isNotEmpty())
                    <form method="POST" action="{{ route('admin.categories.attributes.store', $category) }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Choose Attribute</label>
                            <select name="attribute_id" required class="w-full text-sm rounded-md border border-gray-200 px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                                <option value="">Select an attribute...</option>
                                @foreach ($availableAttributes as $avail)
                                    <option value="{{ $avail->id }}">
                                        {{ $avail->name }} ({{ $avail->attributeGroup?->name ?? 'General' }} - {{ $avail->type->label() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 pt-2 border-t border-gray-100 text-xs text-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_required" value="1" class="w-4 h-4 rounded accent-brand-500">
                                Required field for products in this category
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_filterable" value="1" checked class="w-4 h-4 rounded accent-brand-500">
                                Filterable in shop sidebar
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_variant" value="1" class="w-4 h-4 rounded accent-brand-500">
                                Variant attribute (e.g. Size, Color)
                            </label>
                        </div>

                        <x-input label="Sort Order" name="sort_order" type="number" value="0" min="0" />

                        <x-button type="submit" class="w-full">Assign to {{ $category->name }}</x-button>
                    </form>
                @else
                    <p class="text-xs text-gray-500 py-4 text-center">All existing attributes are already assigned to this category!</p>
                @endif
            </x-card>

            <x-card title="Need a new attribute?">
                <p class="text-xs text-gray-500 mb-3">Create a new global specification attribute and have it automatically attached here.</p>
                <form method="POST" action="{{ route('admin.attributes.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="assign_to_category_id" value="{{ $category->id }}">
                    <input type="hidden" name="redirect_to" value="{{ route('admin.categories.attributes', $category) }}">

                    <x-input label="Attribute Name" name="name" type="text" placeholder="e.g. Screen Size" required />

                    <x-select label="Group" name="attribute_group_id"
                              :options="collect($attributeGroups)->mapWithKeys(fn ($g) => [$g->id => $g->name])"
                              placeholder="General / Other" />

                    <x-select label="Type" name="type"
                              :options="collect(\App\Enums\AttributeType::cases())->mapWithKeys(fn ($t) => [$t->value => $t->label()])"
                              selected="text" />

                    <x-input label="Unit (optional)" name="unit" type="text" placeholder="in, cm, GB" />

                    <x-button type="submit" class="w-full" size="sm">Create & Assign</x-button>
                </form>
            </x-card>
        </div>
    </div>
@endsection
