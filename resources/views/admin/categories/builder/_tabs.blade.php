@php
    $tabs = [
        'categories'        => ['label' => '1. Categories Tree', 'route' => 'admin.categories.builder.categories', 'desc' => 'Manage category hierarchy'],
        'attribute-groups'  => ['label' => '2. Attribute Groups', 'route' => 'admin.categories.builder.attribute-groups', 'desc' => 'Specification sections'],
        'attributes'        => ['label' => '3. Attributes', 'route' => 'admin.categories.builder.attributes', 'desc' => 'Product specifications & inputs'],
        'assign'            => ['label' => '4. Assign to Category', 'route' => 'admin.categories.builder.assign', 'desc' => 'Matrix assignment & sync'],
    ];
@endphp

<div class="mb-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Category & Specification Builder</h1>
            <p class="text-sm text-gray-500 mt-0.5">A unified workspace to configure categories, specification groups, attributes, and category assignments.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.categories.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 px-3 py-1.5 rounded-md hover:bg-gray-50 transition">
                Classic Categories
            </a>
            <a href="{{ route('admin.attributes.index') }}" class="text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-200 px-3 py-1.5 rounded-md hover:bg-gray-50 transition">
                Classic Attributes
            </a>
        </div>
    </div>

    <div class="border-b border-gray-200">
        <nav class="flex gap-2 sm:gap-6 -mb-px overflow-x-auto">
            @foreach($tabs as $key => $tab)
                <a href="{{ route($tab['route']) }}"
                   class="py-3 px-2 sm:px-1 text-sm font-semibold border-b-2 whitespace-nowrap flex items-center gap-2 transition {{ ($active ?? '') === $key ? 'text-brand-600 border-brand-500' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
</div>
