@props(['grade' => 'ungraded'])

@php
    $grade = strtoupper($grade);
    $styles = [
        'A' => 'bg-green-50 text-green-600',
        'B' => 'bg-blue-50 text-blue-600',
        'C' => 'bg-brand-50 text-brand-600',
    ];
    $labels = [
        'A' => 'Grade A · Like New',
        'B' => 'Grade B · Good',
        'C' => 'Grade C · Fair',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded ' . ($styles[$grade] ?? 'bg-gray-100 text-gray-500')]) }}>
    {{ $labels[$grade] ?? 'Ungraded' }}
</span>
