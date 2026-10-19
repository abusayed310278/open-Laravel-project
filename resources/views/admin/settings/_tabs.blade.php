@php
    $categories = [
        'Branding & Design' => [
            'icon' => '<svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>',
            'items' => [
                'logo' => ['label' => 'Logo', 'route' => 'admin.settings.logo', 'desc' => 'Primary site branding logo'],
                'siteicon' => ['label' => 'Site Icon', 'route' => 'admin.settings.siteicon', 'desc' => 'Favicon & browser tab icons'],
                'font' => ['label' => 'Font', 'route' => 'admin.settings.font', 'desc' => 'Typography & Google font setup'],
                'color' => ['label' => 'Color', 'route' => 'admin.settings.color', 'desc' => 'Theme primary & accent colors'],
                'product-card' => ['label' => 'Product Card', 'route' => 'admin.settings.product-card', 'desc' => 'Card typography, colors & button styles'],
            ],
        ],
        'Homepage & Content' => [
            'icon' => '<svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>',
            'items' => [
                'banner' => ['label' => 'Banner', 'route' => 'admin.settings.banner', 'desc' => 'Homepage carousel slides & video'],
                'hero' => ['label' => 'Hero', 'route' => 'admin.settings.hero', 'desc' => 'Top hero headlines & search'],
                'why-buy' => ['label' => 'Why Buy', 'route' => 'admin.settings.why-buy', 'desc' => 'Trust badges & value propositions'],
                'promo' => ['label' => 'Promo Cards', 'route' => 'admin.settings.promo', 'desc' => 'Promotional cards & deals'],
                'start-selling' => ['label' => 'Start Selling', 'route' => 'admin.settings.start-selling', 'desc' => 'Vendor onboarding banner'],
                'newsletter' => ['label' => 'Newsletter', 'route' => 'admin.settings.newsletter', 'desc' => 'Email subscriber lead section'],
                'verified-badge' => ['label' => 'Verified Badge', 'route' => 'admin.settings.verified-badge', 'desc' => 'Inspection badge settings'],
                'footericon' => ['label' => 'Footer & Icon', 'route' => 'admin.settings.footericon', 'desc' => 'Footer links, icons & background'],
                'app-links' => ['label' => 'App Links', 'route' => 'admin.settings.app-links', 'desc' => 'Mobile app download buttons'],
                'seller-banner' => ['label' => 'Seller Banner', 'route' => 'admin.settings.seller-banner', 'desc' => 'Seller recruitment promos'],
            ],
        ],
        'System & Infrastructure' => [
            'icon' => '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
            'items' => [
                'cache' => ['label' => 'Cache Clear', 'route' => 'admin.settings.cache', 'desc' => 'Clear application & view cache'],
                'mail' => ['label' => 'Mail (SMTP)', 'route' => 'admin.settings.mail', 'desc' => 'SMTP mail server configuration'],
                'storage' => ['label' => 'Storage', 'route' => 'admin.settings.storage', 'desc' => 'Cloudinary & local disk setup'],
                'payments' => ['label' => 'Payments', 'route' => 'admin.settings.payments', 'desc' => 'Payment gateway & methods'],
                'system' => ['label' => 'System', 'route' => 'admin.settings.system', 'desc' => 'Maintenance mode & app settings'],
                'git' => ['label' => 'Git', 'route' => 'admin.settings.git', 'desc' => 'Repository version & deployment'],
            ],
        ],
    ];

    $currentKey = null;
    $currentTab = null;
    $currentCategory = null;
    $flatTabs = [];

    foreach ($categories as $catName => $catData) {
        foreach ($catData['items'] as $key => $tab) {
            $isActive = request()->routeIs($tab['route']) || (request()->routeIs('admin.settings.branding') && $key === 'logo');
            $flatTabs[] = array_merge($tab, ['key' => $key, 'category' => $catName]);
            if ($isActive && ! $currentTab) {
                $currentKey = $key;
                $currentTab = $tab;
                $currentCategory = $catName;
            }
        }
    }

    if (! $currentTab) {
        $currentKey = 'logo';
        $currentTab = $categories['Branding & Design']['items']['logo'];
        $currentCategory = 'Branding & Design';
    }

    $currentIndex = 0;
    foreach ($flatTabs as $idx => $ft) {
        if ($ft['key'] === $currentKey) {
            $currentIndex = $idx;
            break;
        }
    }

    $prevTab = $currentIndex > 0 ? $flatTabs[$currentIndex - 1] : null;
    $nextTab = $currentIndex < count($flatTabs) - 1 ? $flatTabs[$currentIndex + 1] : null;
    $totalTabsCount = count($flatTabs);
