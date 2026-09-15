@extends('layouts.app')

@section('title', 'Openbox — Premium Electronics Marketplace')

@section('content')

    {{-- HERO SECTION --}}
    <section class="relative bg-white pt-10 pb-16 overflow-hidden">
        {{-- Background Glow --}}
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-100/40 via-white to-white pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center">
            {{-- Top Pill Badge --}}
            <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold text-amber-900 bg-amber-100/90 border border-amber-200/80 shadow-2xs mb-6">
                <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                <span>First 3 Months Free for New Vendors</span>
            </div>

            {{-- Main Headline --}}
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-950 tracking-tight leading-none mb-1">
                Premium Electronics
            </h1>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-[#b45309] tracking-tight mb-5">
                Marketplace
            </h2>

            {{-- Subheading --}}
            <p class="text-gray-500 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto leading-relaxed mb-6">
                Certified &amp; graded electronics from verified sellers across Bangladesh.<br class="hidden sm:inline">
                Every pre-owned item inspected, graded, and covered by our Openbox Guarantee.
            </p>

            {{-- Popular Keywords --}}
            <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-gray-400">
                <span class="font-medium text-gray-500">Popular:</span>
                <a href="{{ Route::has('search') ? route('search', ['q' => 'iPhone 15']) : '#' }}" class="hover:text-amber-600 transition-colors">iPhone 15 Pro Max</a>
                <span>·</span>
                <a href="{{ Route::has('search') ? route('search', ['q' => 'MacBook']) : '#' }}" class="hover:text-amber-600 transition-colors">MacBook Air M2</a>
                <span>·</span>
                <a href="{{ Route::has('search') ? route('search', ['q' => 'PlayStation 5']) : '#' }}" class="hover:text-amber-600 transition-colors">PlayStation 5</a>
                <span>·</span>
                <a href="{{ Route::has('search') ? route('search', ['q' => 'Apple Watch']) : '#' }}" class="hover:text-amber-600 transition-colors">Apple Watch Ultra</a>
                <span>·</span>
                <a href="{{ Route::has('search') ? route('search', ['q' => 'Sony XM5']) : '#' }}" class="hover:text-amber-600 transition-colors">Sony XM5</a>
            </div>
        </div>
    </section>

    {{-- BROWSE CATEGORIES --}}
    <section class="py-8 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-950">Browse Categories</h2>
            <a href="{{ route('categories.index') }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
            @php
                $categoryList = [
                    ['name' => 'Phones', 'slug' => 'smartphones', 'count' => '124 items', 'icon' => 'phones'],
                    ['name' => 'Laptops', 'slug' => 'laptops', 'count' => '89 items', 'icon' => 'laptops'],
                    ['name' => 'Tablets', 'slug' => 'tablets', 'count' => '45 items', 'icon' => 'tablets'],
                    ['name' => 'Smartwatches', 'slug' => 'smartwatches', 'count' => '67 items', 'icon' => 'smartwatches'],
                    ['name' => 'Audio', 'slug' => 'audio-headphones', 'count' => '150 items', 'icon' => 'audio'],
                    ['name' => 'Gaming & VR', 'slug' => 'gaming-consoles', 'count' => '38 items', 'icon' => 'gaming'],
                    ['name' => 'Cameras', 'slug' => 'cameras', 'count' => '52 items', 'icon' => 'cameras'],
                    ['name' => 'Accessories', 'slug' => 'accessories', 'count' => '95 items', 'icon' => 'accessories'],
                ];
            @endphp

            @foreach ($categoryList as $cat)
                <a href="{{ Route::has('shop') ? route('shop', ['category' => $cat['slug']]) : '#' }}" class="group bg-white border border-gray-100 hover:border-amber-300 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all duration-200">
                    <div class="w-12 h-12 rounded-xl bg-gray-50 group-hover:bg-amber-50 flex items-center justify-center text-gray-700 group-hover:text-amber-600 transition-colors mb-2.5">
                        <x-category-icon :slug="$cat['slug']" class="w-6 h-6" />
                    </div>
                    <span class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-amber-600 transition-colors line-clamp-1">{{ $cat['name'] }}</span>
                    <span class="text-[11px] text-gray-400 mt-0.5">{{ $cat['count'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- FEATURED PRODUCTS --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-950">Featured Products</h2>
            <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($featuredProducts as $product)
                <x-product-card
                    :title="$product['title']"
                    :brand="$product['brand']"
                    :price="$product['price']"
                    :compare-price="$product['comparePrice'] ?? null"
                    :rating="$product['rating'] ?? 4.9"
                    :rating-count="$product['ratingCount'] ?? 42"
                    :location="$product['location'] ?? 'Dhaka'"
                    :condition="$product['condition'] ?? 'used'"
                    :key-features="$product['keyFeatures'] ?? []"
                    :seller="$product['seller'] ?? null"
                    :is-new="$product['isNew'] ?? false"
                    :image="$product['image'] ?? null"
                    :href="$product['href'] ?? '#'"
                />
            @endforeach
        </div>
    </section>

    {{-- REFURBISHED DEALS --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2.5">
                <span class="text-purple-600 font-bold text-base sm:text-lg">⚡ Certified Refurbished</span>
                <span class="bg-purple-50 text-purple-700 text-[11px] font-bold px-2 py-0.5 rounded-full border border-purple-200/60">Save up to 40%</span>
            </div>
            <a href="{{ Route::has('shop') ? route('shop', ['condition' => 'refurbished']) : '#' }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($refurbishedDeals as $product)
                <x-product-card
                    :title="$product['title']"
                    :brand="$product['brand']"
                    :price="$product['price']"
                    :compare-price="$product['comparePrice'] ?? null"
                    :rating="$product['rating'] ?? 4.8"
                    :rating-count="$product['ratingCount'] ?? 29"
                    :location="$product['location'] ?? 'Dhaka'"
                    :condition="$product['condition'] ?? 'refurbished'"
                    :key-features="$product['keyFeatures'] ?? []"
                    :seller="$product['seller'] ?? null"
                    :is-new="false"
                    :image="$product['image'] ?? null"
                    :href="$product['href'] ?? '#'"
                />
            @endforeach
        </div>
    </section>

    {{-- WHY BUY ON OPENBOX --}}
    <section class="py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl font-extrabold text-gray-950 mb-2">Why Buy on Openbox?</h2>
                <p class="text-sm text-gray-500 leading-relaxed">We're not just another marketplace. We guarantee quality, transparency, and trust with every single device.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($whyBuy as $item)
                    <div class="bg-white border border-gray-100 rounded-2xl p-6 text-center shadow-2xs hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
                            @if (($item['icon'] ?? '') === 'shield')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            @elseif (($item['icon'] ?? '') === 'refresh')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            @elseif (($item['icon'] ?? '') === 'lock')
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                            @endif
                        </div>
                        <h3 class="text-sm font-bold text-gray-900 mb-2">{{ $item['title'] }}</h3>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- THE OPENBOX BUYER ADVANTAGE --}}
    <section class="py-14 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl font-extrabold text-gray-950 mb-2">The Openbox Guarantee</h2>
                <p class="text-sm text-gray-500 leading-relaxed">Shop pre-owned and new electronics with 100% confidence and zero hassle.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- 100% Inspected --}}
                <div class="bg-white border-2 border-emerald-100 hover:border-emerald-300 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 mb-4">
                        Quality Guaranteed
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Certified Diagnostic Check</h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                        Every device undergoes thorough hardware and component inspections to ensure pristine operational condition.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                        <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Multi-point diagnostic testing</li>
                        <li class="flex items-center gap-1.5"><span class="text-emerald-500 font-bold">✓</span> Battery health verification</li>
                    </ul>
                </div>

                {{-- 7-Day Return --}}
                <div class="bg-white border-2 border-amber-100 hover:border-amber-300 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200/60 mb-4">
                        Zero Risk Policy
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">7-Day Replacement</h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                        If your item doesn't match its listing description, return it within 7 days for a full refund or direct exchange.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                        <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Fast and easy return pickup</li>
                        <li class="flex items-center gap-1.5"><span class="text-amber-500 font-bold">✓</span> Instant escrow refund processing</li>
                    </ul>
                </div>

                {{-- Secure Escrow --}}
                <div class="bg-white border-2 border-blue-100 hover:border-blue-300 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60 mb-4">
                        Safe &amp; Secure
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Escrow Protected Checkout</h3>
                    <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                        Your payment is held safely in escrow and only released to the seller after you receive and inspect your package.
                    </p>
                    <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                        <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> 100% payment protection</li>
                        <li class="flex items-center gap-1.5"><span class="text-blue-500 font-bold">✓</span> Doorstep delivery nationwide</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- MOBILE & TECH (POPULAR) --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-950">Mobile &amp; Tech</h2>
            <a href="{{ Route::has('shop') ? route('shop', ['category' => 'phones']) : '#' }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($mobileTechProducts as $product)
                <x-product-card
                    :title="$product['title']"
                    :brand="$product['brand']"
                    :price="$product['price']"
                    :compare-price="$product['comparePrice'] ?? null"
                    :rating="$product['rating'] ?? 4.9"
                    :rating-count="$product['ratingCount'] ?? 35"
                    :location="$product['location'] ?? 'Dhaka'"
                    :condition="$product['condition'] ?? 'used'"
                    :key-features="$product['keyFeatures'] ?? []"
                    :seller="$product['seller'] ?? null"
                    :is-new="$product['isNew'] ?? false"
                    :image="$product['image'] ?? null"
                    :href="$product['href'] ?? '#'"
                />
            @endforeach
        </div>
    </section>

    {{-- TOP RATED VENDORS --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-950">Top Rated Vendors</h2>
            <a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($vendors as $vendor)
                <div class="bg-white border border-gray-100 hover:border-amber-300 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all">
                    <div class="w-12 h-12 rounded-full bg-amber-50 border border-amber-200/60 text-amber-700 font-bold flex items-center justify-center text-sm mb-2.5">
                        {{ strtoupper(substr($vendor['name'], 0, 2)) }}
                    </div>
                    <span class="text-xs font-bold text-gray-900 line-clamp-1">{{ $vendor['name'] }}</span>
                    <div class="flex items-center gap-1 mt-1 text-[11px] text-amber-500 font-semibold">
                        <span>★</span>
                        <span>{{ number_format((float) ($vendor['rating'] ?? 4.9), 1) }}</span>
                    </div>
                    <span class="text-[10px] text-gray-400 mt-0.5">{{ $vendor['salesCount'] ?? '120' }}+ sales</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- START SELLING CTA BANNER --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="relative bg-gradient-to-r from-gray-950 via-gray-900 to-amber-950 rounded-3xl p-8 sm:p-12 text-center text-white overflow-hidden shadow-xl">
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative max-w-2xl mx-auto">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black mb-3 tracking-tight">Start selling your electronics</h2>
                <p class="text-gray-300 text-xs sm:text-sm lg:text-base leading-relaxed mb-8">
                    Turn your unused gadgets into instant cash. List in minutes as an individual saler or open a verified business store with warehouse fulfillment.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="{{ Route::has('register.saler') ? route('register.saler') : (Route::has('register') ? route('register') : '#') }}"
                        class="w-full sm:w-auto bg-amber-400 hover:bg-amber-500 text-gray-950 font-bold text-sm px-8 py-3.5 rounded-xl shadow-lg transition-all"
                    >
                        Start Selling Now
                    </a>
                    <a
                        href="{{ Route::has('shop') ? route('shop') : '#' }}"
                        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white font-semibold text-sm px-7 py-3.5 rounded-xl border border-white/15 transition-all"
                    >
                        Explore Verified Deals →
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- LATEST DROPS --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-950">Latest Drops</h2>
            <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-semibold text-amber-600 hover:text-amber-700 flex items-center gap-1">
                <span>View All</span>
                <span>→</span>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($latestProducts as $product)
                <x-product-card
                    :title="$product['title']"
                    :brand="$product['brand']"
                    :price="$product['price']"
                    :compare-price="$product['comparePrice'] ?? null"
                    :rating="$product['rating'] ?? 4.9"
                    :rating-count="$product['ratingCount'] ?? 40"
                    :location="$product['location'] ?? 'Dhaka'"
                    :condition="$product['condition'] ?? 'used'"
                    :key-features="$product['keyFeatures'] ?? []"
                    :seller="$product['seller'] ?? null"
                    :is-new="$product['isNew'] ?? false"
                    :image="$product['image'] ?? null"
                    :href="$product['href'] ?? '#'"
                />
            @endforeach
        </div>
    </section>

    {{-- WHAT OUR COMMUNITY SAYS --}}
    <section class="py-14 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="text-2xl font-extrabold text-gray-950 mb-2">What Our Community Says</h2>
                <p class="text-sm text-gray-500">Real feedback from verified buyers and sellers nationwide.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($reviews as $review)
                    <div class="bg-white border border-gray-100 rounded-2xl p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition-shadow">
                        <div>
                            {{-- Stars --}}
                            <div class="flex items-center gap-1 text-amber-400 mb-3">
                                @for ($i = 0; $i < 5; $i++)
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
                                @endfor
                            </div>

                            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                                &ldquo;{{ $review['body'] }}&rdquo;
                            </p>
                        </div>

                        <div class="border-t border-gray-50 pt-3 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900">{{ $review['name'] }}</span>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Verified Buyer</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- POPULAR SEARCHES --}}
    <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
        <h3 class="text-sm font-bold text-gray-900 mb-4">Popular Searches</h3>
        <div class="flex flex-wrap gap-2">
            @php
                $popularTags = [
                    'iPhone 15 Pro', 'MacBook Air M2', 'Samsung Galaxy S24 Ultra', 'PlayStation 5 Slim',
                    'Apple Watch Ultra 2', 'Sony WH-1000XM5', 'iPad Pro 11"', 'Dell XPS 13',
                    'AirPods Pro 2', 'RTX 4080 Gaming Laptop', 'Canon EOS R6', 'Google Pixel 8 Pro',
                    'DJI Mini 4 Pro', 'Nintendo Switch OLED'
                ];
            @endphp
            @foreach ($popularTags as $tag)
                <a href="{{ Route::has('search') ? route('search', ['q' => $tag]) : '#' }}" class="bg-gray-50 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 border border-gray-200/70 text-xs font-medium text-gray-600 px-3.5 py-1.5 rounded-full transition-colors">
                    {{ $tag }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- STAY UPDATED (NEWSLETTER) --}}
    <section class="py-14 bg-white border-t border-gray-100">
        <div class="max-w-xl mx-auto px-4 sm:px-6 text-center">
            <h2 class="text-2xl font-black text-gray-950 mb-2">Stay Updated</h2>
            <p class="text-xs sm:text-sm text-gray-500 mb-6">Get the latest drops, price drops, and verified electronics deals straight to your inbox.</p>

            <form class="flex flex-col sm:flex-row gap-2.5">
                <input
                    type="email"
                    placeholder="Enter your email address"
                    class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400"
                    required
                >
                <button
                    type="submit"
                    class="bg-amber-400 hover:bg-amber-500 text-gray-950 font-bold px-7 py-3 rounded-xl text-sm shadow-xs transition-colors"
                >
                    Subscribe
                </button>
            </form>
        </div>
    </section>

@endsection
