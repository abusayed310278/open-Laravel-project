@props([
    'label' => null,
    'name',
    'rows' => 4,
    'error' => null,
    'value' => null,
])

@php
    $error = $error ?? $errors->first(str_replace([']', '['], ['', '.'], $name));
    $val = old($name, $value);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->except('value')->merge([
            'class' => 'w-full border rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent '
                . ($error ? 'border-red-300' : 'border-gray-200'),
        ]) }}
    >{{ $slot->isNotEmpty() ? $slot : $val }}</textarea>

    @if ($error)
        <p class="text-xs text-red-500 mt-1.5">{{ $error }}</p>
    @endif
</div>
