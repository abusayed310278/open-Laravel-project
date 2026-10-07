<?php

namespace App\Services;

use GdImage;

class ImageBackgroundRemover
{
    private const MAX_DIMENSION = 1200;

    private const TOLERANCE = 30;

    private const MIN_BORDER_MATCH = 0.85;

    /**
     * Cuts a uniform studio background out of a product photo.
     *
     * Returns the path of a temporary transparent PNG, or null when the image has no
     * uniform background (e.g. a lifestyle photo) or GD is unavailable.
     */
    public function process(string $path): ?string
    {
        if (! extension_loaded('gd') || ! is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $source = $raw === false ? false : @imagecreatefromstring($raw);

        if (! $source instanceof GdImage) {
            return null;
        }

        $image = $this->prepare($source);
        $width = imagesx($image);
        $height = imagesy($image);

        $background = $this->detectBackground($image, $width, $height);

        if ($background === null) {
            return null;
        }

        $transparent = $this->floodFromEdges($image, $width, $height, $background);
        $this->applyTransparency($image, $width, $height, $transparent, $background);

        $temp = tempnam(sys_get_temp_dir(), 'bgr');
        $output = $temp.'.png';
        @unlink($temp);
        imagepng($image, $output, 6);

        return $output;
    }

    private function prepare(GdImage $source): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX_DIMENSION / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $image = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($image, true);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @return array{int, int, int}|null
     */
    private function detectBackground(GdImage $image, int $width, int $height): ?array
    {
        $corners = array_map(
            fn (array $p): array => $this->rgb(imagecolorat($image, $p[0], $p[1])),
            [[0, 0], [$width - 1, 0], [0, $height - 1], [$width - 1, $height - 1]]
        );

        $reference = [
            (int) round(array_sum(array_column($corners, 0)) / 4),
            (int) round(array_sum(array_column($corners, 1)) / 4),
            (int) round(array_sum(array_column($corners, 2)) / 4),
        ];

        $matches = 0;
        $total = 0;
        $stepX = max(1, intdiv($width, 200));
        $stepY = max(1, intdiv($height, 200));

        for ($x = 0; $x < $width; $x += $stepX) {
            foreach ([0, $height - 1] as $y) {
                $total++;
                $matches += $this->distance($this->rgb(imagecolorat($image, $x, $y)), $reference) <= self::TOLERANCE ? 1 : 0;
            }
        }

        for ($y = 0; $y < $height; $y += $stepY) {
            foreach ([0, $width - 1] as $x) {
                $total++;
                $matches += $this->distance($this->rgb(imagecolorat($image, $x, $y)), $reference) <= self::TOLERANCE ? 1 : 0;
            }
        }

        return ($matches / $total) >= self::MIN_BORDER_MATCH ? $reference : null;
    }

    /**
     * @param  array{int, int, int}  $background
     * @return array<int, true>
     */
    private function floodFromEdges(GdImage $image, int $width, int $height, array $background): array
    {
        $visited = [];
        $stack = [];

        for ($x = 0; $x < $width; $x++) {
            $stack[] = $x;
            $stack[] = ($height - 1) * $width + $x;
        }

        for ($y = 0; $y < $height; $y++) {
            $stack[] = $y * $width;
            $stack[] = $y * $width + $width - 1;
        }

        while ($stack !== []) {
            $index = array_pop($stack);

            if (isset($visited[$index])) {
                continue;
            }

            $x = $index % $width;
            $y = intdiv($index, $width);

            if ($this->distance($this->rgb(imagecolorat($image, $x, $y)), $background) > self::TOLERANCE) {
                continue;
            }

            $visited[$index] = true;

            if ($x > 0) {
                $stack[] = $index - 1;
            }
            if ($x < $width - 1) {
                $stack[] = $index + 1;
            }
            if ($y > 0) {
                $stack[] = $index - $width;
            }
            if ($y < $height - 1) {
                $stack[] = $index + $width;
            }
        }

        return $visited;
    }

    /**
     * @param  array<int, true>  $transparent
     * @param  array{int, int, int}  $background
     */
    private function applyTransparency(GdImage $image, int $width, int $height, array $transparent, array $background): void
    {
        $clear = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $edges = [];

        foreach ($transparent as $index => $_) {
            $x = $index % $width;
            $y = intdiv($index, $width);

            foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as [$dx, $dy]) {
                $nx = $x + $dx;
                $ny = $y + $dy;

                if ($nx >= 0 && $ny >= 0 && $nx < $width && $ny < $height && ! isset($transparent[$ny * $width + $nx])) {
                    $edges[$ny * $width + $nx] = true;
                }
            }
        }

        foreach ($transparent as $index => $_) {
            imagesetpixel($image, $index % $width, intdiv($index, $width), $clear);
        }

        foreach ($edges as $index => $_) {
            $x = $index % $width;
            $y = intdiv($index, $width);
            $rgb = $this->rgb(imagecolorat($image, $x, $y));
            $ratio = min(1, $this->distance($rgb, $background) / (self::TOLERANCE * 3));
            $alpha = (int) round(127 * (1 - $ratio));

            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $rgb[0], $rgb[1], $rgb[2], $alpha));
        }
    }

    /**
     * @return array{int, int, int}
     */
    private function rgb(int $color): array
    {
        return [($color >> 16) & 0xFF, ($color >> 8) & 0xFF, $color & 0xFF];
    }

    /**
     * @param  array{int, int, int}  $a
     * @param  array{int, int, int}  $b
     */
    private function distance(array $a, array $b): float
    {
        return sqrt(($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2 + ($a[2] - $b[2]) ** 2);
    }
}
