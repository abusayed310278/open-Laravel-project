<?php

namespace App\Support;

class FooterAppLinks
{
    public const array APP_PLATFORMS = [
        'google-play' => [
            'slug' => 'google-play',
            'name' => 'Google Play',
            'subtitle' => 'Download on',
            'default_title' => 'Google Play',
            'placeholder' => 'https://play.google.com/store/apps/details?id=com.openbox.app',
            'icon_svg' => '<svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M3.6 2.4l11.4 11.4-3.3 3.3L3.6 2.4z" fill="#00E676"/><path d="M15 13.8l3.6-3.6c.7-.7.7-1.8 0-2.5l-3.6-3.6-3.3 3.3 3.3 6.4z" fill="#FFD600"/><path d="M3.6 2.4c-.4.4-.6 1-.6 1.7v15.8c0 .7.2 1.3.6 1.7l8.1-8.1-8.1-11.1z" fill="#00B0FF"/><path d="M3.6 21.6l11.4-11.4-3.3-3.3-8.1 14.7z" fill="#FF3D00"/></svg>',
        ],
        'app-store' => [
            'slug' => 'app-store',
            'name' => 'Apple App Store',
            'subtitle' => 'Download on',
            'default_title' => 'App Store',
            'placeholder' => 'https://apps.apple.com/app/openbox/id123456789',
            'icon_svg' => '<svg class="w-5 h-5 shrink-0 fill-current" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.38c.62-.75 1.04-1.8 0.93-2.85-.9.04-1.99.6-2.63 1.35-.57.66-1.06 1.73-.93 2.76 1 .08 2.01-.51 2.63-1.26z"/></svg>',
        ],
        'huawei' => [
            'slug' => 'huawei',
            'name' => 'Huawei AppGallery',
            'subtitle' => 'EXPLORE IT ON',
            'default_title' => 'AppGallery',
            'placeholder' => 'https://appgallery.huawei.com/app/C123456',
            'icon_svg' => '<svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3v1h-6V8c0-1.66 1.34-3 3-3zm6 13H6v-7h12v7z"/></svg>',
        ],
        'apk' => [
            'slug' => 'apk',
            'name' => 'Android APK Direct',
            'subtitle' => 'DIRECT DOWNLOAD',
            'default_title' => 'Android APK',
            'placeholder' => 'https://openbox.com/download/openbox.apk',
            'icon_svg' => '<svg class="w-4 h-4 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="currentColor"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-1.0003s.4482-1.0002.9993-1.0002c.5516 0 .9997.4485.9997 1.0002 0 .5517-.4481 1.0003-.9997 1.0003m-11.046 0c-.5511 0-.9993-.4486-.9993-1.0003s.4482-1.0002.9993-1.0002c.5516 0 .9997.4485.9997 1.0002 0 .5517-.4481 1.0003-.9997 1.0003m11.4045-6.02l1.9973-3.4592a.416.416 0 00-.1521-.5676.416.416 0 00-.5676.1521l-2.0223 3.503C15.5902 8.4116 13.8533 8.0838 12 8.0838s-3.5902.3278-5.1368.866L4.8409 5.4468a.4161.4161 0 00-.5677-.1521.4157.4157 0 00-.1521.5676l1.9973 3.4592C2.6889 11.1867.3432 14.6589 0 18.7778h24c-.3432-4.1189-2.6889-7.5911-6.1185-9.4564"/></svg>',
        ],
        'microsoft' => [
            'slug' => 'microsoft',
            'name' => 'Microsoft Store',
            'subtitle' => 'GET IT FROM',
            'default_title' => 'Microsoft',
            'placeholder' => 'https://apps.microsoft.com/detail/openbox',
            'icon_svg' => '<svg class="w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="currentColor"><path d="M2 2h9.5v9.5H2V2zm10.5 0H22v9.5h-9.5V2zM2 12.5h9.5V22H2v-9.5zm10.5 0H22V22h-9.5v-9.5z"/></svg>',
        ],
        'pwa' => [
            'slug' => 'pwa',
            'name' => 'Web App / PWA',
            'subtitle' => 'INSTALL',
            'default_title' => 'Web App',
            'placeholder' => 'https://openbox.com/pwa',
            'icon_svg' => '<svg class="w-4 h-4 shrink-0 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="3"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>',
        ],
    ];

