@php
    $brandFont = setting('brand_font');
    $brandBase = setting('brand_color_base') ?: setting('brand_color_primary', '#f59e0b');
    $brandColor = setting('brand_color_primary') ?: '#f59e0b';
    $brandHoverColor = setting('brand_color_hover') ?: '#fff200';
    $brandHoverTextColor = setting('brand_color_hover_text') ?: '#000000';
    $scale = \App\Support\ColorScale::fromHex($brandBase);
    $contrastText = \App\Support\ColorScale::contrastText($brandColor);
    $siteTextColor = setting('site_text_color', '#1f2937');
    $siteHeadingColor = setting('site_heading_color', '#0f172a');
    $siteMutedColor = setting('site_muted_color', '#64748b');
    $siteTextWeight = setting('site_text_weight', '400');
    $siteHeadingWeight = setting('site_heading_weight', '700');
    $siteMutedWeight = setting('site_muted_weight', '400');
    $hoverTextWeight = setting('brand_color_hover_text_weight', '700');
@endphp

@if ($brandFont && !in_array($brandFont, ['Inter', 'Montserrat'], true))
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $brandFont) }}:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
@endif

<style id="site-branding-css">
    :root {
        @if ($brandFont)
            --font-sans: '{{ $brandFont }}', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
        @endif
        --color-brand-primary: {{ $brandColor }};
        --color-brand-contrast: {{ $contrastText }};
        --color-brand-hover: {{ $brandHoverColor }};
        --color-brand-hover-text: {{ $brandHoverTextColor }};
        @foreach ($scale as $step => $hex)
            --color-brand-{{ $step }}: {{ $hex }};
        @endforeach

        /* Global Website Text & Typography Colors & Weights */
        --color-site-text: {{ $siteTextColor }};
        --color-site-heading: {{ $siteHeadingColor }};
        --color-site-muted: {{ $siteMutedColor }};
        --font-weight-site-text: {{ $siteTextWeight }};
        --font-weight-site-heading: {{ $siteHeadingWeight }};
        --font-weight-site-muted: {{ $siteMutedWeight }};
        --font-weight-hover-text: {{ $hoverTextWeight }};

        /* Product Card Typography & Colors */
        --pc-cat-color: {{ setting('product_card_category_color', '#ea580c') }};
        --pc-cat-size: {{ setting('product_card_category_size', '11') }}px;

        --pc-title-color: {{ setting('product_card_title_color', '#090d16') }};
        --pc-title-hover: {{ setting('product_card_title_hover_color', '#ea580c') }};
        --pc-title-size: {{ setting('product_card_title_size', '14') }}px;

        --pc-price-color: {{ setting('product_card_price_color', '#090d16') }};
        --pc-price-size: {{ setting('product_card_price_size', '16') }}px;

        --pc-old-price-color: {{ setting('product_card_old_price_color', '#94a3b8') }};
        --pc-old-price-size: {{ setting('product_card_old_price_size', '12') }}px;

        --pc-star-color: {{ setting('product_card_star_color', '#fbbf24') }};
        --pc-rating-color: {{ setting('product_card_rating_color', '#1e293b') }};
        --pc-rating-count-color: {{ setting('product_card_rating_count_color', '#94a3b8') }};
        --pc-rating-size: {{ setting('product_card_rating_size', '12') }}px;

        --pc-vendor-color: {{ setting('product_card_vendor_color', '#64748b') }};
        --pc-vendor-size: {{ setting('product_card_vendor_size', '12') }}px;

        --pc-btn-bg: {{ setting('product_card_btn_bg') ?: 'var(--color-brand-primary, #656c19)' }};
        --pc-btn-text: {{ setting('product_card_btn_text') ?: 'var(--color-brand-contrast, #ffffff)' }};
        --pc-btn-size: {{ setting('product_card_btn_size', '12') }}px;
        --pc-btn-hover-bg: {{ setting('product_card_btn_hover_bg') ?: 'var(--color-brand-hover, #fff200)' }};
        --pc-btn-hover-text: {{ setting('product_card_btn_hover_text') ?: 'var(--color-brand-hover-text, #000000)' }};
    }

    /* 1. Start Selling CTA Button (Screenshot 1 & 4) */
    .brand-btn-primary,
    .brand-cta-button,
    .js-start-selling-cta {
        background-color: var(--color-brand-primary) !important;
        color: var(--color-brand-contrast) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .brand-btn-primary:hover,
    .brand-cta-button:hover,
    .js-start-selling-cta:hover {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
        font-weight: var(--font-weight-hover-text) !important;
    }
    .brand-btn-primary svg,
    .brand-cta-button svg,
    .js-start-selling-cta svg {
        color: inherit !important;
        stroke: currentColor !important;
    }

    /* 2. Promo Cards Action Buttons (Screenshot 2) */
    .promo-card-btn {
        background-color: var(--color-brand-primary) !important;
        color: var(--color-brand-contrast) !important;
        transition: background-color 0.15s ease, color 0.15s ease;
    }
    .group:hover .promo-card-btn,
    .promo-card-btn:hover {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
        font-weight: var(--font-weight-hover-text) !important;
        opacity: 1 !important;
    }
    .promo-card-btn span {
        color: inherit !important;
    }

    /* 3. Header Start Selling & Add to Cart Buttons (Screenshot 1 & 3) */
    .header-start-selling-btn,
    #btn-add-to-cart,
    .btn-add-to-cart,
    .add-to-cart-btn,
    .js-add-to-cart {
        background-color: var(--color-brand-primary) !important;
        color: var(--color-brand-contrast) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .header-start-selling-btn:hover,
    #btn-add-to-cart:hover,
    .btn-add-to-cart:hover,
    .add-to-cart-btn:hover,
    .js-add-to-cart:hover:not(:disabled) {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
        font-weight: var(--font-weight-hover-text) !important;
    }
    .header-start-selling-btn svg,
    .js-add-to-cart svg {
        color: inherit !important;
        stroke: currentColor !important;
    }
    .js-add-to-cart:hover:not(:disabled) svg {
        color: var(--color-brand-hover-text) !important;
    }

    /* 4. Search Bar Action Buttons (Search & Advanced Sliders) */
    .search-submit-btn,
    .advanced-search-btn {
        background-color: var(--color-brand-primary) !important;
        color: var(--color-brand-contrast) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .search-submit-btn:hover,
    .advanced-search-btn:hover {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
    }
    .search-submit-btn svg,
    .advanced-search-btn svg,
    .search-submit-btn .submit-button-text {
        color: var(--color-brand-contrast) !important;
    }
    .search-submit-btn:hover svg,
    .advanced-search-btn:hover svg,
    .search-submit-btn:hover .submit-button-text {
        color: var(--color-brand-hover-text) !important;
        font-weight: var(--font-weight-hover-text) !important;
    }

    /* 5. Newsletter Stay Updated Banner (Screenshot 5) */
    .newsletter-brand-banner {
        background: linear-gradient(to right, var(--color-brand-primary), var(--color-brand-400)) !important;
    }
    .newsletter-brand-banner .newsletter-heading,
    .newsletter-brand-banner h2 {
        color: var(--color-site-heading) !important;
    }
    .newsletter-brand-banner .newsletter-icon,
    .newsletter-brand-banner svg {
        color: var(--color-site-heading) !important;
    }
    .newsletter-brand-banner .newsletter-desc,
    .newsletter-brand-banner p {
        color: var(--color-site-text) !important;
    }

    /* Brand Utility Overrides */
    .bg-brand-500 { background-color: var(--color-brand-primary) !important; }
    .bg-brand-400 { background-color: var(--color-brand-400) !important; }
    .bg-brand-300 { background-color: var(--color-brand-300) !important; }
    .bg-brand-200 { background-color: var(--color-brand-200) !important; }
    .bg-brand-100 { background-color: var(--color-brand-100) !important; }
    .bg-brand-50 { background-color: var(--color-brand-50) !important; }
    .bg-brand-600 { background-color: var(--color-brand-600) !important; }
    .bg-brand-700 { background-color: var(--color-brand-700) !important; }

    .hover\:bg-brand-500:hover {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
    }
    .hover\:bg-brand-600:hover { background-color: var(--color-brand-600) !important; }
    .hover\:bg-brand-700:hover { background-color: var(--color-brand-700) !important; }

    .group:hover .group-hover\:bg-brand-500 {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
    }
    .group:hover .group-hover\:bg-brand-600 { background-color: var(--color-brand-600) !important; }

    .text-brand-500 { color: var(--color-brand-primary) !important; }
    .text-brand-600 { color: var(--color-brand-600) !important; }

    .js-add-to-cart:hover:not(:disabled) {
        background-color: var(--color-brand-hover) !important;
        color: var(--color-brand-hover-text) !important;
    }

    .border-brand-500 { border-color: var(--color-brand-primary) !important; }
    .border-brand-400 { border-color: var(--color-brand-400) !important; }

    .ring-brand-500 { --tw-ring-color: var(--color-brand-primary) !important; }
    .focus\:ring-brand-400:focus { --tw-ring-color: var(--color-brand-primary) !important; }

    /* Product Card Custom Elements */
    .pc-category {
        color: var(--pc-cat-color) !important;
        font-size: var(--pc-cat-size) !important;
    }
    .pc-title {
        color: var(--pc-title-color) !important;
        font-size: var(--pc-title-size) !important;
    }
    .pc-title:hover {
        color: var(--pc-title-hover) !important;
    }
    .pc-price {
        color: var(--pc-price-color) !important;
        font-size: var(--pc-price-size) !important;
    }
    .pc-old-price {
        color: var(--pc-old-price-color) !important;
        font-size: var(--pc-old-price-size) !important;
    }
    .pc-star {
        color: var(--pc-star-color) !important;
        width: calc(var(--pc-rating-size) * 1.15) !important;
        height: calc(var(--pc-rating-size) * 1.15) !important;
    }
    .pc-rating {
        color: var(--pc-rating-color) !important;
        font-size: var(--pc-rating-size) !important;
    }
    .pc-rating-count {
        color: var(--pc-rating-count-color) !important;
        font-size: var(--pc-rating-size) !important;
    }
    .pc-vendor {
        color: var(--pc-vendor-color) !important;
        font-size: var(--pc-vendor-size) !important;
    }
    .pc-add-btn,
    .pc-add-btn.js-add-to-cart {
        background-color: var(--pc-btn-bg) !important;
        color: var(--pc-btn-text) !important;
        font-size: var(--pc-btn-size) !important;
        border-color: transparent !important;
    }
    .pc-add-btn:hover,
    .pc-add-btn.js-add-to-cart:hover:not(:disabled) {
        background-color: var(--pc-btn-hover-bg) !important;
        color: var(--pc-btn-hover-text) !important;
    }
    .pc-add-btn svg,
    .pc-add-btn.js-add-to-cart svg {
        color: inherit !important;
        stroke: currentColor !important;
        width: calc(var(--pc-btn-size) * 1.15) !important;
        height: calc(var(--pc-btn-size) * 1.15) !important;
    }
    .pc-add-btn:hover svg,
    .pc-add-btn.js-add-to-cart:hover svg {
        color: var(--pc-btn-hover-text) !important;
    }

    @if (!request()->is('admin/*'))
        /* Global Website Typography Overrides (Customer / Public Pages) */
        body {
            color: var(--color-site-text) !important;
            font-weight: var(--font-weight-site-text) !important;
        }

        /* 1. All Headings & Section Titles */
        h1:not(.site-public-footer *):not(.pc-title),
        h2:not(.site-public-footer *):not(.pc-title),
        h3:not(.site-public-footer *):not(.pc-title),
        h4:not(.site-public-footer *):not(.pc-title),
        h5:not(.site-public-footer *):not(.pc-title),
        h6:not(.site-public-footer *):not(.pc-title),
        .promo-card-title,
        .promo-card-title * {
            color: var(--color-site-heading) !important;
            font-weight: var(--font-weight-site-heading) !important;
        }

        /* 2. Deep & Primary Text (includes header category bar, black, and dark grays) */
        .text-gray-950:not(.site-public-footer *):not(.pc-title):not(.pc-price),
        .text-gray-900:not(.site-public-footer *):not(.pc-title):not(.pc-price),
        .text-black:not(.site-public-footer *):not(.pc-title):not(.pc-price),
        [class*="text-gray-950"]:not(.site-public-footer *):not(.pc-title):not(.pc-price),
        [class*="text-gray-900"]:not(.site-public-footer *):not(.pc-title):not(.pc-price),
        #header-nav-items a:not([class*="text-red"]),
        #header-nav-items a:not([class*="text-red"]) span {
            color: var(--color-site-heading) !important;
        }

        /* 3. Announcement Top Bar (Contact & Quick Links) */
        #site-announcement-bar,
        #site-announcement-bar a,
        #site-announcement-bar span,
        #site-announcement-bar svg {
            color: var(--color-site-text) !important;
        }

        /* 4. Body Copy & Mid Grays */
        .text-gray-800:not(.site-public-footer *),
        .text-gray-700:not(.site-public-footer *),
        [class*="text-gray-800"]:not(.site-public-footer *),
        [class*="text-gray-700"]:not(.site-public-footer *) {
            color: var(--color-site-text) !important;
            font-weight: var(--font-weight-site-text) !important;
        }

        /* 5. Muted Text, Captions & Input Placeholders */
        .text-gray-600:not(.site-public-footer *):not(.pc-vendor),
        .text-gray-500:not(.site-public-footer *):not(.pc-vendor):not(.pc-old-price),
        .text-gray-400:not(.site-public-footer *):not(.pc-vendor):not(.pc-old-price),
        [class*="text-gray-600"]:not(.site-public-footer *):not(.pc-vendor),
        [class*="text-gray-500"]:not(.site-public-footer *):not(.pc-vendor):not(.pc-old-price),
        [class*="text-gray-400"]:not(.site-public-footer *):not(.pc-vendor):not(.pc-old-price),
        input::placeholder,
        textarea::placeholder {
            color: var(--color-site-muted) !important;
            font-weight: var(--font-weight-site-muted) !important;
        }

        /* 6. Newsletter Banner Texts & Subscribe Button */
        .newsletter-brand-banner h2,
        .newsletter-brand-banner .newsletter-heading,
        .newsletter-brand-banner svg {
            color: var(--color-site-heading) !important;
        }
        .newsletter-brand-banner p,
        .newsletter-brand-banner .newsletter-desc {
            color: var(--color-site-text) !important;
            opacity: 1 !important;
        }
        .newsletter-brand-banner input::placeholder {
            color: var(--color-site-muted) !important;
            opacity: 0.8 !important;
        }
    @endif

    @php
        $nlBtnTextColor = setting('home_newsletter_button_text_color');
        $nlBtnTextColor = filled($nlBtnTextColor) ? '#'.ltrim($nlBtnTextColor, '#') : '#ffffff';
        $nlBtnBgColor = setting('home_newsletter_button_bg_color');
        $nlBtnBgColor = filled($nlBtnBgColor) ? '#'.ltrim($nlBtnBgColor, '#') : '#090d16';
    @endphp
    .newsletter-brand-banner button[type="submit"] {
        color: {{ $nlBtnTextColor }} !important;
        background-color: {{ $nlBtnBgColor }} !important;
    }
</style>
