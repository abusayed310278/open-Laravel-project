<?php

namespace App\Support;

/**
 * Admin-editable homepage copy (Settings → Why Buy / Promo Cards / Newsletter),
 * stored in the settings table with these defaults as fallbacks.
 */
final class HomeContent
{
    public const WHY_BUY_DEFAULTS = [
        ['title' => 'Verified Quality', 'text' => 'All products tested and verified'],
        ['title' => 'Fast & Reliable Delivery', 'text' => 'Across Abu Dhabi & UAE'],
        ['title' => 'A More Sustainable Choice', 'text' => 'Give tech a second life'],
        ['title' => 'Dedicated Support', 'text' => "We're here to help"],
    ];

    public const PROMO_DEFAULTS = [
        1 => ['title' => "Laptops\nfor Work & Study", 'text' => "Premium performance\nat lower prices.", 'button' => 'Shop Laptops'],
        2 => ['title' => 'Smartphones', 'text' => "Stay Connected\nfor Less.", 'button' => 'Shop Phones'],
        3 => ['title' => 'Sell Your Device', 'text' => "Turn your unused electronics\ninto cash.", 'button' => 'Start Selling'],
    ];

    public const NEWSLETTER_DEFAULTS = [
        'title' => 'Stay Updated',
        'text' => 'Get the latest deals and new arrivals.',
        'placeholder' => 'Your email address',
        'button' => 'Subscribe',
    ];

    /**
     * @return array{heading: string, items: array<int, array{title: string, text: string}>}
     */
    public static function whyBuy(): array
    {
        $items = [];
        foreach (self::WHY_BUY_DEFAULTS as $index => $default) {
            $number = $index + 1;
            $items[] = [
                'title' => setting("home_whybuy_{$number}_title") ?: $default['title'],
                'text' => setting("home_whybuy_{$number}_text") ?: $default['text'],
            ];
        }

        return [
            'heading' => setting('home_whybuy_heading') ?: 'Why Buy from OpenBox?',
            'items' => $items,
        ];
    }

    /**
     * @return array<int, array{title: string, text: string, button: string, product_id: ?int}>
     */
    public static function promoCards(): array
    {
        $cards = [];
        foreach (self::PROMO_DEFAULTS as $number => $default) {
            $productId = setting("home_promo_{$number}_product");
            $cards[$number] = [
                'title' => setting("home_promo_{$number}_title") ?: $default['title'],
                'text' => setting("home_promo_{$number}_text") ?: $default['text'],
                'button' => setting("home_promo_{$number}_button") ?: $default['button'],
                'product_id' => filled($productId) ? (int) $productId : null,
            ];
        }

        return $cards;
    }

    /**
     * @return array{title: string, text: string, placeholder: string, button: string}
     */
    public static function newsletter(): array
    {
        $content = [];
        foreach (self::NEWSLETTER_DEFAULTS as $key => $default) {
            $content[$key] = setting("home_newsletter_{$key}") ?: $default;
        }

        return $content;
    }
}
