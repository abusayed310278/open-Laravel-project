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
        [$h, $s, $l] = self::hexToHsl($hex);

        // Lightness target per step, tuned so the 500 step lands close to
        // the input color regardless of how light/dark it started.
        $lightness = [
            '50' => 97, '100' => 93, '200' => 85, '300' => 74, '400' => 60,
            '500' => max(35, min(55, $l)), '600' => 42, '700' => 33, '800' => 26, '900' => 20,
        ];

        $scale = [];

        foreach ($lightness as $step => $targetL) {
            $scale[$step] = self::hslToHex($h, $s, (float) $targetL);
        }

        return $scale;
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
}