@endphp

<div class="relative z-30 mb-6 bg-white border border-gray-200/90 rounded-2xl p-2.5 sm:p-3 shadow-xs">
    <div class="flex flex-wrap items-center justify-between gap-3">

        {{-- Left: Dropdown Trigger & Label --}}
        <div class="flex items-center gap-3 flex-1 min-w-[260px]">
            <div class="relative flex-1 max-w-md" id="settings-menu-container">
                {{-- Main Dropdown Button --}}
                <button
                    type="button"
                    id="settings-dropdown-button"
                    onclick="toggleSettingsMenu(event)"
                    class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 bg-gray-50/90 hover:bg-gray-100 border border-gray-200 hover:border-brand-500 rounded-xl text-left transition-all duration-150 cursor-pointer shadow-2xs group focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-500 ring-4 ring-brand-100 shrink-0"></span>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none">{{ $currentCategory }}</span>
                            <span class="text-sm font-bold text-gray-900 group-hover:text-brand-600 transition-colors truncate mt-0.5">{{ $currentTab['label'] }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-gray-400 group-hover:text-gray-700 shrink-0">
                        <span class="hidden sm:inline-block text-[11px] font-semibold text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-md shadow-2xs">Settings Dropdown</span>
                        <svg id="settings-dropdown-arrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Floating Dropdown Menu --}}
                <div
                    id="settings-dropdown-panel"
                    class="hidden absolute left-0 top-full mt-2 w-full sm:w-[460px] max-w-[95vw] bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 overflow-hidden transform origin-top-left transition-all duration-150"
                >
                    {{-- Search Header --}}
                    <div class="p-3 border-b border-gray-100 bg-gray-50/60">
                        <div class="relative">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                type="text"
                                id="settings-filter-input"
                                oninput="filterSettingsMenuItems(this.value)"
                                placeholder="Search settings (e.g. Mail, Banner, Logo, Footer)..."
                                class="w-full pl-9 pr-8 py-2 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"
                                autocomplete="off"
                            />
                            <button
                                type="button"
                                onclick="clearSettingsFilter()"
                                id="settings-filter-clear"
                                class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer"
                                title="Clear search"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Scrollable List of Categorized Items --}}
                    <div class="max-h-[380px] overflow-y-auto p-2 divide-y divide-gray-50 space-y-2">
                        @foreach ($categories as $categoryName => $catData)
                            <div class="settings-category-block pt-1 first:pt-0">
                                <div class="px-3 py-1 flex items-center gap-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                                    {!! $catData['icon'] !!}
                                    <span>{{ $categoryName }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 mt-1">
                                    @foreach ($catData['items'] as $itemKey => $item)
                                        @php
                                            $isItemActive = $itemKey === $currentKey;
                                        @endphp
                                        <a
                                            href="{{ route($item['route']) }}"
                                            class="settings-menu-item flex items-start gap-2.5 p-2 rounded-xl text-left transition-colors {{ $isItemActive ? 'bg-brand-50 border border-brand-200 text-brand-900 font-bold shadow-2xs' : 'hover:bg-gray-50 text-gray-700 border border-transparent' }}"
                                            data-label="{{ strtolower($item['label'] . ' ' . $item['desc'] . ' ' . $categoryName) }}"
                                        >
                                            <div class="w-1.5 h-1.5 rounded-full {{ $isItemActive ? 'bg-brand-500' : 'bg-gray-300' }} mt-1.5 shrink-0"></div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center justify-between gap-1">
                                                    <span class="text-xs font-bold leading-tight truncate {{ $isItemActive ? 'text-brand-700' : 'text-gray-900' }}">{{ $item['label'] }}</span>
                                                    @if ($isItemActive)
                                                        <svg class="w-3.5 h-3.5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-gray-400 font-normal leading-tight truncate mt-0.5">{{ $item['desc'] }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Bottom Footer with Quick Count --}}
                    <div class="px-3 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                        <span>Total: <strong>{{ $totalTabsCount }}</strong> Setting Sections</span>
                        <span class="text-brand-600 font-semibold cursor-pointer" onclick="toggleSettingsMenu(event)">Close Esc</span>
                    </div>
                </div>
            </div>

            {{-- Native Select (Sync Switcher for Quick Mobile / Keyboard) --}}
            <div class="hidden lg:block relative shrink-0">
                <select
                    onchange="if(this.value) window.location.href=this.value;"
                    aria-label="Quick Select Setting"
                    class="text-xs font-semibold py-2 px-3 bg-white border border-gray-200 rounded-xl text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer shadow-2xs"
                >
                    @foreach ($categories as $categoryName => $catData)
                        <optgroup label="{{ $categoryName }}">
                            @foreach ($catData['items'] as $itemKey => $item)
                                <option value="{{ route($item['route']) }}" {{ $itemKey === $currentKey ? 'selected' : '' }}>
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Right: Previous & Next Quick Jump Buttons --}}
        <div class="flex items-center gap-1.5 shrink-0 ml-auto">
            @if ($prevTab)
                <a
                    href="{{ route($prevTab['route']) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 hover:text-gray-900 transition-colors shadow-2xs"
                    title="Previous: {{ $prevTab['label'] }}"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    <span class="hidden md:inline">{{ $prevTab['label'] }}</span>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </span>
            @endif

            <span class="text-xs font-bold text-gray-400 px-1">{{ $currentIndex + 1 }} / {{ $totalTabsCount }}</span>

            @if ($nextTab)
                <a
                    href="{{ route($nextTab['route']) }}"
                    class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 hover:text-gray-900 transition-colors shadow-2xs"
                    title="Next: {{ $nextTab['label'] }}"
                >
                    <span class="hidden md:inline">{{ $nextTab['label'] }}</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </span>
            @endif
        </div>

    </div>
