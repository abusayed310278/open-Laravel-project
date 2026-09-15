@props(['slug' => null])

@php
    $slug = strtolower($slug ?? '');

    if (str_contains($slug, 'phone')) {
        $path = 'M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z';
    } elseif (str_contains($slug, 'laptop')) {
        $path = 'M4 6h16v10H4V6zm-2 13h20l-1.5-3h-17L2 19z';
    } elseif (str_contains($slug, 'tablet') || str_contains($slug, 'ipad')) {
        $path = 'M7 4h10a1 1 0 011 1v14a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1zM11 18h2';
    } elseif (str_contains($slug, 'watch')) {
        $path = 'M9 7h6v10H9V7zM10 3h4v3h-4V3zm0 15h4v3h-4v-3z';
    } elseif (str_contains($slug, 'game') || str_contains($slug, 'gaming') || str_contains($slug, 'console')) {
        $path = 'M6 12h4m-2-2v4m7-1h.01M17 9h.01M3 8a2 2 0 012-2h14a2 2 0 012 2l-1 9a2 2 0 01-2 2h-1.5l-1-2h-9l-1 2H6a2 2 0 01-2-2L3 8z';
    } elseif (str_contains($slug, 'audio') || str_contains($slug, 'headphone') || str_contains($slug, 'earbud') || str_contains($slug, 'sound')) {
        $path = 'M3 18v-6a9 9 0 0118 0v6M3 18a2 2 0 002 2h1a1 1 0 001-1v-4a1 1 0 00-1-1H3m18 0h-3a1 1 0 00-1 1v4a1 1 0 001 1h1a2 2 0 002-2';
    } elseif (str_contains($slug, 'camera')) {
        $path = 'M4 8h3l1.5-2h7L17 8h3a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1zm8 3a3.5 3.5 0 100 7 3.5 3.5 0 000-7z';
    } elseif (str_contains($slug, 'access') || str_contains($slug, 'cable') || str_contains($slug, 'charger')) {
        $path = 'M13 10V3L4 14h7v7l9-11h-7z';
    } else {
        $path = 'M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z';
    }
@endphp

<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}" />
</svg>
