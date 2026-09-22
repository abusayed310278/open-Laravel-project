@props(['navGroups' => [], 'portalLabel' => 'Dashboard'])

<style>
    #sidebar {
        transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .main-content-wrapper {
        transition: margin-left 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    @media (min-width: 1024px) {
        .sidebar-collapsed #sidebar {
            width: 72px !important;
        }
        .sidebar-collapsed .main-content-wrapper {
            margin-left: 72px !important;
        }
        .sidebar-collapsed #sidebar .sidebar-brand-text,
        .sidebar-collapsed #sidebar .sidebar-group-label,
        .sidebar-collapsed #sidebar .sidebar-text {
            display: none !important;
            opacity: 0 !important;
        }
        .sidebar-collapsed #sidebar .sidebar-header-container {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
            justify-content: center !important;
        }
        .sidebar-collapsed #sidebar .sidebar-brand-link {
            justify-content: center !important;
            gap: 0 !important;
        }
        .sidebar-collapsed #sidebar .sidebar-nav-item {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        .sidebar-collapsed #sidebar .sidebar-group-divider {
            display: block !important;
            height: 1px !important;
            background-color: #f1f5f9 !important;
            margin: 0.75rem 0.25rem !important;
        }
    }
</style>

<aside
    id="sidebar"
    class="w-56 bg-white border-r border-gray-100 h-screen fixed left-0 top-0 flex flex-col z-40 transform -translate-x-full lg:translate-x-0 overflow-hidden shadow-xs"
>
    {{-- Sidebar Top Header --}}
    <div class="sidebar-header-container flex items-center justify-between gap-2 px-5 py-5 border-b border-gray-50 shrink-0">
        <a href="{{ url('/') }}" class="sidebar-brand-link flex items-center gap-2.5 group overflow-hidden" title="{{ config('app.name', 'Openbox') }}">
            @php
                $customLogo = setting('brand_logo');
                $siteIcon = setting('site_icon');
            @endphp
            @if (!empty($customLogo) && is_string($customLogo) && trim($customLogo) !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($customLogo) }}" alt="{{ config('app.name', 'Openbox') }}" class="h-8 w-auto max-w-[140px] object-contain shrink-0" />
            @else
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <img src="{{ !empty($siteIcon) && is_string($siteIcon) && trim($siteIcon) !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteIcon) ? \Illuminate\Support\Facades\Storage::disk('public')->url($siteIcon) : asset('icon.png') }}" alt="{{ config('app.name', 'Openbox') }}" class="w-8 h-8 object-contain" width="32" height="32" />
                </div>
                <div class="sidebar-brand-text flex flex-col min-w-0 transition-opacity duration-200">
                    <span class="font-black text-gray-900 text-sm tracking-tight leading-tight group-hover:text-brand-600 transition-colors truncate">{{ config('app.name', 'Openbox') }}</span>
                    <span class="text-[10px] text-gray-400 font-medium truncate">{{ $portalLabel }}</span>
                </div>
            @endif
        </a>

        {{-- Mobile Close Button --}}
        <button id="sidebar-close" type="button" onclick="toggleDashboardSidebar(false)" class="lg:hidden text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    {{-- Nav Items (Scrollable with preserved position) --}}
    @php
        $user = auth()->user();
        $userRoleSlug = $roleSlug ?? ($user?->role?->value ?? $user?->roleModel?->slug ?? null);
        $siteMgmt = app(\App\Services\SiteManagementService::class);
        $dynamicMenus = $userRoleSlug ? $siteMgmt->getMenuForRole($userRoleSlug) : collect();

        if ($dynamicMenus->isNotEmpty()) {
            $effectiveGroups = [];
            $grouped = $dynamicMenus->groupBy('group_name');
            $iconPaths = \App\Support\Icons::PATHS;

            foreach ($grouped as $groupLabel => $items) {
                $filteredItems = [];
                foreach ($items as $item) {
                    if ($item->permission_slug && !user_can($item->permission_slug)) {
                        continue;
                    }
                    $iconKey = $item->icon;
                    if (empty($iconKey) || !isset($iconPaths[$iconKey])) {
                        $t = strtolower(($item->title ?? '') . ' ' . ($item->route_name ?? ''));
                        if (str_contains($t, 'dash')) $iconKey = 'home';
                        elseif (str_contains($t, 'site') || str_contains($t, 'whole')) $iconKey = 'sliders';
                        elseif (str_contains($t, 'user') || str_contains($t, 'profile') || str_contains($t, 'address') || str_contains($t, 'account')) $iconKey = 'users';
                        elseif (str_contains($t, 'setting') || str_contains($t, 'brand')) $iconKey = 'cog';
                        elseif (str_contains($t, 'report') || str_contains($t, 'visitor')) $iconKey = 'chart';
                        elseif (str_contains($t, 'product') || str_contains($t, 'catalog') || str_contains($t, 'item')) $iconKey = 'box';
                        elseif (str_contains($t, 'categor') || str_contains($t, 'tag')) $iconKey = 'tag';
                        elseif (str_contains($t, 'order') || str_contains($t, 'shop')) $iconKey = 'shopping-bag';
                        elseif (str_contains($t, 'invoice') || str_contains($t, 'payout') || str_contains($t, 'wallet') || str_contains($t, 'payment')) $iconKey = 'banknotes';
                        elseif (str_contains($t, 'plan') || str_contains($t, 'subscript')) $iconKey = 'credit-card';
                        elseif (str_contains($t, 'verif') || str_contains($t, 'inspect') || str_contains($t, 'queue') || str_contains($t, 'kyc') || str_contains($t, 'hub')) $iconKey = 'shield';
                        elseif (str_contains($t, 'house') || str_contains($t, 'stock') || str_contains($t, 'store')) $iconKey = 'building';
                        elseif (str_contains($t, 'chat') || str_contains($t, 'messag')) $iconKey = 'chat';
                        elseif (str_contains($t, 'support') || str_contains($t, 'ticket')) $iconKey = 'support';
                        elseif (str_contains($t, 'review')) $iconKey = 'star';
                        elseif (str_contains($t, 'wishlist')) $iconKey = 'heart';
                        elseif (str_contains($t, 'return')) $iconKey = 'undo';
                        else $iconKey = 'box';
                    }

                    $iconSvg = $iconPaths[$iconKey] ?? $iconPaths['box'];
                    $filteredItems[] = [
                        'route' => $item->route_name,
                        'url' => $item->url_path,
                        'label' => $item->title,
                        'icon' => $iconSvg,
                    ];
                }
                if (!empty($filteredItems)) {
                    $effectiveGroups[] = [
                        'label' => ($groupLabel === 'MAIN' || $groupLabel === 'Dashboard' || $groupLabel === 'Navigation') ? null : $groupLabel,
                        'items' => $filteredItems,
                    ];
                }
            }
        } else {
            $effectiveGroups = $navGroups;
        }
    @endphp

    <nav id="sidebar-nav" class="flex-1 px-3 py-3 space-y-3 overflow-y-auto">
        @foreach ($effectiveGroups as $group)
            <div>
                @if (!empty($group['label']))
                    <p class="sidebar-group-label px-3 pb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider" style="font-size: 10px; letter-spacing: 0.05em;">{{ $group['label'] }}</p>
                    <div class="sidebar-group-divider hidden"></div>
                @endif
                <div class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        @php
                            if (isset($item['permission']) && !user_can($item['permission'])) {
                                continue;
                            }
                            if (isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route'])) {
                                $href = route($item['route']);
                                $isActive = request()->routeIs($item['route'] . '*');
                            } elseif (!empty($item['url'])) {
                                $href = url($item['url']);
                                $isActive = request()->is(trim($item['url'], '/'));
                            } else {
                                $href = '#';
                                $isActive = false;
                            }
                        @endphp
                        <x-dashboard.nav-item
                            :href="$href"
                            :icon="$item['icon']"
                            :active="$isActive"
                        >
                            {{ $item['label'] }}
                        </x-dashboard.nav-item>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>