</div>

<script>
    function toggleSettingsMenu(e) {
        if (e) e.stopPropagation();
        const panel = document.getElementById('settings-dropdown-panel');
        const btn = document.getElementById('settings-dropdown-button');
        const arrow = document.getElementById('settings-dropdown-arrow');
        const searchInput = document.getElementById('settings-filter-input');
        if (!panel) return;

        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
            if (btn) btn.setAttribute('aria-expanded', 'true');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 50);
            }
        } else {
            closeSettingsMenu();
        }
    }

    function closeSettingsMenu() {
        const panel = document.getElementById('settings-dropdown-panel');
        const btn = document.getElementById('settings-dropdown-button');
        const arrow = document.getElementById('settings-dropdown-arrow');
        if (panel) panel.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    function filterSettingsMenuItems(query) {
        const q = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.settings-menu-item');
        const clearBtn = document.getElementById('settings-filter-clear');
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', q.length === 0);
        }

        items.forEach(el => {
            const label = el.getAttribute('data-label') || '';
            const matches = !q || label.includes(q);
            el.style.display = matches ? 'flex' : 'none';
        });

        // Hide empty category headers
        document.querySelectorAll('.settings-category-block').forEach(cat => {
            const visibleItems = cat.querySelectorAll('.settings-menu-item:not([style*="display: none"])');
            cat.style.display = visibleItems.length > 0 ? 'block' : 'none';
        });
    }

    function clearSettingsFilter() {
        const searchInput = document.getElementById('settings-filter-input');
        if (searchInput) {
            searchInput.value = '';
            filterSettingsMenuItems('');
            searchInput.focus();
        }
    }

    // Close dropdown on click outside or on Escape
    document.addEventListener('click', function(e) {
        const container = document.getElementById('settings-menu-container');
        if (container && !container.contains(e.target)) {
            closeSettingsMenu();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSettingsMenu();
        }
    });
</script>
