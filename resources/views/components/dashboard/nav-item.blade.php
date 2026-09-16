@props(['href' => '#', 'icon', 'active' => false])

@php
    $label = trim((string) $slot);
@endphp

<a
    href="{{ $href }}"
    title="{{ $label }}"
    @class([
        'sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all group relative',
        'bg-brand-50 text-brand-600 font-semibold' => $active,
        'text-gray-600 hover:bg-gray-50 hover:text-gray-950' => !$active,
    ])
>
    <svg class="w-4 h-4 shrink-0 transition-transform duration-150 group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
    </svg>
    <span class="sidebar-text truncate transition-opacity duration-200">{{ $slot }}</span>
</a>
