@props(['id', 'position' => 'center'])

@php
    $positionClasses = match($position) {
        'top-right' => 'fixed top-5 right-5 z-50 w-full max-w-sm px-4',
        'top-left' => 'fixed top-5 left-5 z-50 w-full max-w-sm px-4',
        'bottom-right' => 'fixed bottom-5 right-5 z-50 w-full max-w-sm px-4',
        default => 'fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-full max-w-sm px-4',
    };
@endphp

<div id="{{ $id }}" data-top-card class="hidden {{ $positionClasses }}">
    {{ $slot }}
</div>
