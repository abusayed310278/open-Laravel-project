@props([
    'label' => null,
    'name',
    'type' => 'text',
    'error' => null,
])

@php
    // Laravel's error bag always uses dot notation, even for a field
    // submitted with a bracketed HTML name like documents[nid].
    $error = $error ?? $errors->first(str_replace([']', '['], ['', '.'], $name));
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full border rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent '
                . ($error ? 'border-red-300' : 'border-gray-200'),
        ]) }}
    >

    @if ($error)
        <p class="text-xs text-red-500 mt-1.5">{{ $error }}</p>
    @endif
</div>
