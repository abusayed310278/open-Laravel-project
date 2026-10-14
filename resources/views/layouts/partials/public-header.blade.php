<header id="site-header" class="bg-white dark:bg-[#0f172a] border-b border-gray-100 dark:border-slate-800 sticky top-0 z-40 shadow-xs">
    <style>
        .all-cat-container:hover .all-cat-dropdown,
        .all-cat-dropdown:hover {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        .subnav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            margin: 0 2px;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
        }
        .subnav-link:hover {
            color: #0f172a;
            background-color: #f8fafc;
        }
    </style>

    {{-- Main Navbar (Logo, Search Bar, and Actions on a Single Row) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-3 sm:gap-4 lg:gap-8 relative z-30" style="position: relative; z-index: 30;">
        <div class="flex items-center gap-2">
            {{-- Mobile Drawer Toggle Button --}}
            <button id="drawer-open" type="button" class="lg:hidden p-2 text-gray-600 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0" aria-label="Open Mobile Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            {{-- Left: Logo --}}
            <a href="{{ route('home') }}" class="flex-shrink-0 group">
                <x-brand-logo />
            </a>
        </div>

        {{-- Center: Animated Search Bar --}}
        <div class="flex-1 max-w-xl lg:max-w-2xl mx-1 sm:mx-4">
            <x-animated-search />
        </div>

        {{-- Right: Actions & Start Selling --}}
        <div class="flex items-center gap-2.5 sm:gap-4 lg:gap-5 flex-shrink-0">
            {{-- Compare (text appears on hover) --}}
            <a href="{{ route('compare') }}" data-site-compare-link class="relative inline-flex items-center text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-100/80 dark:hover:bg-slate-800/80 group cursor-pointer" title="Compare">
                <div class="relative flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-700 group-hover:text-amber-500 dark:text-gray-300 dark:group-hover:text-amber-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span id="header-compare-badge" class="js-compare-badge absolute -top-1.5 -right-2 bg-amber-500 text-gray-950 text-[10px] font-extrabold h-4 min-w-[16px] px-1 rounded-full flex items-center justify-center shadow-xs transition-all transform scale-100 hidden">
                        0
                    </span>
                </div>
                <span class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-24 group-hover:opacity-100 group-hover:ml-1.5 transition-all duration-200 ease-out text-xs font-semibold whitespace-nowrap select-none">Compare</span>
            </a>

            {{-- Wishlist (text appears on hover) --}}
            <a href="{{ Route::has('wishlist') ? route('wishlist') : '#' }}" class="inline-flex items-center text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-100/80 dark:hover:bg-slate-800/80 group cursor-pointer" title="Wishlist">
                <svg class="w-5 h-5 text-gray-700 group-hover:text-red-500 dark:text-gray-300 dark:group-hover:text-red-400 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                <span class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-24 group-hover:opacity-100 group-hover:ml-1.5 transition-all duration-200 ease-out text-xs font-semibold whitespace-nowrap select-none">Wishlist</span>
            </a>

            {{-- Cart (text appears on hover) --}}
            @php
                $headerCartCount = 0;
                try {
                    $cartService = app(\App\Services\CartService::class);
                    $headerCart = auth()->check()
                        ? $cartService->forUser(auth()->user())
                        : $cartService->forGuest($cartService->sessionId());
                    $headerCartCount = (int) $headerCart->items()->sum('quantity');
                } catch (\Throwable $e) {
                    $headerCartCount = 0;
                }
            @endphp
            <a href="{{ Route::has('cart') ? route('cart') : '#' }}" class="relative inline-flex items-center text-gray-700 hover:text-gray-950 dark:text-gray-300 dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-100/80 dark:hover:bg-slate-800/80 group cursor-pointer" title="Cart">
                <div class="relative flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-gray-700 group-hover:text-amber-500 dark:text-gray-300 dark:group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span id="header-cart-badge" class="js-cart-badge absolute -top-1.5 -right-2 bg-red-600 text-white text-[10px] font-extrabold h-4 min-w-[16px] px-1 rounded-full flex items-center justify-center shadow-xs transition-all transform scale-100 {{ $headerCartCount > 0 ? '' : 'hidden' }}">
                        {{ $headerCartCount }}
                    </span>
                </div>
                <span class="max-w-0 overflow-hidden opacity-0 group-hover:max-w-20 group-hover:opacity-100 group-hover:ml-1.5 transition-all duration-200 ease-out text-xs font-semibold whitespace-nowrap select-none">Cart</span>
            </a>

            {{-- User / Account --}}
            @auth
                @php
                    $headerUser = auth()->user();
                    $dashboardUrl = match($headerUser->role?->value) {
                        'admin' => route('admin.dashboard'),
                        'business' => route('business.dashboard'),
                        'saler' => route('saler.dashboard'),
                        'verifier' => route('verifier.dashboard'),
                        default => route('account.dashboard'),
                    };
                    $avatarUrl = \App\Support\MediaUrl::resolve($headerUser->profile?->avatar);
                @endphp
                <a
                    href="{{ $dashboardUrl }}"
                    class="flex items-center justify-center p-0.5 rounded-full transition-all hover:ring-2 hover:ring-brand-500 hover:scale-105"
                    title="{{ $headerUser->name }} — Go to Dashboard"
                >
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $headerUser->name }}" class="w-8 h-8 rounded-full object-cover shadow-2xs">
                    @else
                        <div class="w-8 h-8 bg-brand-500 text-white rounded-full font-black text-xs flex items-center justify-center shadow-2xs">
                            {{ strtoupper(substr($headerUser->name, 0, 1)) }}
                        </div>
                    @endif
                </a>
            @else
                <a href="{{ Route::has('login') ? route('login') : '#' }}" class="text-gray-600 hover:text-gray-950 transition-colors p-1" title="Account">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                </a>
            @endauth

            {{-- Start Selling Button --}}
            <a href="{{ route('register', ['type' => 'saler']) }}" class="header-start-selling-btn text-xs sm:text-sm font-bold px-3 sm:px-6 py-2 sm:py-2.5 rounded-lg shadow-xs hover:shadow-sm transition-all flex items-center gap-1.5 flex-shrink-0">
                <span class="whitespace-nowrap">Start Selling</span>
            </a>
        </div>
    </div>

    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
        }
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Slim Custom Scrollbar for Category Menus */
        .all-cat-scroll {
            max-height: 400px !important;
            max-height: min(420px, calc(100vh - 160px)) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            overscroll-behavior: contain !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: thin !important;
            scrollbar-color: #94a3b8 #f1f5f9 !important;
        }
        .all-cat-scroll::-webkit-scrollbar {
            width: 7px !important;
            display: block !important;
        }
        .all-cat-scroll::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 4px !important;
        }
        .all-cat-scroll::-webkit-scrollbar-thumb {
            background-color: #94a3b8 !important;
            border-radius: 4px !important;
        }
        .all-cat-scroll::-webkit-scrollbar-thumb:hover {
            background-color: #64748b !important;
        }

        /* All Categories Dropdown Container & Menu */
        .all-categories-container {
            position: relative;
        }
        .all-cat-nav-dropdown {
            position: absolute !important;
            left: 0 !important;
            top: 100% !important;
            padding-top: 2px !important;
            min-width: 240px !important;
            z-index: 50 !important;
            display: none;
        }
        .all-categories-container:hover > .all-cat-nav-dropdown,
        .all-categories-container.is-open > .all-cat-nav-dropdown,
        .all-cat-nav-dropdown:hover {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        .all-categories-container:hover .all-cat-chevron,
        .all-categories-container.is-open .all-cat-chevron {
            transform: rotate(180deg);
        }

        /* Navigation Groups */
        .nav-item-group {
            position: relative !important;
        }
        .nav-sub-item-group {
            position: relative !important;
        }

        /* Level 1 Horizontal Bar Category Dropdown */
        .nav-dropdown {
            position: absolute !important;
            left: 0 !important;
            top: 100% !important;
            padding-top: 2px !important;
            min-width: 220px !important;
            max-width: 280px !important;
            z-index: 50 !important;
            display: none;
        }
        .nav-item-group:hover > .nav-dropdown,
        .nav-item-group.is-open > .nav-dropdown {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }
        #header-nav-items {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        #header-nav-items::-webkit-scrollbar {
            display: none;
        }

        /* Level 2 & Level 3 Flyout Submenus (Fly out to the RIGHT side) */
        .nav-sub-dropdown {
            position: absolute !important;
            left: 100% !important;
            top: 0 !important;
            margin-left: 2px !important;
            min-width: 220px !important;
            max-width: 280px !important;
            background-color: #ffffff !important;
            border: 1px solid #f3f4f6 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            z-index: 60 !important;
            display: none;
        }
        .nav-sub-item-group:hover > .nav-sub-dropdown,
        .nav-sub-dropdown:hover {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        /* Level 3 Flyout Submenu specifically */
        .nav-sub-dropdown .nav-sub-dropdown {
            z-index: 70 !important;
            min-width: 200px !important;
            border-radius: 0.5rem !important;
        }
    </style>

    {{-- Horizontal Category Navigation Bar (Responsive based on Device Width) --}}
    <div class="border-t border-gray-100 dark:border-slate-800 bg-white dark:bg-[#0f172a] relative z-20 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-center gap-2 sm:gap-4 md:gap-6 lg:gap-8 min-h-[42px]">
            @php
                $dbCategories = \App\Models\Category::cachedTree();
                if ($dbCategories->isNotEmpty()) {
                    $navMenuCategories = $dbCategories->map(function ($cat) {
                        return [
                            'name' => $cat->name,
                            'slug' => $cat->slug,
                            'children' => $cat->children->map(function ($child) {
                                return [
                                    'name' => $child->name,
                                    'slug' => $child->slug,
                                    'children' => $child->children->map(function ($grandchild) {
                                        return [
                                            'name' => $grandchild->name,
                                            'slug' => $grandchild->slug,
                                        ];
                                    })->all(),
                                ];
                            })->all(),
                        ];
                    })->all();

                    // Append Deals item (from Screenshot 1)
                    $navMenuCategories[] = [
                        'name' => 'Deals',
                        'slug' => 'deals',
                        'is_deals' => true,
                        'children' => [],
                    ];
                } else {
                    $navMenuCategories = [
                    [
                        'name' => 'Desktop',
                        'slug' => 'desktop-computers',
                        'children' => [
                            ['name' => 'Special PC', 'slug' => 'special-pc'],
                            ['name' => 'Star PC', 'slug' => 'star-pc'],
                            ['name' => 'Gaming PC', 'slug' => 'gaming-pc', 'children' => [
                                ['name' => 'Intel Gaming PC', 'slug' => 'intel-gaming-pc'],
                                ['name' => 'AMD Ryzen Gaming PC', 'slug' => 'amd-gaming-pc'],
                                ['name' => 'Budget Gaming PC', 'slug' => 'budget-gaming-pc'],
                            ]],
                            ['name' => 'Brand PC', 'slug' => 'brand-pc', 'children' => [
                                ['name' => 'HP Desktop PC', 'slug' => 'hp-pc'],
                                ['name' => 'Dell Desktop PC', 'slug' => 'dell-pc'],
                                ['name' => 'Lenovo Desktop PC', 'slug' => 'lenovo-pc'],
                                ['name' => 'Asus Desktop PC', 'slug' => 'asus-pc'],
                            ]],
                            ['name' => 'All in One PC', 'slug' => 'all-in-one-pc'],
                            ['name' => 'Portable Mini PC', 'slug' => 'portable-mini-pc'],
                            ['name' => 'Apple Mac Mini', 'slug' => 'apple-mac-mini'],
                            ['name' => 'Apple iMac', 'slug' => 'apple-imac'],
                            ['name' => 'Apple Mac Studio', 'slug' => 'apple-mac-studio'],
                        ],
                    ],
                    [
                        'name' => 'Laptop',
                        'slug' => 'laptops',
                        'children' => [
                            ['name' => 'All Laptops', 'slug' => 'all-laptops'],
                            ['name' => 'Gaming Laptop', 'slug' => 'gaming-laptop', 'children' => [
                                ['name' => 'ASUS ROG / TUF', 'slug' => 'asus-gaming-laptop'],
                                ['name' => 'Lenovo Legion / LOQ', 'slug' => 'lenovo-gaming-laptop'],
                                ['name' => 'MSI Gaming', 'slug' => 'msi-gaming-laptop'],
                                ['name' => 'Acer Predator / Nitro', 'slug' => 'acer-gaming-laptop'],
                                ['name' => 'HP Victus / OMEN', 'slug' => 'hp-gaming-laptop'],
                            ]],
                            ['name' => 'Premium Ultrabook', 'slug' => 'premium-ultrabook'],
                            ['name' => 'Business Laptop', 'slug' => 'business-laptop'],
                            ['name' => 'MacBook', 'slug' => 'macbook', 'children' => [
                                ['name' => 'MacBook Air M2 / M3', 'slug' => 'macbook-air'],
                                ['name' => 'MacBook Pro 14" / 16"', 'slug' => 'macbook-pro'],
                            ]],
                            ['name' => 'Chromebook', 'slug' => 'chromebook'],
                            ['name' => 'Laptop Accessories', 'slug' => 'laptop-accessories'],
                        ],
                    ],
                    [
                        'name' => 'Component',
                        'slug' => 'components',
                        'children' => [
                            ['name' => 'Processor', 'slug' => 'processor', 'children' => [
                                ['name' => 'Intel Processors', 'slug' => 'intel-processor'],
                                ['name' => 'AMD Ryzen Processors', 'slug' => 'amd-processor'],
                            ]],
                            ['name' => 'CPU Cooler', 'slug' => 'cpu-cooler', 'children' => [
                                ['name' => 'Air CPU Cooler', 'slug' => 'air-cpu-cooler'],
                                ['name' => 'AIO Liquid Cooler', 'slug' => 'liquid-cpu-cooler'],
                            ]],
                            ['name' => 'Motherboard', 'slug' => 'motherboard', 'children' => [
                                ['name' => 'Intel Motherboard', 'slug' => 'intel-motherboard'],
                                ['name' => 'AMD Motherboard', 'slug' => 'amd-motherboard'],
                            ]],
                            ['name' => 'Graphics Card', 'slug' => 'graphics-card', 'children' => [
                                ['name' => 'NVIDIA GeForce RTX', 'slug' => 'nvidia-graphics-card'],
                                ['name' => 'AMD Radeon RX', 'slug' => 'amd-graphics-card'],
                                ['name' => 'Intel Arc Graphics', 'slug' => 'intel-graphics-card'],
                            ]],
                            ['name' => 'RAM (Desktop)', 'slug' => 'ram-desktop', 'children' => [
                                ['name' => 'DDR4 Desktop RAM', 'slug' => 'ddr4-desktop-ram'],
                                ['name' => 'DDR5 Desktop RAM', 'slug' => 'ddr5-desktop-ram'],
                            ]],
                            ['name' => 'RAM (Laptop)', 'slug' => 'ram-laptop', 'children' => [
                                ['name' => 'DDR4 Laptop RAM', 'slug' => 'ddr4-laptop-ram'],
                                ['name' => 'DDR5 Laptop RAM', 'slug' => 'ddr5-laptop-ram'],
                            ]],
                            ['name' => 'Power Supply', 'slug' => 'power-supply', 'children' => [
                                ['name' => 'Bronze 80+ PSU', 'slug' => 'bronze-psu'],
                                ['name' => 'Gold 80+ PSU', 'slug' => 'gold-psu'],
                                ['name' => 'Fully Modular PSU', 'slug' => 'modular-psu'],
                            ]],
                            ['name' => 'Hard Disk Drive', 'slug' => 'hard-disk-drive', 'children' => [
                                ['name' => '1TB - 2TB HDD', 'slug' => '1tb-2tb-hdd'],
                                ['name' => '4TB - 8TB HDD', 'slug' => '4tb-8tb-hdd'],
                                ['name' => 'Surveillance HDD', 'slug' => 'surveillance-hdd'],
                            ]],
                            ['name' => 'Portable Hard Disk Drive', 'slug' => 'portable-hard-disk-drive'],
                            ['name' => 'SSD', 'slug' => 'ssd', 'children' => [
                                ['name' => 'M.2 NVMe PCIe SSD', 'slug' => 'm2-nvme-ssd'],
                                ['name' => '2.5" SATA III SSD', 'slug' => 'sata-ssd'],
                            ]],
                            ['name' => 'Portable SSD', 'slug' => 'portable-ssd'],
                            ['name' => 'Casing', 'slug' => 'casing', 'children' => [
                                ['name' => 'Mid Tower Case', 'slug' => 'mid-tower-casing'],
                                ['name' => 'Full Tower Case', 'slug' => 'full-tower-casing'],
                                ['name' => 'Mini ITX Case', 'slug' => 'mini-itx-casing'],
                            ]],
                            ['name' => 'Casing Cooler', 'slug' => 'casing-cooler'],
                            ['name' => 'Optical Disk Drive', 'slug' => 'optical-disk-drive'],
                            ['name' => 'Vertical GPU Holder', 'slug' => 'vertical-gpu-holder'],
                            ['name' => 'Water / Liquid Cooling', 'slug' => 'liquid-cooling'],
                        ],
                    ],
                    [
                        'name' => 'Monitor',
                        'slug' => 'monitor',
                        'children' => [
                            ['name' => 'All Monitors', 'slug' => 'all-monitors'],
                            ['name' => 'Gaming Monitor', 'slug' => 'gaming-monitor'],
                            ['name' => '4K UHD Monitor', 'slug' => '4k-monitor'],
                            ['name' => 'Curved Monitor', 'slug' => 'curved-monitor'],
                            ['name' => 'Touch Monitor', 'slug' => 'touch-monitor'],
                            ['name' => 'Portable Monitor', 'slug' => 'portable-monitor'],
                            ['name' => 'Monitor Arm & Stand', 'slug' => 'monitor-arm-stand'],
                        ],
                    ],
                    [
                        'name' => 'Power',
                        'slug' => 'power',
                        'children' => [
                            ['name' => 'UPS (Offline)', 'slug' => 'offline-ups'],
                            ['name' => 'Online UPS', 'slug' => 'online-ups'],
                            ['name' => 'IPS / Inverter', 'slug' => 'ips-inverter'],
                            ['name' => 'Voltage Stabilizer', 'slug' => 'voltage-stabilizer'],
                            ['name' => 'UPS Battery', 'slug' => 'ups-battery'],
                            ['name' => 'Power Strip', 'slug' => 'power-strip'],
                        ],
                    ],
                    [
                        'name' => 'Phone',
                        'slug' => 'smartphones',
                        'children' => [
                            ['name' => 'Apple iPhone', 'slug' => 'apple-iphone', 'children' => [
                                ['name' => 'iPhone 16 Series', 'slug' => 'iphone-16'],
                                ['name' => 'iPhone 15 Series', 'slug' => 'iphone-15'],
                                ['name' => 'iPhone 14 Series', 'slug' => 'iphone-14'],
                            ]],
                            ['name' => 'Samsung Phone', 'slug' => 'samsung-phone', 'children' => [
                                ['name' => 'Galaxy S Series', 'slug' => 'galaxy-s-series'],
                                ['name' => 'Galaxy Z Fold / Flip', 'slug' => 'galaxy-z-series'],
                                ['name' => 'Galaxy A Series', 'slug' => 'galaxy-a-series'],
                            ]],
                            ['name' => 'Google Pixel', 'slug' => 'google-pixel'],
                            ['name' => 'Xiaomi / Poco', 'slug' => 'xiaomi-poco'],
                            ['name' => 'OnePlus', 'slug' => 'oneplus'],
                            ['name' => 'Feature Phone', 'slug' => 'feature-phone'],
                            ['name' => 'Phone Accessories', 'slug' => 'phone-accessories'],
                        ],
                    ],
                    [
                        'name' => 'Tablet',
                        'slug' => 'tablets',
                        'children' => [
                            ['name' => 'Apple iPad', 'slug' => 'apple-ipad', 'children' => [
                                ['name' => 'iPad Pro M4 / M2', 'slug' => 'ipad-pro'],
                                ['name' => 'iPad Air M2', 'slug' => 'ipad-air'],
                                ['name' => 'iPad 10th Gen', 'slug' => 'ipad-10th-gen'],
                                ['name' => 'iPad mini', 'slug' => 'ipad-mini'],
                            ]],
                            ['name' => 'Samsung Galaxy Tab', 'slug' => 'samsung-galaxy-tab'],
                            ['name' => 'Graphics Tablet', 'slug' => 'graphics-tablet'],
                            ['name' => 'Android Tablet', 'slug' => 'android-tablet'],
                            ['name' => 'Tablet Accessories', 'slug' => 'tablet-accessories'],
                        ],
                    ],
                    [
                        'name' => 'Office Equipment',
                        'slug' => 'office-equipment',
                        'children' => [
                            ['name' => 'Projector', 'slug' => 'projector'],
                            ['name' => 'Printer', 'slug' => 'printer'],
                            ['name' => 'Scanner', 'slug' => 'scanner'],
                            ['name' => 'Photocopier', 'slug' => 'photocopier'],
                            ['name' => 'Barcode Scanner', 'slug' => 'barcode-scanner'],
                            ['name' => 'Cash Drawer', 'slug' => 'cash-drawer'],
                            ['name' => 'Paper Shredder', 'slug' => 'paper-shredder'],
                            ['name' => 'Conference System', 'slug' => 'conference-system'],
                        ],
                    ],
                    [
                        'name' => 'Camera',
                        'slug' => 'cameras',
                        'children' => [
                            ['name' => 'DSLR Camera', 'slug' => 'dslr-camera'],
                            ['name' => 'Mirrorless Camera', 'slug' => 'mirrorless-camera'],
                            ['name' => 'Action Camera', 'slug' => 'action-camera'],
                            ['name' => 'Security / IP Camera', 'slug' => 'camera-security'],
                            ['name' => 'Camera Lens', 'slug' => 'camera-lens'],
                            ['name' => 'Tripod & Monopod', 'slug' => 'tripod-monopod'],
                            ['name' => 'Gimbal', 'slug' => 'camera-gimbal'],
                        ],
                    ],
                    [
                        'name' => 'Security',
                        'slug' => 'security',
                        'children' => [
                            ['name' => 'CCTV Camera', 'slug' => 'cctv-camera'],
                            ['name' => 'IP Camera', 'slug' => 'ip-camera'],
                            ['name' => 'DVR / NVR', 'slug' => 'dvr-nvr'],
                            ['name' => 'Access Control', 'slug' => 'access-control'],
                            ['name' => 'Smart Door Lock', 'slug' => 'smart-door-lock'],
                            ['name' => 'Video Doorbell', 'slug' => 'video-doorbell'],
                        ],
                    ],
                    [
                        'name' => 'Networking',
                        'slug' => 'networking',
                        'children' => [
                            ['name' => 'Router', 'slug' => 'router'],
                            ['name' => 'Mesh Router', 'slug' => 'mesh-router'],
                            ['name' => 'Switch', 'slug' => 'network-switch'],
                            ['name' => 'Access Point', 'slug' => 'access-point'],
                            ['name' => 'Network Adapter', 'slug' => 'network-adapter'],
                            ['name' => 'Patch Cord', 'slug' => 'patch-cord'],
                            ['name' => 'Server Rack', 'slug' => 'server-rack-networking'],
                        ],
                    ],
                    [
                        'name' => 'Software',
                        'slug' => 'software',
                        'children' => [
                            ['name' => 'Operating System', 'slug' => 'operating-system'],
                            ['name' => 'Office Application', 'slug' => 'office-application'],
                            ['name' => 'Antivirus & Security', 'slug' => 'antivirus-security'],
                            ['name' => 'Graphic & Design Software', 'slug' => 'graphic-software'],
                        ],
                    ],
                    [
                        'name' => 'Server & Storage',
                        'slug' => 'server-storage',
                        'children' => [
                            ['name' => 'Server', 'slug' => 'server'],
                            ['name' => 'Server Rack', 'slug' => 'server-rack'],
                            ['name' => 'NAS Storage', 'slug' => 'nas-storage'],
                            ['name' => 'SAN Storage', 'slug' => 'san-storage'],
                            ['name' => 'Server HDD', 'slug' => 'server-hdd'],
                            ['name' => 'Server RAM', 'slug' => 'server-ram'],
                        ],
                    ],
                    [
                        'name' => 'Accessories',
                        'slug' => 'accessories',
                        'children' => [
                            ['name' => 'Keyboard', 'slug' => 'keyboard'],
                            ['name' => 'Mouse', 'slug' => 'mouse'],
                            ['name' => 'Mouse Pad', 'slug' => 'mouse-pad'],
                            ['name' => 'Headphone / Headset', 'slug' => 'headphones'],
                            ['name' => 'Earphone', 'slug' => 'earphones'],
                            ['name' => 'Speaker', 'slug' => 'speakers'],
                            ['name' => 'Web Camera', 'slug' => 'webcam'],
                            ['name' => 'External Hard Drive', 'slug' => 'external-hdd'],
                            ['name' => 'USB Flash Drive', 'slug' => 'usb-pen-drive'],
                            ['name' => 'Power Bank', 'slug' => 'power-bank'],
                            ['name' => 'Cable & Converter', 'slug' => 'cables-converters'],
                        ],
                    ],
                    [
                        'name' => 'Gadget',
                        'slug' => 'gadgets',
                        'children' => [
                            ['name' => 'Smart Watch', 'slug' => 'smartwatches'],
                            ['name' => 'Smart Band', 'slug' => 'smart-band'],
                            ['name' => 'Earbuds (TWS)', 'slug' => 'tws-earbuds'],
                            ['name' => 'Bluetooth Speaker', 'slug' => 'bluetooth-speaker'],
                            ['name' => 'Drone', 'slug' => 'drones'],
                            ['name' => 'Gimbal & Stabilizer', 'slug' => 'gimbal-stabilizer'],
                            ['name' => 'Microphone', 'slug' => 'microphone'],
                        ],
                    ],
                    [
                        'name' => 'Gaming',
                        'slug' => 'gaming-consoles',
                        'children' => [
                            ['name' => 'Gaming Chair', 'slug' => 'gaming-chair'],
                            ['name' => 'Gaming Desk', 'slug' => 'gaming-desk'],
                            ['name' => 'Gaming Headset', 'slug' => 'gaming-headset'],
                            ['name' => 'Gaming Mouse', 'slug' => 'gaming-mouse'],
                            ['name' => 'Gaming Keyboard', 'slug' => 'gaming-keyboard'],
                            ['name' => 'Game Controller', 'slug' => 'game-controller'],
                            ['name' => 'Gaming Console', 'slug' => 'gaming-console'],
                            ['name' => 'Gaming Monitor', 'slug' => 'gaming-monitor-gaming'],
                        ],
                    ],
                    [
                        'name' => 'TV',
                        'slug' => 'television',
                        'children' => [
                            ['name' => 'Smart TV', 'slug' => 'smart-tv'],
                            ['name' => '4K UHD TV', 'slug' => '4k-uhd-tv'],
                            ['name' => 'OLED TV', 'slug' => 'oled-tv'],
                            ['name' => 'Android TV', 'slug' => 'android-tv'],
                            ['name' => 'TV Wall Mount', 'slug' => 'tv-wall-mount'],
                            ['name' => 'TV Box & Stick', 'slug' => 'tv-box-stick'],
                        ],
                    ],
                    [
                        'name' => 'Appliance',
                        'slug' => 'appliance',
                        'children' => [
                            ['name' => 'Air Conditioner', 'slug' => 'air-conditioner'],
                            ['name' => 'Refrigerator', 'slug' => 'refrigerator'],
                            ['name' => 'Washing Machine', 'slug' => 'washing-machine'],
                            ['name' => 'Microwave Oven', 'slug' => 'microwave-oven'],
                            ['name' => 'Air Purifier', 'slug' => 'air-purifier'],
                            ['name' => 'Vacuum Cleaner', 'slug' => 'vacuum-cleaner'],
                            ['name' => 'Water Purifier', 'slug' => 'water-purifier'],
                        ],
                    ],
                ];
                }
            @endphp            {{-- All Categories Dropdown Container (Left Fixed) --}}
            <div id="all-categories-container" class="relative all-categories-container flex-shrink-0 z-40">
                <button type="button" id="all-categories-btn" class="relative py-2.5 px-2 sm:px-2.5 inline-flex items-center gap-1.5 text-[12.5px] sm:text-[13px] font-bold text-gray-900 hover:text-black bg-transparent transition-colors whitespace-nowrap group cursor-pointer select-none">
                    <svg class="w-4 h-4 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>All Categories</span>
                    <svg class="w-3 h-3 text-gray-900 all-cat-chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <span class="absolute bottom-0 left-0 w-full h-[2.5px] bg-transparent group-hover:bg-gray-900 transition-all duration-150"></span>
                </button>

                {{-- All Categories Multi-Level Flyout Dropdown Menu --}}
                <div id="all-categories-dropdown" class="all-cat-nav-dropdown absolute left-0 top-full pt-0.5 hidden z-50 min-w-[250px] max-w-[290px]">
                    <div class="bg-white dark:bg-[#111827] rounded-b-xl shadow-2xl border border-gray-100 dark:border-slate-800 text-[13px] overflow-hidden flex flex-col">
                        {{-- Scrollable Category Items List --}}
                        <div class="all-cat-scroll py-1.5" style="max-height: 400px; max-height: min(420px, calc(100vh - 160px)); overflow-y: auto !important; overflow-x: hidden !important; overscroll-behavior: contain;">
                            @foreach ($navMenuCategories as $allCatItem)
                                @php
                                    $hasCatChildren = !empty($allCatItem['children']);
                                @endphp
                                <div class="relative nav-sub-item-group group/allsub">
                                    <a href="{{ Route::has('shop') ? route('shop', ['category' => $allCatItem['slug']]) : '#' }}" class="flex items-center justify-between px-4 py-2 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors">
                                        <span>{{ $allCatItem['name'] }}</span>
                                        @if ($hasCatChildren)
                                            <svg class="w-3 h-3 text-gray-400 group-hover/allsub:text-gray-950 group-hover/allsub:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    </a>

                                    {{-- Level 2 Flyout Submenu for Category --}}
                                    @if ($hasCatChildren)
                                        <div class="nav-sub-dropdown absolute left-full top-0 ml-0.5 min-w-[220px] max-w-[270px] bg-white border border-gray-100 shadow-2xl rounded-xl py-1.5 hidden z-[60]">
                                            @foreach ($allCatItem['children'] as $child)
                                                @php
                                                    $hasGrandchildren = !empty($child['children']);
                                                @endphp
                                                <div class="relative nav-sub-item-group group/grandsub">
                                                    <a href="{{ Route::has('shop') ? route('shop', ['category' => $child['slug'] ?? \Illuminate\Support\Str::slug($child['name'])]) : '#' }}" class="flex items-center justify-between px-4 py-1.5 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors text-[12.5px]">
                                                        <span>{{ $child['name'] }}</span>
                                                        @if ($hasGrandchildren)
                                                            <svg class="w-3 h-3 text-gray-400 group-hover/grandsub:text-gray-950 group-hover/grandsub:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        @endif
                                                    </a>

                                                    {{-- Level 3 Flyout Submenu --}}
                                                    @if ($hasGrandchildren)
                                                        <div class="nav-sub-dropdown absolute left-full top-0 ml-0.5 min-w-[190px] bg-white border border-gray-100 shadow-2xl rounded-lg py-1.5 hidden z-[70]">
                                                            @foreach ($child['children'] as $grandchild)
                                                                <a href="{{ Route::has('shop') ? route('shop', ['category' => $grandchild['slug'] ?? \Illuminate\Support\Str::slug($grandchild['name'])]) : '#' }}" class="block px-4 py-1.5 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors text-[12px]">
                                                                    {{ $grandchild['name'] }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach

                                            <div class="border-t border-gray-100 mt-1 pt-1">
                                                <a href="{{ Route::has('shop') ? route('shop', ['category' => $allCatItem['slug']]) : '#' }}" class="flex items-center justify-between px-4 py-1.5 text-gray-950 hover:text-black hover:bg-gray-100/70 font-semibold transition-colors text-xs">
                                                    <span>Show All {{ $allCatItem['name'] }}</span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            {{-- Product Condition Navigation Bar (Dynamically populated from App\Enums\ProductCondition) --}}
            @php
                $conditionCases = \App\Enums\ProductCondition::cases();
                // Preferred display order: New, Refurbished, Used, Openbox
                $preferredOrder = ['new', 'refurbished', 'used', 'openbox'];
                usort($conditionCases, function ($a, $b) use ($preferredOrder) {
                    $posA = array_search($a->value, $preferredOrder);
                    $posB = array_search($b->value, $preferredOrder);
                    return ($posA === false ? 99 : $posA) <=> ($posB === false ? 99 : $posB);
                });

                $horizontalNavItems = \Illuminate\Support\Facades\Cache::remember('header_nav_condition_items_v5', 180, function () use ($conditionCases) {
                    $rows = \Illuminate\Support\Facades\DB::table('categories')
                        ->join('products', 'categories.id', '=', 'products.category_id')
                        ->where('products.publication_status', \App\Enums\ProductPublicationStatus::Published->value)
                        ->select('categories.id', 'categories.name', 'categories.slug', 'products.condition', \Illuminate\Support\Facades\DB::raw('count(products.id) as product_count'))
                        ->groupBy('categories.id', 'categories.name', 'categories.slug', 'products.condition')
                        ->orderByDesc('product_count')
                        ->get();

                    $grouped = [];
                    $totalCounts = [];
                    foreach ($rows as $row) {
                        $grouped[$row->condition][] = [
                            'name' => $row->name,
                            'slug' => $row->slug,
                            'count' => (int) $row->product_count,
                        ];
                        $totalCounts[$row->condition] = ($totalCounts[$row->condition] ?? 0) + (int) $row->product_count;
                    }

                    $items = [];
                    foreach ($conditionCases as $case) {
                        $items[] = [
                            'name' => $case->label(),
                            'value' => $case->value,
                            'slug' => $case->value,
                            'is_deals' => false,
                            'total_count' => $totalCounts[$case->value] ?? 0,
                            'categories' => $grouped[$case->value] ?? [],
                        ];
                    }

                    // Append Deals at the end
                    $items[] = [
                        'name' => 'Deals',
                        'value' => 'deals',
                        'slug' => 'deals',
                        'is_deals' => true,
                        'total_count' => null,
                        'categories' => [],
                    ];

                    return $items;
                });
            @endphp
            <nav id="header-main-nav" class="flex items-center py-0 min-w-0">
                <div id="header-nav-items" class="flex items-center gap-2 sm:gap-4 md:gap-6 lg:gap-8 min-w-0">
                    @foreach ($horizontalNavItems as $index => $navItem)
                        @php
                            $isDeals = !empty($navItem['is_deals']);
                            $isActive = $isDeals ? (request()->boolean('deal') || request()->boolean('deals')) : (request('condition') === $navItem['value']);
                            $url = $isDeals 
                                ? (Route::has('shop') ? route('shop', ['deal' => 1]) : '#')
                                : (Route::has('shop') ? route('shop', ['condition' => $navItem['value']]) : '#');
                        @endphp
                        <div class="relative nav-item-group flex-shrink-0 js-nav-category-item" data-index="{{ $index }}">
                            {{-- Top Menu Item --}}
                            @if ($isDeals)
                                <a href="{{ $url }}" class="relative py-2.5 px-3 sm:px-3.5 inline-flex items-center gap-1.5 text-[13px] sm:text-[13.5px] font-bold text-red-600 hover:text-red-700 transition-colors whitespace-nowrap group">
                                    <svg class="w-3.5 h-3.5 text-red-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" />
                                        <line x1="7" y1="7" x2="7.01" y2="7" />
                                    </svg>
                                    <span>Deals</span>
                                    <span class="absolute bottom-0 left-0 w-full h-[2.5px] {{ $isActive ? 'bg-red-600' : 'bg-transparent group-hover:bg-red-600' }} transition-all duration-150"></span>
                                </a>
                            @else
                                <a href="{{ $url }}" class="relative py-2.5 px-3 sm:px-3.5 inline-flex items-center text-[13px] sm:text-[13.5px] font-semibold {{ $isActive ? 'text-gray-950 dark:text-white font-bold' : 'text-gray-800 dark:text-gray-200 hover:text-gray-950 dark:hover:text-white' }} transition-colors whitespace-nowrap group">
                                    <span>{{ $navItem['name'] }}</span>
                                    <span class="absolute bottom-0 left-0 w-full h-[2.5px] {{ $isActive ? 'bg-gray-900 dark:bg-brand-400' : 'bg-transparent group-hover:bg-gray-900 dark:group-hover:bg-brand-400' }} transition-all duration-150"></span>
                                </a>
                            @endif

                            {{-- Dropdown Menu on Hover with Categories for this Condition --}}
                            @if (!empty($navItem['categories']))
                                <div class="nav-dropdown absolute left-1/2 -translate-x-1/2 top-full pt-0.5 hidden z-50 min-w-[220px] max-w-[280px]">
                                    <div class="bg-white dark:bg-[#111827] rounded-b-lg shadow-xl border border-gray-100 dark:border-slate-800 py-1.5 text-[13px]">
                                        <div class="px-4 py-1 text-[10.5px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 border-b border-gray-50 dark:border-slate-800/80 mb-1">
                                            {{ $navItem['name'] }} Categories
                                        </div>
                                        @foreach (array_slice($navItem['categories'], 0, 10) as $cat)
                                            <a href="{{ Route::has('shop') ? route('shop', ['condition' => $navItem['value'], 'category' => $cat['slug']]) : '#' }}" class="flex items-center justify-between px-4 py-1.5 text-gray-700 dark:text-gray-300 hover:text-gray-950 dark:hover:text-white hover:bg-gray-100/70 dark:hover:bg-gray-800 font-medium transition-colors">
                                                <span>{{ $cat['name'] }}</span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500 font-normal">({{ $cat['count'] }})</span>
                                            </a>
                                        @endforeach

                                        {{-- View All Condition Products Link --}}
                                        <div class="border-t border-gray-100 dark:border-slate-800 mt-1 pt-1">
                                            <a href="{{ $url }}" class="flex items-center justify-between px-4 py-2 text-brand-600 dark:text-brand-400 hover:text-brand-700 dark:hover:text-brand-300 hover:bg-brand-50/50 dark:hover:bg-gray-800 font-semibold transition-colors">
                                                <span>View All {{ $navItem['name'] }} ({{ $navItem['total_count'] }})</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </nav>

        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allCatContainer = document.getElementById('all-categories-container');
            const allCatBtn = document.getElementById('all-categories-btn');

            if (allCatContainer && allCatBtn) {
                let hideTimeout;
                const showMenu = () => {
                    clearTimeout(hideTimeout);
                    allCatContainer.classList.add('is-open');
                };
                const hideMenu = () => {
                    hideTimeout = setTimeout(() => {
                        allCatContainer.classList.remove('is-open');
                    }, 150);
                };

                allCatContainer.addEventListener('mouseenter', showMenu);
                allCatContainer.addEventListener('mouseleave', hideMenu);

                allCatBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    allCatContainer.classList.toggle('is-open');
                });

                document.addEventListener('click', (e) => {
                    if (!allCatContainer.contains(e.target)) {
                        allCatContainer.classList.remove('is-open');
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        allCatContainer.classList.remove('is-open');
                    }
                });

                // Position flyout submenus cleanly outside scrollable container
                const allCatDropdown = document.getElementById('all-categories-dropdown');
                if (allCatDropdown) {
                    const subItemGroups = allCatDropdown.querySelectorAll('.group\\/allsub');
                    subItemGroups.forEach(group => {
                        const flyout = group.querySelector('.nav-sub-dropdown');
                        if (!flyout) return;

                        group.addEventListener('mouseenter', () => {
                            const rect = group.getBoundingClientRect();
                            flyout.style.position = 'fixed';
                            flyout.style.left = (rect.right + 2) + 'px';
                            const flyoutHeight = flyout.offsetHeight || 220;
                            const maxTop = window.innerHeight - flyoutHeight - 16;
                            flyout.style.top = Math.max(16, Math.min(rect.top, maxTop)) + 'px';
                            flyout.style.display = 'block';
                        });

                        group.addEventListener('mouseleave', () => {
                            flyout.style.display = 'none';
                        });
                    });

                    const scrollWrap = allCatDropdown.querySelector('.all-cat-scroll');
                    if (scrollWrap) {
                        scrollWrap.addEventListener('scroll', () => {
                            allCatDropdown.querySelectorAll('.nav-sub-dropdown').forEach(flyout => {
                                flyout.style.display = 'none';
                            });
                        }, { passive: true });
                    }
                }
            }

            // Dynamically show only categories that fit 100% completely without cutting off words
            const navContainer = document.getElementById('header-main-nav');
            const navItemsContainer = document.getElementById('header-nav-items');
            const topItems = Array.from(document.querySelectorAll('.js-nav-category-item'));

            if (navContainer && navItemsContainer && topItems.length > 0) {
                let cachedWidths = [];

                const measure = () => {
                    topItems.forEach(item => {
                        item.style.display = 'inline-flex';
                    });
                    cachedWidths = topItems.map(item => item.getBoundingClientRect().width);
                };

                const updateFit = () => {
                    if (cachedWidths.length === 0) measure();
                    const availableWidth = navContainer.clientWidth;
                    if (availableWidth <= 0) return;

                    let accum = 0;
                    const gap = 4; // gap between category items
                    let visibleCount = 0;

                    for (let i = 0; i < cachedWidths.length; i++) {
                        const itemW = cachedWidths[i] + (i > 0 ? gap : 0);
                        if (accum + itemW <= availableWidth) {
                            accum += itemW;
                            visibleCount++;
                        } else {
                            break;
                        }
                    }

                    // Display only categories that fit completely; hide any that would be cut off
                    topItems.forEach((item, idx) => {
                        item.style.display = (idx < visibleCount) ? 'inline-flex' : 'none';
                    });
                };

                measure();
                updateFit();

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(() => {
                        measure();
                        updateFit();
                    });
                }

                window.addEventListener('resize', () => {
                    measure();
                    updateFit();
                });

                if (window.ResizeObserver) {
                    const ro = new ResizeObserver(() => {
                        updateFit();
                    });
                    ro.observe(navContainer);
                }
            }

            // Global Cart Badge Manager & Add to Cart Click Listener
            window.updateCartBadge = function(count) {
                const badges = document.querySelectorAll('.js-cart-badge');
                badges.forEach(badge => {
                    badge.textContent = count;
                    if (parseInt(count) > 0) {
                        badge.classList.remove('hidden');
                        badge.classList.add('scale-125', 'ring-2', 'ring-red-400');
                        setTimeout(() => {
                            badge.classList.remove('scale-125', 'ring-2', 'ring-red-400');
                        }, 300);
                    } else {
                        badge.classList.add('hidden');
                    }
                });
            };

            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-add-to-cart');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                const productId = btn.dataset.productId;
                const btnText = btn.querySelector('.js-btn-text');
                const originalText = btnText ? btnText.textContent : 'Add';

                btn.disabled = true;
                if (btnText) btnText.textContent = 'Adding...';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (productId && productId !== '' && productId !== 'null') {
                    fetch('/cart/' + productId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ quantity: 1 })
                    })
                    .then(res => res.json().then(data => ({ ok: res.ok, data })))
                    .then(({ ok, data }) => {
                        if (!ok || data.success === false) {
                            if (window.showToast) window.showToast(data.message || 'Could not add the item to your cart.', 'error');
                            btn.disabled = false;
                            if (btnText) btnText.textContent = originalText;
                            return;
                        }
                        if (data.cart_count !== undefined) {
                            window.updateCartBadge(data.cart_count);
                        } else {
                            const badge = document.querySelector('.js-cart-badge');
                            const current = badge && !badge.classList.contains('hidden') ? parseInt(badge.textContent) || 0 : 0;
                            window.updateCartBadge(current + 1);
                        }
                        if (window.showToast) window.showToast(data.message || 'Added to your cart.', 'success');
                        if (btnText) btnText.textContent = 'Added!';
                        btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                        setTimeout(() => {
                            btn.disabled = false;
                            if (btnText) btnText.textContent = originalText;
                            btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                        }, 1200);
                    })
                    .catch(err => {
                        console.error('Cart addition error:', err);
                        if (window.showToast) window.showToast('Could not add the item to your cart. Please try again.', 'error');
                        btn.disabled = false;
                        if (btnText) btnText.textContent = originalText;
                    });
                } else {
                    // Demo item without DB ID
                    const badge = document.querySelector('.js-cart-badge');
                    const current = badge && !badge.classList.contains('hidden') ? parseInt(badge.textContent) || 0 : 0;
                    window.updateCartBadge(current + 1);
                    if (btnText) btnText.textContent = 'Added!';
                    btn.classList.add('bg-emerald-600', 'text-white', 'border-emerald-600');
                    setTimeout(() => {
                        btn.disabled = false;
                        if (btnText) btnText.textContent = originalText;
                        btn.classList.remove('bg-emerald-600', 'text-white', 'border-emerald-600');
                    }, 1200);
                }
            });

            // Wishlist (heart) toggle on product cards
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-wishlist-btn');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                const icon = btn.querySelector('.js-wishlist-icon');
                const productId = btn.dataset.wishlistId;
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const setLiked = (liked) => {
                    icon?.classList.toggle('fill-current', liked);
                    icon?.classList.toggle('text-red-500', liked);
                    btn.classList.toggle('text-red-500', liked);
                };

                if (!productId || productId === '' || productId === 'null') {
                    setLiked(!icon?.classList.contains('fill-current'));
                    return;
                }

                btn.disabled = true;

                fetch('/wishlist/' + productId + '/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (res.status === 401) {
                        window.location.href = '{{ route('login') }}';
                        return null;
                    }
                    return res.json();
                })
                .then(data => {
                    if (!data) return;
                    setLiked(!!data.added);
                })
                .catch(() => {})
                .finally(() => {
                    btn.disabled = false;
                });
            });

            // Share button on product cards
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.js-share-btn');
                if (!btn) return;

                e.preventDefault();
                e.stopPropagation();

                const url = btn.dataset.shareUrl || window.location.href;
                const title = btn.dataset.shareTitle || document.title;

                if (navigator.share) {
                    navigator.share({ title, url }).catch(() => {});
                    return;
                }

                navigator.clipboard?.writeText(url).then(() => {
                    const original = btn.innerHTML;
                    btn.innerHTML = '<svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
                    setTimeout(() => { btn.innerHTML = original; }, 1200);
                }).catch(() => {});
            });
        });
    </script>
</header>