    public const array SOCIAL_PLATFORMS = [
        'facebook' => [
            'slug' => 'facebook',
            'name' => 'Facebook',
            'placeholder' => 'https://facebook.com/openbox',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        ],
        'x' => [
            'slug' => 'x',
            'name' => 'X / Twitter',
            'placeholder' => 'https://x.com/openbox',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        ],
        'instagram' => [
            'slug' => 'instagram',
            'name' => 'Instagram',
            'placeholder' => 'https://instagram.com/openbox',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
        ],
        'youtube' => [
            'slug' => 'youtube',
            'name' => 'YouTube',
            'placeholder' => 'https://youtube.com/@openbox',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        ],
        'linkedin' => [
            'slug' => 'linkedin',
            'name' => 'LinkedIn',
            'placeholder' => 'https://linkedin.com/company/openbox',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>',
        ],
        'whatsapp' => [
            'slug' => 'whatsapp',
            'name' => 'WhatsApp',
            'placeholder' => 'https://wa.me/8801700000000',
            'icon_svg' => '<svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>',
        ],
    ];

    /**
     * @return array<int, array{platform: string, name: string, title: string, subtitle: string, url: string, icon_svg: string}>
     */
    public static function getAppLinks(): array
    {
        $raw = setting('footer_app_links');
        $decoded = !empty($raw) ? (is_string($raw) ? json_decode($raw, true) : (array) $raw) : null;

        $result = [];
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (empty($item['is_active']) || empty($item['url'])) {
                    continue;
                }

                $slug = (string) ($item['platform'] ?? '');
                $meta = self::APP_PLATFORMS[$slug] ?? null;

                $subtitle = !empty($item['subtitle']) ? (string) $item['subtitle'] : ($meta['subtitle'] ?? 'Download on');
                if (in_array(strtoupper(trim($subtitle)), ['GET IT ON', 'DOWNLOAD ON THE'])) {
                    $subtitle = 'Download on';
                }

                $result[] = [
                    'platform' => $slug,
                    'name' => $meta['name'] ?? ucfirst($slug),
                    'title' => !empty($item['title']) ? (string) $item['title'] : ($meta['default_title'] ?? ucfirst($slug)),
                    'subtitle' => $subtitle,
                    'url' => (string) $item['url'],
                    'icon_svg' => $meta['icon_svg'] ?? '<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                ];
            }
        }

        // Default Star Tech style apps if none active/set
        if (empty($result)) {
            $defaultPlatforms = ['google-play', 'app-store'];
            foreach ($defaultPlatforms as $slug) {
                $meta = self::APP_PLATFORMS[$slug] ?? null;
                if ($meta) {
                    $result[] = [
                        'platform' => $slug,
                        'name' => $meta['name'],
                        'title' => $meta['default_title'],
                        'subtitle' => $meta['subtitle'] ?? 'Download on',
                        'url' => '#',
                        'icon_svg' => $meta['icon_svg'],
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * @return array<int, array{platform: string, name: string, url: string, icon_svg: string}>
     */
    public static function getSocialLinks(): array
    {
        $raw = setting('footer_social_links');
        $decoded = !empty($raw) ? (is_string($raw) ? json_decode($raw, true) : (array) $raw) : null;

        $result = [];
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (empty($item['is_active']) || empty($item['url'])) {
                    continue;
                }

                $slug = (string) ($item['platform'] ?? '');
                $meta = self::SOCIAL_PLATFORMS[$slug] ?? null;

                $result[] = [
                    'platform' => $slug,
                    'name' => $meta['name'] ?? ucfirst($slug),
                    'url' => (string) $item['url'],
                    'icon_svg' => $meta['icon_svg'] ?? '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>',
                ];
            }
        }

        // Default Star Tech style social icons (WhatsApp, Facebook, YouTube, Instagram) if none active/set
        if (empty($result)) {
            $defaultPlatforms = ['whatsapp', 'facebook', 'youtube', 'instagram'];
            foreach ($defaultPlatforms as $slug) {
                $meta = self::SOCIAL_PLATFORMS[$slug] ?? null;
                if ($meta) {
                    $result[] = [
                        'platform' => $slug,
                        'name' => $meta['name'],
                        'url' => '#',
                        'icon_svg' => $meta['icon_svg'],
                    ];
                }
            }
        }

        return $result;
    }
}
