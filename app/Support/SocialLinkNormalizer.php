<?php

namespace App\Support;

/**
 * Shared normalization for the dynamic {platform, url} social-link rows on
 * business/saler store settings: auto-prefixes bare handles/domains with
 * https:// (and a platform-specific host when a bare handle is given), used
 * both at form-request validation time and again before persisting.
 */
class SocialLinkNormalizer
{
    private const array PLATFORM_HOSTS = [
        'facebook' => 'https://facebook.com/',
        'youtube' => 'https://youtube.com/',
        'twitter' => 'https://x.com/',
        'x' => 'https://x.com/',
        'instagram' => 'https://instagram.com/',
        'linkedin' => 'https://linkedin.com/in/',
        'whatsapp' => 'https://wa.me/',
    ];

    /**
     * @param  array<int|string, array{platform?: string, url?: string}>  $rows
     * @return array<int, array{platform: string, url: string}>
     */
    public static function prepare(array $rows): array
    {
        $prepared = [];

        foreach ($rows as $row) {
            $platform = trim((string) ($row['platform'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));

            if ($url !== '') {
                if (str_starts_with($url, '@')) {
                    $url = substr($url, 1);
                }

                if (!preg_match('~^https?://~i', $url)) {
                    $host = self::PLATFORM_HOSTS[strtolower($platform)] ?? 'https://';
                    $url = str_contains($url, '/') || str_contains($url, '.')
                        ? 'https://' . $url
                        : $host . $url;
                }
            }

            $prepared[] = ['platform' => $platform, 'url' => $url];
        }

        return $prepared;
    }

    /**
     * Drop incomplete rows and reindex, for persistence.
     *
     * @param  mixed  $rows
     * @return array<int, array{platform: string, url: string}>
     */
    public static function forStorage(mixed $rows): array
    {
        if (!is_array($rows)) {
            return [];
        }

        return collect(self::prepare($rows))
            ->filter(fn (array $row) => $row['platform'] !== '' && $row['url'] !== '')
            ->values()
            ->all();
    }

    /**
     * Accepts either the current [{platform, url}, ...] shape or the legacy
     * shape (an assoc array keyed by platform slug, e.g. {"instagram": "..."})
     * that older business profiles were saved with, and normalizes both to
     * a list for rendering the dynamic rows in the edit form.
     *
     * @return array<int, array{platform: string, url: string}>
     */
    public static function normalizeForDisplay(mixed $stored): array
    {
        if (!is_array($stored)) {
            return [];
        }

        if (array_is_list($stored)) {
            return collect($stored)
                ->filter(fn ($row) => is_array($row) && !empty($row['url']))
                ->map(fn (array $row) => ['platform' => (string) ($row['platform'] ?? ''), 'url' => (string) $row['url']])
                ->values()
                ->all();
        }

        return collect($stored)
            ->filter(fn ($url) => !empty($url))
            ->map(fn ($url, $platform) => ['platform' => (string) $platform, 'url' => (string) $url])
            ->values()
            ->all();
    }
}
