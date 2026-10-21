<?php

namespace App\Support;

/**
 * Admin-editable homepage copy (Settings → Why Buy / Promo Cards / Newsletter),
 * stored in the settings table with these defaults as fallbacks.
 */
final class HomeContent
{
    public const WHY_BUY_ICONS = [
        'shield' => [
            'label' => 'Shield / Verified Quality',
            'd' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        ],
        'truck' => [
            'label' => 'Delivery Truck / Fast Shipping',
            'd' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
        ],
        'leaf' => [
            'label' => 'Eco Leaf / Sustainable Choice',
            'd' => 'M5 19c0-9 5-14 15-14 0 10-5 15-14 15m-1 1c2-5 5-8 9-10',
        ],
        'headset' => [
            'label' => 'Headset / Dedicated Support',
            'd' => 'M4 14v-2a8 8 0 0116 0v2M4 14a2 2 0 012-2h1v6H6a2 2 0 01-2-2v-2zm16 0a2 2 0 00-2-2h-1v6h1a2 2 0 002-2v-2zm-3 6c0 1-2 2-5 2',
        ],
        'badge-check' => [
            'label' => 'Certified Check Badge',
            'd' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
        ],
        'refresh' => [
            'label' => 'Return / Refund & Exchange',
            'd' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
        ],
        'lock' => [
            'label' => 'Lock / Secure Escrow Payment',
            'd' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        ],
        'star' => [
            'label' => 'Star / Top Rated Sellers',
            'd' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        ],
        'heart' => [
            'label' => 'Heart / Customer Love',
            'd' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
        ],
        'sparkles' => [
            'label' => 'Sparkles / Premium Refurbished',
            'd' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        ],
        'award' => [
            'label' => 'Award / 1-Year Guarantee',
            'd' => 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
        ],
        'wallet' => [
            'label' => 'Wallet / Unbeatable Value',
            'd' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        ],
        'zap' => [
            'label' => 'Lightning / Instant Response',
            'd' => 'M13 10V3L4 14h7v7l9-11h-7z',
        ],
        'box' => [
            'label' => 'Package / Unboxed & Inspected',
            'd' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        ],
        'clock' => [
            'label' => 'Clock / 24/7 Availability',
            'd' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    ];

    public const WHY_BUY_DEFAULTS = [
        [
            'title' => 'Verified Quality',
            'text' => 'All products tested and verified',
            'icon' => 'shield',
            'icon_bg' => '#fef3c7',
            'icon_color' => '#1f2937',
            'icon_size' => 24,
            'title_size' => 14,
            'title_weight' => '700',
            'title_color' => '#0f172a',
            'text_size' => 12,
            'text_weight' => '400',
            'text_color' => '#64748b',
            'card_bg' => '#ffffff',
            'custom_icon' => null,
        ],
        [
            'title' => 'Fast & Reliable Delivery',
            'text' => 'Across Abu Dhabi & UAE',
            'icon' => 'truck',
            'icon_bg' => '#ffedd5',
            'icon_color' => '#1f2937',
            'icon_size' => 24,
            'title_size' => 14,
            'title_weight' => '700',
            'title_color' => '#0f172a',
            'text_size' => 12,
            'text_weight' => '400',
            'text_color' => '#64748b',
            'card_bg' => '#ffffff',
            'custom_icon' => null,
        ],
        [
            'title' => 'A More Sustainable Choice',
            'text' => 'Give tech a second life',
            'icon' => 'leaf',
            'icon_bg' => '#dcfce7',
            'icon_color' => '#16a34a',
            'icon_size' => 24,
            'title_size' => 14,
            'title_weight' => '700',
            'title_color' => '#0f172a',
            'text_size' => 12,
            'text_weight' => '400',
            'text_color' => '#64748b',
            'card_bg' => '#ffffff',
            'custom_icon' => null,
        ],
        [
            'title' => 'Dedicated Support',
            'text' => "We're here to help",
            'icon' => 'headset',
            'icon_bg' => '#ffedd5',
            'icon_color' => '#1f2937',
            'icon_size' => 24,
            'title_size' => 14,
            'title_weight' => '700',
            'title_color' => '#0f172a',
            'text_size' => 12,
            'text_weight' => '400',
            'text_color' => '#64748b',
            'card_bg' => '#ffffff',
            'custom_icon' => null,
        ],
    ];

    public static function getWhyBuyIconSvg(string $icon): string
    {
        return self::WHY_BUY_ICONS[$icon]['d'] ?? self::WHY_BUY_ICONS['shield']['d'];
    }

    public const PROMO_DEFAULTS = [
        1 => [
            'title' => "Laptops\nfor Work & Study",
            'text' => "Premium performance\nat lower prices.",
            'button' => 'Shop Laptops',
            'bg_color_start' => '#ad7f67',
            'bg_color_end' => '#e8dedc',
            'title_color' => '#030712',
            'text_color' => '#1f2937',
            'button_bg_color' => '#fef08a',
            'button_text_color' => '#000000',
        ],
        2 => [
            'title' => 'Smartphones',
            'text' => "Stay Connected\nfor Less.",
            'button' => 'Shop Phones',
            'bg_color_start' => '#ebeef3',
            'bg_color_end' => '#ccd6e2',
            'title_color' => '#030712',
            'text_color' => '#1f2937',
            'button_bg_color' => '#fef08a',
            'button_text_color' => '#000000',
        ],
        3 => [
            'title' => 'Sell Your Device',
            'text' => "Turn your unused electronics\ninto cash.",
            'button' => 'Start Selling',
            'bg_color_start' => '#6283c4',
            'bg_color_end' => '#c1d3f7',
            'title_color' => '#030712',
            'text_color' => '#1f2937',
            'button_bg_color' => '#fef08a',
            'button_text_color' => '#000000',
        ],
    ];

    public const NEWSLETTER_DEFAULTS = [
        'title' => 'Stay Updated',
        'text' => 'Get the latest deals and new arrivals.',
        'placeholder' => 'Your email address',
        'button' => 'Subscribe',
        'button_text_color' => '#ffffff',
        'button_bg_color' => '#090d16',
    ];

    public const START_SELLING_DEFAULTS = [
        'title' => 'Start selling your electronics',
        'text' => 'Turn your unused gadgets into instant cash. List in minutes as a seller or open a verified store with warehouse fulfillment.',
        'primary_label' => 'Start Selling Now',
        'secondary_label' => 'Explore Verified Deals →',
        'app_heading' => 'Shop and sell on the go',
        'app_text' => 'Get the app for a faster marketplace experience.',
        'qr_caption' => 'Scan to download',
        'ios_subtitle' => 'Download on the',
        'ios_title' => 'App Store',
        'android_subtitle' => 'Get it on',
        'android_title' => 'Google Play',
    ];

    /**
     * Start Selling banner copy, links and app-download media (Settings → Start Selling).
     *
     * @return array<string, ?string>
     */
    public static function startSelling(): array
    {
        $content = [];
        foreach (self::START_SELLING_DEFAULTS as $key => $default) {
            $content[$key] = setting("home_sell_{$key}") ?: $default;
        }

        foreach (['primary_url', 'secondary_url', 'ios_url', 'android_url', 'qr_url'] as $key) {
            $content[$key] = setting("home_sell_{$key}") ?: null;
        }

        foreach (['title', 'text'] as $field) {
            $size = (int) setting("home_sell_{$field}_size");
            $content["{$field}_size"] = $size > 0 ? $size : null;
        }

        foreach (['phone_image', 'qr_image'] as $key) {
            $content[$key] = MediaUrl::resolve(setting("home_sell_{$key}"));
        }

        return $content;
    }

    /**
     * @return array{heading: string, heading_size: int, heading_weight: string, heading_color: string, items: array<int, array>}
     */
    public static function whyBuy(): array
    {
        $items = [];
        foreach (self::WHY_BUY_DEFAULTS as $index => $default) {
            $number = $index + 1;
            $customIconPath = setting("home_whybuy_{$number}_custom_icon");
            $iconKey = setting("home_whybuy_{$number}_icon") ?: $default['icon'];

            $iconBg = setting("home_whybuy_{$number}_icon_bg") ?: $default['icon_bg'];
            if (filled($iconBg) && ! str_starts_with($iconBg, '#') && strlen($iconBg) === 6) {
                $iconBg = '#' . $iconBg;
            }

            $iconColor = setting("home_whybuy_{$number}_icon_color") ?: $default['icon_color'];
            if (filled($iconColor) && ! str_starts_with($iconColor, '#') && strlen($iconColor) === 6) {
                $iconColor = '#' . $iconColor;
            }

            $titleColor = setting("home_whybuy_{$number}_title_color") ?: $default['title_color'];
            if (filled($titleColor) && ! str_starts_with($titleColor, '#') && strlen($titleColor) === 6) {
                $titleColor = '#' . $titleColor;
            }

            $textColor = setting("home_whybuy_{$number}_text_color") ?: $default['text_color'];
            if (filled($textColor) && ! str_starts_with($textColor, '#') && strlen($textColor) === 6) {
                $textColor = '#' . $textColor;
            }

            $cardBg = setting("home_whybuy_{$number}_card_bg") ?: $default['card_bg'];
            if (filled($cardBg) && ! str_starts_with($cardBg, '#') && strlen($cardBg) === 6) {
                $cardBg = '#' . $cardBg;
            }

            $items[] = [
                'title' => setting("home_whybuy_{$number}_title") ?: $default['title'],
                'text' => setting("home_whybuy_{$number}_text") ?: $default['text'],
                'icon' => $iconKey,
                'icon_svg_d' => self::getWhyBuyIconSvg($iconKey),
                'custom_icon' => filled($customIconPath) ? MediaUrl::resolve($customIconPath) : null,
                'custom_icon_path' => $customIconPath ?: null,
                'icon_bg' => $iconBg,
                'icon_color' => $iconColor,
                'icon_size' => (int) (setting("home_whybuy_{$number}_icon_size") ?: $default['icon_size']),
                'title_size' => (int) (setting("home_whybuy_{$number}_title_size") ?: $default['title_size']),
                'title_weight' => (string) (setting("home_whybuy_{$number}_title_weight") ?: $default['title_weight']),
                'title_color' => $titleColor,
                'text_size' => (int) (setting("home_whybuy_{$number}_text_size") ?: $default['text_size']),
                'text_weight' => (string) (setting("home_whybuy_{$number}_text_weight") ?: $default['text_weight']),
                'text_color' => $textColor,
                'card_bg' => $cardBg,
            ];
        }

        $headingColor = setting('home_whybuy_heading_color') ?: '#030712';
        if (filled($headingColor) && ! str_starts_with($headingColor, '#') && strlen($headingColor) === 6) {
            $headingColor = '#' . $headingColor;
        }

        return [
            'heading' => setting('home_whybuy_heading') ?: 'Why Buy from OpenBox?',
            'heading_size' => (int) (setting('home_whybuy_heading_size') ?: 18),
            'heading_weight' => (string) (setting('home_whybuy_heading_weight') ?: '700'),
            'heading_color' => $headingColor,
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array{title: string, text: string, button: string, product_id: ?int, bg_color_start: string, bg_color_end: string, title_color: string, text_color: string, button_bg_color: string, button_text_color: string}>
     */
    public static function promoCards(): array
    {
        $cards = [];
        foreach (self::PROMO_DEFAULTS as $number => $default) {
            $productId = setting("home_promo_{$number}_product");

            $bgStart = setting("home_promo_{$number}_bg_color_start") ?: $default['bg_color_start'];
            if (filled($bgStart) && ! str_starts_with($bgStart, '#') && (strlen($bgStart) === 6 || strlen($bgStart) === 3)) {
                $bgStart = '#' . $bgStart;
            }

            $bgEnd = setting("home_promo_{$number}_bg_color_end") ?: $default['bg_color_end'];
            if (filled($bgEnd) && ! str_starts_with($bgEnd, '#') && (strlen($bgEnd) === 6 || strlen($bgEnd) === 3)) {
                $bgEnd = '#' . $bgEnd;
            }

            $titleColor = setting("home_promo_{$number}_title_color") ?: $default['title_color'];
            if (filled($titleColor) && ! str_starts_with($titleColor, '#') && (strlen($titleColor) === 6 || strlen($titleColor) === 3)) {
                $titleColor = '#' . $titleColor;
            }

            $textColor = setting("home_promo_{$number}_text_color") ?: $default['text_color'];
            if (filled($textColor) && ! str_starts_with($textColor, '#') && (strlen($textColor) === 6 || strlen($textColor) === 3)) {
                $textColor = '#' . $textColor;
            }

            $btnBg = setting("home_promo_{$number}_button_bg_color") ?: $default['button_bg_color'];
            if (filled($btnBg) && ! str_starts_with($btnBg, '#') && (strlen($btnBg) === 6 || strlen($btnBg) === 3)) {
                $btnBg = '#' . $btnBg;
            }

            $btnText = setting("home_promo_{$number}_button_text_color") ?: $default['button_text_color'];
            if (filled($btnText) && ! str_starts_with($btnText, '#') && (strlen($btnText) === 6 || strlen($btnText) === 3)) {
                $btnText = '#' . $btnText;
            }

            $cards[$number] = [
                'title' => setting("home_promo_{$number}_title") ?: $default['title'],
                'text' => setting("home_promo_{$number}_text") ?: $default['text'],
                'button' => setting("home_promo_{$number}_button") ?: $default['button'],
                'product_id' => filled($productId) ? (int) $productId : null,
                'bg_color_start' => $bgStart,
                'bg_color_end' => $bgEnd,
                'title_color' => $titleColor,
                'text_color' => $textColor,
                'button_bg_color' => $btnBg,
                'button_text_color' => $btnText,
            ];
        }

        return $cards;
    }

    /**
     * @return array{title: string, text: string, placeholder: string, button: string, button_text_color: string, button_bg_color: string}
     */
    public static function newsletter(): array
    {
        $content = [];
        foreach (self::NEWSLETTER_DEFAULTS as $key => $default) {
            $val = setting("home_newsletter_{$key}");
            if (str_ends_with($key, '_color') && filled($val)) {
                $val = '#'.ltrim((string) $val, '#');
            }
            $content[$key] = filled($val) ? (string) $val : $default;
        }

        return $content;
    }
}
