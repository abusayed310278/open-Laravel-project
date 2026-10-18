<?php

namespace App\Support;

/**
 * Generates a Tailwind-style 50–900 tonal scale from a single admin-picked
 * hex color, so the Settings module's "brand color" only needs one input
 * while every shade used across the design system (badges, hovers, soft
 * backgrounds) still has something to render.
 */
class ColorScale
{
    /**
     * @return array<string, string> Keys 50, 100, ..., 900 mapped to hex.
     */
    public static function fromHex(string $hex): array
    {
        $hex = '#' . strtolower(ltrim($hex, '#'));
        [$h, $s, $l] = self::hexToHsl($hex);

        // 500 is ALWAYS the exact input color so brand primary elements match 1:1.
        // Shades 50–400 scale smoothly from 97% down towards $l.
        // Shades 600–900 scale smoothly from $l down towards deep shadow.
        $l400 = $l > 60 ? ($l + 74.0) / 2 : max(30.0, ($l + 74.0) / 2);

        return [
            '50'  => self::hslToHex($h, $s, 97.0),
            '100' => self::hslToHex($h, $s, 93.0),
            '200' => self::hslToHex($h, $s, 85.0),
            '300' => self::hslToHex($h, $s, 74.0),
            '400' => self::hslToHex($h, $s, (float) $l400),
            '500' => $hex,
            '600' => self::hslToHex($h, $s, (float) max(12.0, $l * 0.84)),
            '700' => self::hslToHex($h, $s, (float) max(9.0, $l * 0.68)),
            '800' => self::hslToHex($h, $s, (float) max(6.0, $l * 0.52)),
            '900' => self::hslToHex($h, $s, (float) max(4.0, $l * 0.38)),
        ];
    }

    /**
     * @return array{0: float, 1: float, 2: float} Hue (0-360), saturation and lightness (0-100).
     */
    private static function hexToHsl(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            return [0.0, 0.0, $l * 100];
        }

        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        $h = match ($max) {
            $r => fmod(($g - $b) / $d + ($g < $b ? 6 : 0), 6),
            $g => ($b - $r) / $d + 2,
            default => ($r - $g) / $d + 4,
        };

        return [$h * 60, $s * 100, $l * 100];
    }

    private static function hslToHex(float $h, float $s, float $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf(
            '#%02x%02x%02x',
            (int) round(($r + $m) * 255),
            (int) round(($g + $m) * 255),
            (int) round(($b + $m) * 255),
        );
    }

    /**
     * Returns '#0f172a' (dark) or '#ffffff' (white) depending on the perceived brightness of the hex color.
     */
    public static function contrastText(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#0f172a';
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return ($yiq >= 155) ? '#0f172a' : '#ffffff';
    }
}