<div id="sidebar-overlay" onclick="toggleDashboardSidebar(false)" class="hidden fixed inset-0 bg-black/40 z-30 lg:hidden backdrop-blur-xs"></div>

<script>
    (function() {
        const nav = document.getElementById('sidebar-nav');
        if (!nav) return;

        function restoreSidebarScroll() {
            const savedScroll = sessionStorage.getItem('sidebar_nav_scroll_top');
            const activeItem = nav.querySelector('.sidebar-nav-item.bg-brand-50, .sidebar-nav-item[class*="bg-brand"]');

            if (savedScroll !== null) {
                nav.scrollTop = parseInt(savedScroll, 10);
            } else if (activeItem) {
                const navRect = nav.getBoundingClientRect();
                const itemRect = activeItem.getBoundingClientRect();

                if (itemRect.top < navRect.top || itemRect.bottom > navRect.bottom) {
                    activeItem.scrollIntoView({ block: 'center', behavior: 'instant' });
                }
            }
        }

        // Store scroll position on navigation link click
        nav.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link) {
                sessionStorage.setItem('sidebar_nav_scroll_top', nav.scrollTop);
            }
        });

        // Store scroll position on page unload
        window.addEventListener('beforeunload', function() {
            if (nav) {
                sessionStorage.setItem('sidebar_nav_scroll_top', nav.scrollTop);
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', restoreSidebarScroll);
        } else {
            restoreSidebarScroll();
        }

        window.addEventListener('load', restoreSidebarScroll);
    })();
</script>
