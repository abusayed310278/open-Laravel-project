@props([
    'name',
    'checked' => false,
])

<label {{ $attributes->only('class')->merge(['class' => 'flex items-center justify-between py-3 border-b border-gray-50 cursor-pointer']) }}>
    <span class="text-sm font-medium text-gray-700">{{ $slot }}</span>
    <input
        type="checkbox"
        name="{{ $name }}"
        id="{{ $name }}"
        @checked(old($name, $checked))
        {{ $attributes->except('class')->merge(['class' => 'w-4 h-4 rounded accent-brand-500']) }}
    >
</label>
