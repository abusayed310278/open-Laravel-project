@props(['icon', 'label', 'value', 'change' => null, 'changeColor' => 'text-green-500'])

<div class="bg-white border border-gray-100 rounded-md p-5">
    <div class="flex items-center justify-between mb-3">
        <div class="w-9 h-9 bg-brand-50 rounded-md flex items-center justify-center text-brand-500">
            {!! $icon !!}
        </div>
        @if ($change)
            <span class="text-xs font-semibold {{ $changeColor }}">{{ $change }}</span>
        @endif
    </div>
    <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
    <p class="text-sm text-gray-400 mt-1">{{ $label }}</p>
</div>
