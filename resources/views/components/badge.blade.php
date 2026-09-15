@props(['color' => 'gray'])

@php
    $colors = [
        'green' => 'bg-green-50 text-green-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'amber' => 'bg-brand-50 text-brand-600',
        'red' => 'bg-red-50 text-red-500',
        'gray' => 'bg-gray-100 text-gray-500',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded ' . ($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
