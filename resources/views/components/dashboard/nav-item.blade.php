@props(['href' => '#', 'icon', 'active' => false])

<a
    href="{{ $href }}"
    @class([
        'flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-colors',
        'bg-brand-50 text-brand-600' => $active,
        'text-gray-500 hover:bg-gray-50 hover:text-gray-800' => !$active,
    ])
>
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}" />
    </svg>
    <span>{{ $slot }}</span>
</a>
