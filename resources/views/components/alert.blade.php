@props(['type' => 'info', 'dismissible' => false])

@php
    $styles = [
        'success' => 'bg-green-50 border-green-100 text-green-700',
        'error' => 'bg-red-50 border-red-100 text-red-700',
        'warning' => 'bg-brand-50 border-brand-100 text-brand-700',
        'info' => 'bg-blue-50 border-blue-100 text-blue-700',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start justify-between gap-3 border rounded-md px-4 py-3 text-sm ' . ($styles[$type] ?? $styles['info'])]) }}>
    <div>{{ $slot }}</div>

    @if ($dismissible)
        <button type="button" onclick="this.closest('[role=alert]')?.remove() ?? this.parentElement.remove()" class="text-current opacity-60 hover:opacity-100 leading-none">
            &times;
        </button>
    @endif
</div>
