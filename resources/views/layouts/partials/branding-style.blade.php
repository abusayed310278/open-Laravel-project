@php
    $brandFont = setting('brand_font');
    $brandColor = setting('brand_color_primary');
@endphp

@if ($brandFont && $brandFont !== 'Montserrat')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $brandFont) }}:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endif

@if ($brandFont || $brandColor)
    <style>
        :root {
            @if ($brandFont)
                --font-sans: '{{ $brandFont }}', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            @endif
            @if ($brandColor)
                @foreach (\App\Support\ColorScale::fromHex($brandColor) as $step => $hex)
                    --color-brand-{{ $step }}: {{ $hex }};
                @endforeach
            @endif
        }
    </style>
@endif
