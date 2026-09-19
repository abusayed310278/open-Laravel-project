@props(['icon', 'label', 'value', 'change' => null, 'changeColor' => null, 'hint' => null])

@php
    $resolvedChangeColor = $changeColor ?? (str_starts_with((string) $change, '-') ? 'text-red-500' : 'text-emerald-500');
@endphp

<div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-2xs hover:shadow-sm transition-shadow">
    <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600">
            {!! $icon !!}
        </div>
        @if ($change)
            <span class="text-xs font-semibold {{ $resolvedChangeColor }}">{{ $change }}</span>
        @endif
    </div>
    <p class="text-2xl font-black text-gray-950 tracking-tight">{{ $value }}</p>
    <p class="text-sm text-gray-500 mt-1">{{ $label }}</p>
    @if ($hint)
        <p class="text-[11px] text-gray-400 mt-1.5 pt-1.5 border-t border-gray-50">{{ $hint }}</p>
    @endif
</div>
