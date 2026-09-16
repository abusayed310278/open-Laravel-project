@props(['navGroups' => [], 'portalLabel' => 'Dashboard', 'backHref' => null, 'backLabel' => 'Back to Marketplace'])

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
        .sidebar-collapsed #sidebar .sidebar-text,
        .sidebar-collapsed #sidebar .sidebar-footer-text {
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
        .sidebar-collapsed #sidebar .sidebar-footer-link {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
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
            @if ($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($customLogo) }}" alt="{{ config('app.name', 'Openbox') }}" class="h-8 w-auto max-w-[140px] object-contain shrink-0" />
            @else
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0">
                    <img src="{{ $siteIcon && \Illuminate\Support\Facades\Storage::disk('public')->exists($siteIcon) ? \Illuminate\Support\Facades\Storage::disk('public')->url($siteIcon) : asset('icon.png') }}" alt="{{ config('app.name', 'Openbox') }}" class="w-8 h-8 object-contain" />
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
    <nav id="sidebar-nav" class="flex-1 px-3 py-3 space-y-3 overflow-y-auto">
        @foreach ($navGroups as $group)
            <div>
                @if (!empty($group['label']))
                    <p class="sidebar-group-label px-3 pb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider" style="font-size: 10px; letter-spacing: 0.05em;">{{ $group['label'] }}</p>
                    <div class="sidebar-group-divider hidden"></div>
                @endif
                <div class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        <x-dashboard.nav-item
                            :href="isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#'"
                            :icon="$item['icon']"
                            :active="isset($item['route']) && request()->routeIs($item['route'] . '*')"
                        >
                            {{ $item['label'] }}
                        </x-dashboard.nav-item>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Sidebar Footer: Back to Store --}}
    @if (!empty($backHref))
        <div class="px-3 py-3 border-t border-gray-100 space-y-2 bg-gray-50/50 shrink-0">
            <a href="{{ $backHref }}" title="{{ $backLabel }}" class="sidebar-footer-link flex items-center gap-2 px-2.5 py-1.5 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span class="sidebar-footer-text truncate">{{ $backLabel }}</span>
            </a>
        </div>
    @endif
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
