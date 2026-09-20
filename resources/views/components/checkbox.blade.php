@props([
    'name',
    'checked' => false,
])

<label {{ $attributes->only('class')->merge(['class' => 'inline-flex items-center gap-2.5 py-2 cursor-pointer select-none']) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        @checked(old($name, $checked))
        {{ $attributes->except('class')->merge(['class' => 'w-4 h-4 rounded border-gray-300 text-brand-500 focus:ring-brand-400 accent-brand-500 cursor-pointer']) }}
    >
    <span class="text-sm font-medium text-gray-700">{{ $slot }}</span>
</label>
