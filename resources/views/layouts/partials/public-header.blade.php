<header class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-xs">
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

        {{-- Center: Search Bar --}}
        <div class="flex-1 max-w-xl lg:max-w-2xl mx-1 sm:mx-4">
            <form action="{{ Route::has('search') ? route('search') : '#' }}" method="GET" class="w-full">
                <div class="flex items-center w-full h-10 sm:h-11 bg-[#f8fafc] hover:bg-gray-100/80 focus-within:bg-white border border-gray-200 hover:border-gray-300 focus-within:border-gray-900 focus-within:ring-2 focus-within:ring-gray-900/20 rounded-lg transition-all shadow-2xs px-3 sm:px-4">
                    {{-- Search Icon --}}
                    <div class="flex items-center justify-center text-gray-400 flex-shrink-0 mr-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    {{-- Search Input --}}
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search electronics..."
                        class="w-full h-full bg-transparent border-0 p-0 text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 leading-normal"
                    >
                </div>
            </form>
        </div>

        {{-- Right: Actions & Start Selling --}}
        <div class="flex items-center gap-2.5 sm:gap-4 lg:gap-5 flex-shrink-0">
            {{-- Compare Icon --}}
            <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-gray-600 hover:text-gray-950 transition-colors p-1 hidden sm:inline-block" title="Compare">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
            </a>

            {{-- Wishlist --}}
            <a href="{{ Route::has('wishlist') ? route('wishlist') : '#' }}" class="text-gray-600 hover:text-gray-950 transition-colors p-1" title="Wishlist">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            </a>

            {{-- Cart --}}
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
            <a href="{{ Route::has('cart') ? route('cart') : '#' }}" class="relative text-gray-600 hover:text-gray-950 transition-colors p-1 group" title="Cart">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                <span id="header-cart-badge" class="js-cart-badge absolute -top-1.5 -right-2 bg-red-600 text-white text-[10px] font-extrabold h-4 min-w-[16px] px-1 rounded-full flex items-center justify-center shadow-xs transition-all transform scale-100 {{ $headerCartCount > 0 ? '' : 'hidden' }}">
                    {{ $headerCartCount }}
                </span>
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
            <a href="{{ route('register', ['type' => 'saler']) }}" class="bg-brand-500 hover:bg-brand-600 text-white text-xs sm:text-sm font-bold px-3 sm:px-6 py-2 sm:py-2.5 rounded-lg shadow-xs hover:shadow-sm transition-all flex items-center gap-1.5 flex-shrink-0">
                <span class="whitespace-nowrap">Start Selling</span>
            </a>
        </div>
    </div>

    <style>
        /* Globally Hide Scrollbars */
        html, body, *, ::-webkit-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
        }
        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
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
        #nav-more-dropdown:hover .more-chevron,
        #nav-more-dropdown.is-open .more-chevron {
            transform: rotate(180deg);
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
    <div class="border-t border-gray-100 bg-white relative z-20 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
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
            <div id="all-categories-container" class="relative all-categories-container flex-shrink-0 mr-1 sm:mr-2 z-40">
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
                <div id="all-categories-dropdown" class="all-cat-nav-dropdown absolute left-0 top-full pt-0.5 hidden z-50 min-w-[240px]">
                    <div class="bg-white rounded-b-xl shadow-2xl border border-gray-100 py-1.5 text-[13px]">
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

                        <div class="border-t border-gray-100 mt-1.5 pt-1.5 px-3">
                            <a href="{{ route('categories.index') }}" class="flex items-center justify-center gap-1.5 w-full py-2 bg-gray-900 hover:bg-black text-white font-bold rounded-lg text-xs transition-colors shadow-2xs">
                                <span>View Category Directory</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Categories Navigation Bar (Adapts based on device width) --}}
            <nav id="header-main-nav" class="flex items-center flex-1 py-0 justify-start sm:justify-start min-w-0 overflow-hidden">
                <div id="header-nav-items" class="flex items-center gap-0.5 sm:gap-1 min-w-0 w-full overflow-hidden">
                    @foreach ($navMenuCategories as $index => $navCat)
                        <div class="relative nav-item-group flex-shrink-0 js-nav-category-item" data-index="{{ $index }}">
                            {{-- Top Menu Item --}}
                            <a href="{{ Route::has('shop') ? route('shop', ['category' => $navCat['slug']]) : '#' }}" class="relative py-2.5 px-2 sm:px-2.5 lg:px-3 inline-flex items-center text-[12.5px] sm:text-[13px] font-semibold text-gray-800 hover:text-gray-950 transition-colors whitespace-nowrap group">
                                <span>{{ $navCat['name'] }}</span>
                                <span class="absolute bottom-0 left-0 w-full h-[2.5px] bg-transparent group-hover:bg-gray-900 transition-all duration-150"></span>
                            </a>

                            {{-- Dropdown Menu on Hover --}}
                            @if (!empty($navCat['children']))
                                <div class="nav-dropdown absolute left-0 top-full pt-0.5 hidden z-50 min-w-[220px] max-w-[280px]">
                                    <div class="bg-white rounded-b-lg shadow-xl border border-gray-100 py-1.5 text-[13px]">
                                        @foreach ($navCat['children'] as $child)
                                            @php
                                                $hasGrandchildren = !empty($child['children']);
                                            @endphp
                                            <div class="relative nav-sub-item-group group/sub">
                                                <a href="{{ Route::has('shop') ? route('shop', ['category' => $child['slug'] ?? \Illuminate\Support\Str::slug($child['name'])]) : '#' }}" class="flex items-center justify-between px-4 py-2 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors">
                                                    <span>{{ $child['name'] }}</span>
                                                    @if ($hasGrandchildren)
                                                        <svg class="w-3 h-3 text-gray-400 group-hover/sub:text-gray-950 group-hover/sub:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    @endif
                                                </a>

                                                {{-- 2nd Level Flyout Submenu --}}
                                                @if ($hasGrandchildren)
                                                    <div class="nav-sub-dropdown absolute left-full top-0 ml-0.5 min-w-[200px] bg-white border border-gray-100 shadow-xl rounded-lg py-1.5 hidden z-50">
                                                        @foreach ($child['children'] as $grandchild)
                                                            <a href="{{ Route::has('shop') ? route('shop', ['category' => $grandchild['slug'] ?? \Illuminate\Support\Str::slug($grandchild['name'])]) : '#' }}" class="block px-4 py-1.5 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors">
                                                                {{ $grandchild['name'] }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach

                                        {{-- Show All Link --}}
                                        <div class="border-t border-gray-100 mt-1 pt-1">
                                            <a href="{{ Route::has('shop') ? route('shop', ['category' => $navCat['slug']]) : '#' }}" class="flex items-center justify-between px-4 py-2 text-gray-950 hover:text-black hover:bg-gray-100/70 font-semibold transition-colors">
                                                <span>Show All {{ $navCat['name'] }}</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- More Dropdown --}}
                    <div id="nav-more-dropdown" class="relative nav-item-group flex-shrink-0 hidden">
                        <button type="button" id="nav-more-btn" class="relative py-2.5 px-2 sm:px-2.5 inline-flex items-center gap-1 text-[12.5px] sm:text-[13px] font-bold text-gray-900 hover:text-black bg-transparent transition-colors whitespace-nowrap group cursor-pointer select-none" aria-label="More categories">
                            <span class="text-sm font-extrabold tracking-wider">...</span>
                            <svg class="w-3 h-3 text-gray-900 more-chevron transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            <span class="absolute bottom-0 left-0 w-full h-[2.5px] bg-transparent group-hover:bg-gray-900 transition-all duration-150"></span>
                        </button>

                        <div id="nav-more-menu" class="nav-dropdown absolute right-0 sm:left-auto top-full pt-0.5 hidden z-50 min-w-[220px]">
                            <div class="bg-white rounded-b-lg shadow-xl border border-gray-100 py-1.5 text-[13px]">
                                @foreach ($navMenuCategories as $index => $navCat)
                                    <div class="relative nav-sub-item-group group/sub js-more-category-item" data-index="{{ $index }}">
                                        <a href="{{ Route::has('shop') ? route('shop', ['category' => $navCat['slug']]) : '#' }}" class="flex items-center justify-between px-4 py-2 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors">
                                            <span>{{ $navCat['name'] }}</span>
                                            @if (!empty($navCat['children']))
                                                <svg class="w-3 h-3 text-gray-400 group-hover/sub:text-gray-950 group-hover/sub:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            @endif
                                        </a>

                                        @if (!empty($navCat['children']))
                                            <div class="nav-sub-dropdown absolute right-full top-0 mr-0.5 min-w-[200px] bg-white border border-gray-100 shadow-xl rounded-lg py-1.5 hidden z-50">
                                                @foreach ($navCat['children'] as $child)
                                                    <a href="{{ Route::has('shop') ? route('shop', ['category' => $child['slug'] ?? \Illuminate\Support\Str::slug($child['name'])]) : '#' }}" class="block px-4 py-1.5 text-gray-700 hover:text-gray-950 hover:bg-gray-100/70 font-medium transition-colors">
                                                        {{ $child['name'] }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
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
            }

            // Dynamic Responsive Navigation Bar Category Overflow (Priority-Plus Menu)
            const navContainer = document.getElementById('header-main-nav');
            const navItemsContainer = document.getElementById('header-nav-items');
            const topItems = Array.from(document.querySelectorAll('.js-nav-category-item'));
            const moreDropdown = document.getElementById('nav-more-dropdown');
            const moreItems = Array.from(document.querySelectorAll('.js-more-category-item'));
            const moreBtn = document.getElementById('nav-more-btn');

            if (navContainer && navItemsContainer && topItems.length > 0 && moreDropdown) {
                // More Dropdown Hover & Click toggle
                if (moreDropdown) {
                    let moreTimeout;
                    moreDropdown.addEventListener('mouseenter', () => {
                        clearTimeout(moreTimeout);
                        moreDropdown.classList.add('is-open');
                    });

                    moreDropdown.addEventListener('mouseleave', () => {
                        moreTimeout = setTimeout(() => {
                            moreDropdown.classList.remove('is-open');
                        }, 150);
                    });

                    if (moreBtn) {
                        moreBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            moreDropdown.classList.toggle('is-open');
                        });
                    }

                    document.addEventListener('click', (e) => {
                        if (!moreDropdown.contains(e.target)) {
                            moreDropdown.classList.remove('is-open');
                        }
                    });

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            moreDropdown.classList.remove('is-open');
                        }
                    });
                }

                let cachedItemWidths = [];
                let cachedMoreWidth = 0;

                const measureWidths = () => {
                    moreDropdown.style.display = 'inline-flex';
                    moreDropdown.classList.remove('hidden');
                    topItems.forEach(item => {
                        item.style.display = 'inline-flex';
                        item.classList.remove('hidden');
                    });

                    cachedMoreWidth = moreDropdown.offsetWidth || 80;
                    cachedItemWidths = topItems.map(item => item.offsetWidth || 100);
                };

                const updateNavOverflow = () => {
                    if (cachedItemWidths.length === 0) {
                        measureWidths();
                    }

                    const availableWidth = navContainer.clientWidth;
                    if (availableWidth <= 0) return;

                    let totalWidthAll = cachedItemWidths.reduce((sum, w) => sum + w, 0);

                    let visibleCount = 0;
                    if (totalWidthAll <= availableWidth) {
                        visibleCount = cachedItemWidths.length;
                    } else {
                        const targetWidth = availableWidth - cachedMoreWidth;
                        let accumWidth = 0;
                        for (let i = 0; i < cachedItemWidths.length; i++) {
                            if (accumWidth + cachedItemWidths[i] <= targetWidth) {
                                accumWidth += cachedItemWidths[i];
                                visibleCount++;
                            } else {
                                break;
                            }
                        }
                    }

                    // Apply display states
                    topItems.forEach((item, idx) => {
                        if (idx < visibleCount) {
                            item.style.display = 'inline-flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    moreItems.forEach((item, idx) => {
                        if (idx >= visibleCount) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    if (visibleCount < topItems.length) {
                        moreDropdown.style.display = 'inline-flex';
                        moreDropdown.classList.remove('hidden');
                    } else {
                        moreDropdown.style.display = 'none';
                        moreDropdown.classList.add('hidden');
                        moreDropdown.classList.remove('is-open');
                    }
                };

                // Run measurement and calculation
                measureWidths();
                updateNavOverflow();

                // Recalculate on window resize & ResizeObserver
                window.addEventListener('resize', () => {
                    measureWidths();
                    updateNavOverflow();
                });

                if (window.ResizeObserver) {
                    const ro = new ResizeObserver(() => {
                        updateNavOverflow();
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
                    .then(res => res.json())
                    .then(data => {
                        if (data.cart_count !== undefined) {
                            window.updateCartBadge(data.cart_count);
                        } else {
                            const badge = document.querySelector('.js-cart-badge');
                            const current = badge && !badge.classList.contains('hidden') ? parseInt(badge.textContent) || 0 : 0;
                            window.updateCartBadge(current + 1);
                        }
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
                        const badge = document.querySelector('.js-cart-badge');
                        const current = badge && !badge.classList.contains('hidden') ? parseInt(badge.textContent) || 0 : 0;
                        window.updateCartBadge(current + 1);
                        if (btnText) btnText.textContent = 'Added!';
                        setTimeout(() => {
                            btn.disabled = false;
                            if (btnText) btnText.textContent = originalText;
                        }, 1200);
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
