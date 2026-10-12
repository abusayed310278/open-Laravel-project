@props(['slug' => null])

@php
    $slug = strtolower($slug ?? '');

    if (str_contains($slug, 'phone') || in_array($slug, ['vivo', 'samsung', 'oppo', 'infinix', 'apple-iphone', 'google-pixel', 'oneplus', 'xiaomi-poco'])) {
        $path = 'M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z';
    } elseif (str_contains($slug, 'keyboard')) {
        $path = 'M3 7h18a2 2 0 012 2v6a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2zm4 4h.01M10 11h.01M13 11h.01M16 11h.01M19 11h.01M5 11h.01M7 14h10';
    } elseif (str_contains($slug, 'ups') || str_contains($slug, 'battery') || str_contains($slug, 'power')) {
        $path = 'M13 10V3L4 14h7v7l9-11h-7z';
    } elseif (str_contains($slug, 'server') || str_contains($slug, 'storage')) {
        $path = 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01';
    } elseif (str_contains($slug, 'printer')) {
        $path = 'M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z';
    } elseif (str_contains($slug, 'whirlpool') || str_contains($slug, 'appliance')) {
        $path = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
    } elseif (str_contains($slug, 'usb') || str_contains($slug, 'converter')) {
        $path = 'M8 7h8m-8 3h8m-5 4v6m2-6v6M6 4h12a1 1 0 011 1v5a1 1 0 01-1 1H6a1 1 0 01-1-1V5a1 1 0 011-1z';
    } elseif (str_contains($slug, 'ram') || str_contains($slug, 'memory') || str_contains($slug, 'processor') || str_contains($slug, 'cpu')) {
        $path = 'M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m14-6h2m-2 6h2M7 5h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2zm2 4h6v6H9V9z';
    } elseif (str_contains($slug, 'monitor') || str_contains($slug, 'screen') || str_contains($slug, 'display')) {
        $path = 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z';
    } elseif (str_contains($slug, 'laptop') || str_contains($slug, 'macbook')) {
        $path = 'M4 6h16v10H4V6zm-2 13h20l-1.5-3h-17L2 19z';
    } elseif (str_contains($slug, 'tablet') || str_contains($slug, 'ipad')) {
        $path = 'M7 4h10a1 1 0 011 1v14a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1zM11 18h2';
    } elseif (str_contains($slug, 'watch')) {
        $path = 'M9 7h6v10H9V7zM10 3h4v3h-4V3zm0 15h4v3h-4v-3z';
    } elseif (str_contains($slug, 'game') || str_contains($slug, 'gaming') || str_contains($slug, 'console') || str_contains($slug, 'xbox') || str_contains($slug, 'playstation')) {
        $path = 'M6 12h4m-2-2v4m7-1h.01M17 9h.01M3 8a2 2 0 012-2h14a2 2 0 012 2l-1 9a2 2 0 01-2 2h-1.5l-1-2h-9l-1 2H6a2 2 0 01-2-2L3 8z';
    } elseif (str_contains($slug, 'audio') || str_contains($slug, 'headphone') || str_contains($slug, 'earbud') || str_contains($slug, 'sound')) {
        $path = 'M3 18v-6a9 9 0 0118 0v6M3 18a2 2 0 002 2h1a1 1 0 001-1v-4a1 1 0 00-1-1H3m18 0h-3a1 1 0 00-1 1v4a1 1 0 001 1h1a2 2 0 002-2';
    } elseif (str_contains($slug, 'camera')) {
        $path = 'M4 8h3l1.5-2h7L17 8h3a1 1 0 011 1v9a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1zm8 3a3.5 3.5 0 100 7 3.5 3.5 0 000-7z';
    } elseif (str_contains($slug, 'access') || str_contains($slug, 'cable') || str_contains($slug, 'charger')) {
        $path = 'M13 10V3L4 14h7v7l9-11h-7z';
    } elseif (str_contains($slug, 'bag')) {
        $path = 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z';
    } elseif (str_contains($slug, 'network') || str_contains($slug, 'wifi')) {
        $path = 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.393 9.393c5.857-5.857 15.355-5.857 21.213 0';
    } elseif (str_contains($slug, 'tv')) {
        $path = 'M7 4l5 5 5-5M4 9h16a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2v-9a2 2 0 012-2z';
    } else {
        $path = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
    }
@endphp

<svg {{ $attributes->merge(['class' => 'w-5 h-5']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}" />
</svg>
