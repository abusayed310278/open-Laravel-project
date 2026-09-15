@props([
    'variant' => 'primary', // primary | secondary | dark | danger | ghost
    'size' => 'md', // sm | md
    'as' => 'button', // button | a
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-brand-500 hover:bg-brand-600 text-white font-semibold',
        'secondary' => 'border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium',
        'dark' => 'bg-gray-900 hover:bg-gray-700 text-white font-semibold',
        'danger' => 'bg-red-500 hover:bg-red-600 text-white font-semibold',
        'ghost' => 'text-gray-500 hover:text-gray-800 hover:bg-gray-50 font-medium',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-6 py-2.5 text-sm',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed '
        . ($variants[$variant] ?? $variants['primary']) . ' '
        . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
