@props([
    'name',
    'label' => null,
    'accept' => 'image/*',
    'multiple' => false,
    'hint' => 'PNG, JPG up to 5MB',
])

<div>
    @if ($label)
        <label class="block text-sm font-medium text-gray-700 mb-1.5">{{ $label }}</label>
    @endif

    <label
        for="{{ $name }}"
        class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-gray-200 rounded-md p-8 text-center cursor-pointer hover:border-brand-300 transition-colors"
    >
        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
        </svg>
        <span class="text-sm text-gray-500">
            <span class="text-brand-600 font-semibold">Click to upload</span> or drag and drop
        </span>
        <span class="text-xs text-gray-400">{{ $hint }}</span>

        <input
            type="file"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            id="{{ $name }}"
            accept="{{ $accept }}"
            @if ($multiple) multiple @endif
            {{ $attributes->merge(['class' => 'hidden']) }}
        >
    </label>

    @error(str_replace([']', '['], ['', '.'], $name))
        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>
