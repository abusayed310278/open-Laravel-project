@php
    $tabs = [
        'logo' => ['label' => 'Logo', 'route' => 'admin.settings.logo'],
        'banner' => ['label' => 'Banner', 'route' => 'admin.settings.banner'],
        'hero' => ['label' => 'Hero', 'route' => 'admin.settings.hero'],
        'why-buy' => ['label' => 'Why Buy', 'route' => 'admin.settings.why-buy'],
        'promo' => ['label' => 'Promo Cards', 'route' => 'admin.settings.promo'],
        'newsletter' => ['label' => 'Newsletter', 'route' => 'admin.settings.newsletter'],
        'siteicon' => ['label' => 'Site Icon', 'route' => 'admin.settings.siteicon'],
        'footericon' => ['label' => 'Footer Icon', 'route' => 'admin.settings.footericon'],
        'font' => ['label' => 'Font', 'route' => 'admin.settings.font'],

        'color' => ['label' => 'Color', 'route' => 'admin.settings.color'],
        'cache' => ['label' => 'Cache Clear', 'route' => 'admin.settings.cache'],
        'mail' => ['label' => 'Mail (SMTP)', 'route' => 'admin.settings.mail'],
        'storage' => ['label' => 'Storage', 'route' => 'admin.settings.storage'],
        'payments' => ['label' => 'Payments', 'route' => 'admin.settings.payments'],
        'system' => ['label' => 'System', 'route' => 'admin.settings.system'],
        'git' => ['label' => 'Git', 'route' => 'admin.settings.git'],
        'seller-banner' => ['label' => 'Seller Banner', 'route' => 'admin.settings.seller-banner'],
    ];
@endphp

<div class="flex items-stretch gap-1 border-b border-gray-100 mb-6" data-tabs-scroller>
    <button type="button" data-tabs-prev aria-label="Scroll tabs left" class="hidden shrink-0 w-8 items-center justify-center text-gray-500 hover:text-gray-900 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
    </button>

    <div data-tabs-track class="flex min-w-0 flex-1 items-center gap-6 overflow-x-auto whitespace-nowrap pb-px scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($tabs as $key => $tab)
            @php($isActive = request()->routeIs($tab['route']) || (request()->routeIs('admin.settings.branding') && $key === 'logo'))
            <a
                href="{{ route($tab['route']) }}"
                @if ($isActive) data-tab-active aria-current="page" @endif
                @class([
                    'text-sm font-medium py-3 border-b-2 -mb-px transition-colors flex items-center gap-2 shrink-0',
                    'border-brand-500 text-brand-600' => $isActive,
                    'border-transparent text-gray-500 hover:text-gray-800' => ! $isActive,
                ])
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </div>

    <button type="button" data-tabs-next aria-label="Scroll tabs right" class="hidden shrink-0 w-8 items-center justify-center text-gray-500 hover:text-gray-900 cursor-pointer">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
    </button>
</div>

<script>
    document.querySelectorAll('[data-tabs-scroller]').forEach((root) => {
        const track = root.querySelector('[data-tabs-track]');
        const prev = root.querySelector('[data-tabs-prev]');
        const next = root.querySelector('[data-tabs-next]');

        const update = () => {
            const max = track.scrollWidth - track.clientWidth;
            prev.classList.toggle('hidden', track.scrollLeft <= 4);
            prev.classList.toggle('flex', track.scrollLeft > 4);
            next.classList.toggle('hidden', track.scrollLeft >= max - 4);
            next.classList.toggle('flex', track.scrollLeft < max - 4);
        };

        prev.addEventListener('click', () => track.scrollBy({ left: -track.clientWidth * 0.6 }));
        next.addEventListener('click', () => track.scrollBy({ left: track.clientWidth * 0.6 }));
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);

        const active = track.querySelector('[data-tab-active]');
        if (active) {
            track.scrollLeft = active.offsetLeft - (track.clientWidth - active.offsetWidth) / 2;
        }
        update();
    });
</script>
