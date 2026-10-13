@php
    $brandFont = setting('brand_font');
    $brandColor = setting('brand_color_primary') ?: '#f59e0b';
    $scale = \App\Support\ColorScale::fromHex($brandColor);
    $contrastText = \App\Support\ColorScale::contrastText($brandColor);
@endphp

@if ($brandFont && $brandFont !== 'Montserrat')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $brandFont) }}:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endif

<style id="site-branding-css">
    :root {
        @if ($brandFont)
            --font-sans: '{{ $brandFont }}', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
        @endif
        --color-brand-primary: {{ $brandColor }};
        --color-brand-contrast: {{ $contrastText }};
        @foreach ($scale as $step => $hex)
            --color-brand-{{ $step }}: {{ $hex }};
        @endforeach
    }

    /* 1. Start Selling CTA Button (Screenshot 1) */
    .brand-btn-primary,
    .brand-cta-button,
    .js-start-selling-cta {
        background-color: var(--color-brand-500) !important;
        color: var(--color-brand-contrast) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .brand-btn-primary:hover,
    .brand-cta-button:hover,
    .js-start-selling-cta:hover {
        background-color: var(--color-brand-600) !important;
        color: var(--color-brand-contrast) !important;
    }

    /* 2. Promo Cards Action Buttons (Screenshot 2) */
    .promo-card-btn {
        background-color: var(--color-brand-400) !important;
        color: var(--color-brand-contrast) !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .group:hover .promo-card-btn,
    .promo-card-btn:hover {
        background-color: var(--color-brand-500) !important;
        color: var(--color-brand-contrast) !important;
    }

    /* 3. Header Start Selling Button (Screenshot 3) */
    .header-start-selling-btn {
        background-color: var(--color-brand-500) !important;
        color: var(--color-brand-contrast) !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .header-start-selling-btn:hover {
        background-color: var(--color-brand-600) !important;
        color: var(--color-brand-contrast) !important;
    }

    /* 4. Search Bar Action Buttons (Search & Advanced Sliders) */
    .search-submit-btn,
    .advanced-search-btn {
        background-color: var(--color-brand-500) !important;
        color: var(--color-brand-contrast) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .search-submit-btn:hover,
    .advanced-search-btn:hover {
        background-color: var(--color-brand-600) !important;
        color: var(--color-brand-contrast) !important;
    }
    .search-submit-btn svg,
    .advanced-search-btn svg,
    .search-submit-btn .submit-button-text {
        color: var(--color-brand-contrast) !important;
    }

    /* 4. Newsletter Stay Updated Banner (Screenshot 4) */
    .newsletter-brand-banner {
        background: linear-gradient(to right, var(--color-brand-300), var(--color-brand-400)) !important;
    }
    .newsletter-brand-banner .newsletter-heading,
    .newsletter-brand-banner h2 {
        color: var(--color-brand-contrast) !important;
    }
    .newsletter-brand-banner .newsletter-icon,
    .newsletter-brand-banner svg {
        color: var(--color-brand-contrast) !important;
    }
    .newsletter-brand-banner .newsletter-desc,
    .newsletter-brand-banner p {
        color: var(--color-brand-contrast) !important;
        opacity: 0.85 !important;
    }

    /* Brand Utility Overrides */
    .bg-brand-500 { background-color: var(--color-brand-500) !important; }
    .bg-brand-400 { background-color: var(--color-brand-400) !important; }
    .bg-brand-300 { background-color: var(--color-brand-300) !important; }
    .bg-brand-200 { background-color: var(--color-brand-200) !important; }
    .bg-brand-100 { background-color: var(--color-brand-100) !important; }
    .bg-brand-50 { background-color: var(--color-brand-50) !important; }
    .bg-brand-600 { background-color: var(--color-brand-600) !important; }
    .bg-brand-700 { background-color: var(--color-brand-700) !important; }

    .hover\:bg-brand-500:hover { background-color: var(--color-brand-500) !important; }
    .hover\:bg-brand-600:hover { background-color: var(--color-brand-600) !important; }
    .hover\:bg-brand-700:hover { background-color: var(--color-brand-700) !important; }

    .group:hover .group-hover\:bg-brand-500 { background-color: var(--color-brand-500) !important; }
    .group:hover .group-hover\:bg-brand-600 { background-color: var(--color-brand-600) !important; }

    .text-brand-500 { color: var(--color-brand-500) !important; }
    .text-brand-600 { color: var(--color-brand-600) !important; }

    .border-brand-500 { border-color: var(--color-brand-500) !important; }
    .border-brand-400 { border-color: var(--color-brand-400) !important; }

    .ring-brand-500 { --tw-ring-color: var(--color-brand-500) !important; }
    .focus\:ring-brand-400:focus { --tw-ring-color: var(--color-brand-400) !important; }
</style>
