@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => null,
    'selected' => null,
    'error' => null,
])

@php
    $error = $error ?? $errors->first(str_replace([']', '['], ['', '.'], $name));
    $selected = old($name, $selected);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full border rounded-md px-4 py-2.5 text-sm text-gray-800 bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent '
                . ($error ? 'border-red-300' : 'border-gray-200'),
        ]) }}
    >
        @if ($placeholder)
            <option value="" @selected(is_null($selected))>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $label_)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $label_ }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if ($error)
        <p class="text-xs text-red-500 mt-1.5">{{ $error }}</p>
    @endif
</div>
