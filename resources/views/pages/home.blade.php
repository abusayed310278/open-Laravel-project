@extends('layouts.app')

@section('title', 'Openbox — Premium Electronics Marketplace')

@section('content')

    @php $heroImageUrl = \App\Support\MediaUrl::resolve(setting('home_hero_image')); @endphp

    @if (feature_enabled('home_hero_section') && $heroSlides->isNotEmpty())
        {{-- HERO SLIDER (admin-managed slides, each linking to a product) --}}
        <section class="w-full bg-white" data-hero-slider>
            <div data-hero-viewport class="relative overflow-hidden" style="aspect-ratio:3.5/1;">
                <div data-hero-track style="display:flex;height:100%;transition:transform 700ms ease-in-out;will-change:transform;">
                @foreach ($heroSlides as $slide)
                    @php $slideUrl = $slide->product ? route('products.show', $slide->product) : null; @endphp
                    <div data-hero-slide style="flex:0 0 100%;min-width:0;height:100%;">
                        @if ($slideUrl)
                            <a href="{{ $slideUrl }}" title="{{ $slide->product->title }}" class="block" style="height:100%;">
                                <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->product->title }}" class="block" style="width:100%;height:100%;object-fit:contain;">
                            </a>
                        @else
                            <img src="{{ $slide->imageUrl() }}" alt="{{ config('app.name') }}" class="block" style="width:100%;height:100%;object-fit:contain;">
                        @endif
                    </div>
                @endforeach
                </div>

                @if ($heroSlides->count() > 1)
                    <button type="button" data-hero-prev aria-label="Previous slide" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow text-gray-900 flex items-center justify-center cursor-pointer">&larr;</button>
                    <button type="button" data-hero-next aria-label="Next slide" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/80 hover:bg-white shadow text-gray-900 flex items-center justify-center cursor-pointer">&rarr;</button>
                    <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
                        @foreach ($heroSlides as $slide)
                            <button type="button" data-hero-dot="{{ $loop->index }}" aria-label="Go to slide {{ $loop->iteration }}" class="w-2.5 h-2.5 rounded-full bg-white/60 cursor-pointer"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <script>
            (() => {
                const root = document.querySelector('[data-hero-slider]');
                const viewport = root.querySelector('[data-hero-viewport]');
                const firstImage = root.querySelector('[data-hero-slide] img');
                const syncHeight = () => {
                    if (firstImage.naturalWidth && firstImage.naturalHeight) {
                        viewport.style.aspectRatio = `${firstImage.naturalWidth} / ${firstImage.naturalHeight}`;
                    }
                };
                firstImage.complete ? syncHeight() : firstImage.addEventListener('load', syncHeight);
            })();
        </script>

        @if ($heroSlides->count() > 1)
            <script>
                (() => {
                    const root = document.querySelector('[data-hero-slider]');
                    const track = root.querySelector('[data-hero-track]');
                    const slides = root.querySelectorAll('[data-hero-slide]');
                    const dots = root.querySelectorAll('[data-hero-dot]');
                    let index = 0;
                    let timer;

                    const show = (next) => {
                        index = (next + slides.length) % slides.length;
                        track.style.transform = `translateX(-${index * 100}%)`;
                        dots.forEach((el, i) => el.classList.toggle('bg-white', i === index));
                        dots.forEach((el, i) => el.classList.toggle('bg-white/60', i !== index));
                    };
                    const restart = () => {
                        clearInterval(timer);
                        timer = setInterval(() => show(index + 1), 5000);
                    };

                    root.querySelector('[data-hero-prev]').addEventListener('click', () => { show(index - 1); restart(); });
                    root.querySelector('[data-hero-next]').addEventListener('click', () => { show(index + 1); restart(); });
                    dots.forEach((el) => el.addEventListener('click', () => { show(Number(el.dataset.heroDot)); restart(); }));
                    show(0);
                    restart();
                })();
            </script>
        @endif
    @elseif (feature_enabled('home_hero_section') && $heroImageUrl)
        {{-- HERO IMAGE (admin-managed) --}}
        <section class="w-full bg-white">
            <img src="{{ $heroImageUrl }}" alt="{{ config('app.name') }}" class="block w-full h-auto">
        </section>
    @elseif (feature_enabled('home_hero_section'))
        {{-- HERO SECTION --}}
        <section class="relative bg-white pt-14 sm:pt-20 pb-14 sm:pb-16 overflow-hidden">
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center">
                {{-- Top Pill Badge --}}
                @php
                    $heroBadge = setting('home_hero_badge', 'First 3 Months Free for New Vendors');
                    $heroTitle = setting('home_hero_title') ?: 'Premium Electronics';
                    $heroHighlight = setting('home_hero_highlight', 'Marketplace');
                    $heroSubtitle = setting('home_hero_subtitle', 'Certified & graded electronics from verified sellers across Bangladesh. Every pre-owned item inspected, graded, and covered by our Openbox Guarantee.');
                @endphp

                @if (filled($heroBadge))
                    <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold text-gray-900 bg-gray-100 border border-gray-200/80 shadow-2xs mb-6">
                        <svg class="w-4 h-4 text-gray-900" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                        <span>{{ $heroBadge }}</span>
                    </div>
                @endif

                {{-- Main Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight mb-5">
                    <span class="block text-[#18182b]">{{ $heroTitle }}</span>
                    @if (filled($heroHighlight))
                        <span class="block text-[#b45309]" style="color: rgb(180, 83, 9);">{{ $heroHighlight }}</span>
                    @endif
                </h1>

                {{-- Subheading --}}
                @if (filled($heroSubtitle))
                    <p class="text-gray-500 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto leading-relaxed mb-8">{{ $heroSubtitle }}</p>
                @endif

                {{-- Popular Keywords (Well-spaced tags) --}}
                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
                    <span class="text-xs font-semibold text-gray-400 mr-1.5">Popular:</span>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'iPhone 15']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-950 border border-gray-200 hover:border-gray-900 shadow-2xs transition-all">
                        iPhone 15 Pro Max
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'MacBook']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-950 border border-gray-200 hover:border-gray-900 shadow-2xs transition-all">
                        MacBook Air M2
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'PlayStation 5']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-950 border border-gray-200 hover:border-gray-900 shadow-2xs transition-all">
                        PlayStation 5
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'Apple Watch']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-950 border border-gray-200 hover:border-gray-900 shadow-2xs transition-all">
                        Apple Watch Ultra
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'Sony XM5']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-950 border border-gray-200 hover:border-gray-900 shadow-2xs transition-all">
                        Sony XM5
                    </a>
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_browse_categories'))
        {{-- BROWSE CATEGORIES --}}
        <section class="py-8 max-w-7xl mx-auto px-4 sm:px-6">

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
                    <a href="{{ Route::has('shop') ? route('shop', ['category' => $cat['slug']]) : '#' }}" class="group bg-white border border-gray-100 hover:border-amber-300 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 group-hover:bg-amber-50 border border-gray-100 group-hover:border-amber-200 flex items-center justify-center text-gray-600 group-hover:text-amber-600 transition-colors mb-2.5">
                            <x-category-icon :slug="$cat['slug']" class="w-6 h-6" />
                        </div>
                        <span class="text-xs sm:text-sm font-semibold text-gray-800 group-hover:text-amber-600 transition-colors line-clamp-1">{{ $cat['name'] }}</span>
                        <span class="text-[11px] text-gray-400 mt-0.5">{{ $cat['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_banners') && isset($banners) && $banners->isNotEmpty())
        {{-- PROMOTIONAL BANNERS --}}
        <section class="py-6 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-{{ min($banners->count(), 2) }} gap-6">
                @foreach ($banners as $b)
                    <a href="{{ $b->link ?: '#' }}" class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group relative border border-gray-100 bg-white">
                        @if ($b->video_url)
                            <video src="{{ $b->video_url }}" autoplay loop muted playsinline class="w-full h-64 sm:h-80 md:h-[380px] lg:h-[440px] object-cover group-hover:scale-[1.02] transition-transform duration-500"></video>
                        @else
                            <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="block w-full h-48 sm:h-64 md:h-[320px] lg:h-[400px] object-contain group-hover:scale-[1.02] transition-transform duration-500" onerror="this.parentElement.style.display='none'">
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_featured_products'))
        {{-- FEATURED PRODUCTS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950">Featured Products</h2>
                <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 hover:text-black flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($featuredProducts as $product)
                    <x-product-card
                        :id="$product['id'] ?? null"
                        :title="$product['title']"
                        :category="$product['category'] ?? $product['brand'] ?? 'Electronics'"
                        :brand="$product['brand']"
                        :price="$product['price']"
                        :compare-price="$product['comparePrice'] ?? null"
                        :rating="$product['rating'] ?? null"
                        :rating-count="$product['ratingCount'] ?? null"
                        :seller="$product['seller'] ?? null"
                        :is-new="$product['isNew'] ?? false"
                        :key-features="$product['keyFeatures'] ?? []"
                        :image="$product['image'] ?? null"
                        :href="$product['href'] ?? '#'"
                        :is-verified="$product['isVerified'] ?? true"
                        :is-admin-approved="$product['isAdminApproved'] ?? true"
                    />
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_why_buy'))
        {{-- WHY BUY ON OPENBOX --}}
        @php
            $whyBuyStyles = [
                ['title' => 'Verified Quality', 'text' => 'All products tested and verified', 'bg' => 'bg-amber-100', 'color' => 'text-gray-900', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['title' => 'Fast & Reliable Delivery', 'text' => 'Across Abu Dhabi & UAE', 'bg' => 'bg-orange-100', 'color' => 'text-gray-900', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                ['title' => 'A More Sustainable Choice', 'text' => 'Give tech a second life', 'bg' => 'bg-green-100', 'color' => 'text-green-600', 'icon' => 'M5 19c0-9 5-14 15-14 0 10-5 15-14 15m-1 1c2-5 5-8 9-10'],
                ['title' => 'Dedicated Support', 'text' => "We're here to help", 'bg' => 'bg-orange-100', 'color' => 'text-gray-900', 'icon' => 'M4 14v-2a8 8 0 0116 0v2M4 14a2 2 0 012-2h1v6H6a2 2 0 01-2-2v-2zm16 0a2 2 0 00-2-2h-1v6h1a2 2 0 002-2v-2zm-3 6c0 1-2 2-5 2'],
            ];
        @endphp
        <section class="py-6 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-1 h-4 rounded-full bg-brand-400"></span>
                <h2 class="text-lg font-bold text-gray-950">{{ $whyBuyContent['heading'] }}</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($whyBuyStyles as $i => $item)
                    <div class="bg-white border border-gray-100 rounded-xl px-6 py-5 flex items-center gap-5 shadow-2xs">
                        <div class="w-14 h-14 shrink-0 rounded-full {{ $item['bg'] }} {{ $item['color'] }} flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}" /></svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-gray-900">{{ $whyBuyContent['items'][$i]['title'] }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ $whyBuyContent['items'][$i]['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_why_buy') && collect($promoCards)->contains(fn ($card) => $card['product']))
        {{-- PRODUCT PROMO CARDS (real products, admin-editable text) --}}
        @php
            $promoStyles = [
                1 => ['wrap' => 'from-[#7a5642] to-[#3a2a24] text-white', 'text' => 'text-white/80', 'body' => 'text-xs mt-2 leading-snug', 'img' => 'bg-white rounded-lg p-2 h-[85%] w-2/5 object-contain group-hover:scale-105 transition-transform duration-300'],
                2 => ['wrap' => 'from-[#eef1f6] to-[#c9d2e0] text-gray-950', 'text' => '', 'body' => 'text-xl font-medium leading-tight', 'img' => 'h-full w-2/5 object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-300'],
                3 => ['wrap' => 'from-[#16213a] to-[#0b1220] text-white', 'text' => 'text-white/80', 'body' => 'text-xs mt-2 leading-snug', 'img' => 'bg-white rounded-lg p-2 h-[85%] w-2/5 object-contain group-hover:scale-105 transition-transform duration-300'],
            ];
        @endphp
        <section class="pb-6 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($promoCards as $n => $card)
                    @continue(! $card['product'])
                    @php $style = $promoStyles[$n]; @endphp
                    <a href="{{ route('products.show', $card['product']) }}" title="{{ $card['product']->title }}" class="group relative flex items-center justify-between gap-3 h-44 rounded-xl overflow-hidden px-6 py-5 bg-gradient-to-br {{ $style['wrap'] }} shadow-2xs hover:shadow-lg transition-shadow">
                        <div class="relative z-10 max-w-[55%]">
                            <h3 class="text-xl font-extrabold leading-tight">{!! nl2br(e($card['title'])) !!}</h3>
                            <p class="{{ $style['body'] }} {{ $style['text'] }}">{!! nl2br(e($card['text'])) !!}</p>
                            <span class="inline-flex items-center gap-2 mt-4 bg-brand-400 group-hover:bg-brand-500 text-gray-950 text-xs font-bold px-4 py-2 rounded-md transition-colors">{{ $card['button'] }} <span>→</span></span>
                        </div>
                        <img src="{{ $card['product']->primaryImageUrl() }}" alt="{{ $card['product']->title }}" class="relative z-10 {{ $style['img'] }}" loading="lazy">
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_refurbished_deals'))
        {{-- REFURBISHED DEALS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-bold text-gray-950">⚡ Certified Refurbished</h2>
                    <span class="bg-gray-50 text-gray-900 text-[11px] font-bold px-2.5 py-1 rounded-md border border-gray-200">Save up to 40%</span>
                </div>
                <a href="{{ Route::has('shop') ? route('shop', ['condition' => 'refurbished']) : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 hover:text-black flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($refurbishedDeals as $product)
                    <x-product-card
                        :id="$product['id'] ?? null"
                        :title="$product['title']"
                        :category="$product['category'] ?? $product['brand'] ?? 'Electronics'"
                        :brand="$product['brand']"
                        :price="$product['price']"
                        :compare-price="$product['comparePrice'] ?? null"
                        :rating="$product['rating'] ?? null"
                        :rating-count="$product['ratingCount'] ?? null"
                        :seller="$product['seller'] ?? null"
                        :is-new="$product['isNew'] ?? false"
                        :key-features="$product['keyFeatures'] ?? []"
                        :image="$product['image'] ?? null"
                        :href="$product['href'] ?? '#'"
                        :is-verified="$product['isVerified'] ?? true"
                        :is-admin-approved="$product['isAdminApproved'] ?? true"
                    />
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_openbox_guarantee'))
        {{-- THE OPENBOX BUYER ADVANTAGE --}}
        <section class="py-14 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl font-extrabold text-gray-950 mb-2">The Openbox Guarantee</h2>
                    <p class="text-sm text-gray-500 leading-relaxed">Shop pre-owned and new electronics with 100% confidence and zero hassle.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- 100% Inspected --}}
                    <div class="bg-white border border-gray-100 hover:border-gray-900 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 text-gray-900 border border-gray-200 mb-4">
                            Quality Guaranteed
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">Certified Diagnostic Check</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                            Every device undergoes thorough hardware and component inspections to ensure pristine operational condition.
                        </p>
                        <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> Multi-point diagnostic testing</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> Battery health verification</li>
                        </ul>
                    </div>

                    {{-- 7-Day Return --}}
                    <div class="bg-white border border-gray-100 hover:border-gray-900 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 text-gray-900 border border-gray-200 mb-4">
                            Zero Risk Policy
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">7-Day Replacement</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                            If your item doesn't match its listing description, return it within 7 days for a full refund or direct exchange.
                        </p>
                        <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> Fast and easy return pickup</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> Instant escrow refund processing</li>
                        </ul>
                    </div>

                    {{-- Secure Escrow --}}
                    <div class="bg-white border border-gray-100 hover:border-gray-900 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 text-gray-900 border border-gray-200 mb-4">
                            Safe &amp; Secure
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-2">Escrow Protected Checkout</h3>
                        <p class="text-xs sm:text-sm text-gray-600 leading-relaxed mb-4">
                            Your payment is held safely in escrow and only released to the seller after you receive and inspect your package.
                        </p>
                        <ul class="text-xs text-gray-500 space-y-1.5 border-t border-gray-50 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> 100% payment protection</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 font-bold">✓</span> Doorstep delivery nationwide</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_mobile_tech'))
        {{-- MOBILE & TECH (POPULAR) --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950">Mobile &amp; Tech</h2>
                <a href="{{ Route::has('shop') ? route('shop', ['category' => 'phones']) : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 hover:text-black flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($mobileTechProducts as $product)
                    <x-product-card
                        :id="$product['id'] ?? null"
                        :title="$product['title']"
                        :category="$product['category'] ?? $product['brand'] ?? 'Electronics'"
                        :brand="$product['brand']"
                        :price="$product['price']"
                        :compare-price="$product['comparePrice'] ?? null"
                        :rating="$product['rating'] ?? null"
                        :rating-count="$product['ratingCount'] ?? null"
                        :seller="$product['seller'] ?? null"
                        :is-new="$product['isNew'] ?? false"
                        :key-features="$product['keyFeatures'] ?? []"
                        :image="$product['image'] ?? null"
                        :href="$product['href'] ?? '#'"
                        :is-verified="$product['isVerified'] ?? true"
                        :is-admin-approved="$product['isAdminApproved'] ?? true"
                    />
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_top_vendors'))
        {{-- TOP RATED VENDORS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950">Top Rated Vendors</h2>
                <a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 hover:text-black flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ($vendors as $vendor)
                    <div class="bg-white border border-gray-100 hover:border-gray-900 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-full bg-gray-50 border border-gray-200 text-gray-900 font-bold flex items-center justify-center text-sm mb-2.5">
                            {{ strtoupper(substr($vendor['name'], 0, 2)) }}
                        </div>
                        <span class="text-xs font-bold text-gray-900 line-clamp-1">{{ $vendor['name'] }}</span>
                        <div class="flex items-center gap-1 mt-1 text-[11px] text-gray-950 font-semibold">
                            <span>★</span>
                            <span>{{ number_format((float) ($vendor['rating'] ?? 4.9), 1) }}</span>
                        </div>
                        <span class="text-[10px] text-gray-400 mt-0.5">{{ $vendor['salesCount'] ?? '120' }}+ sales</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_start_selling_cta'))
        {{-- START SELLING CTA BANNER --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="relative bg-gray-50 border border-gray-100 rounded-3xl p-8 sm:p-12 text-center shadow-2xs">
                <div class="relative max-w-2xl mx-auto">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-950 mb-3 tracking-tight">Start selling your electronics</h2>
                    <p class="text-gray-600 text-xs sm:text-sm lg:text-base leading-relaxed mb-8">
                        Turn your unused gadgets into instant cash. List in minutes as a seller or open a verified store with warehouse fulfillment.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a
                            href="{{ Route::has('register.saler') ? route('register.saler') : (Route::has('register') ? route('register') : '#') }}"
                            class="w-full sm:w-auto bg-amber-400 hover:bg-amber-500 text-gray-950 font-bold text-sm px-8 py-3.5 rounded-xl shadow-xs transition-all"
                        >
                            Start Selling Now
                        </a>
                        <a
                            href="{{ Route::has('shop') ? route('shop') : '#' }}"
                            class="w-full sm:w-auto bg-white hover:bg-gray-100 text-gray-900 font-semibold text-sm px-7 py-3.5 rounded-xl border border-gray-200 shadow-2xs transition-all"
                        >
                            Explore Verified Deals →
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_latest_drops'))
        {{-- LATEST DROPS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950">Latest Drops</h2>
                <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 hover:text-black flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($latestProducts as $product)
                    <x-product-card
                        :id="$product['id'] ?? null"
                        :title="$product['title']"
                        :category="$product['category'] ?? $product['brand'] ?? 'Electronics'"
                        :brand="$product['brand']"
                        :price="$product['price']"
                        :compare-price="$product['comparePrice'] ?? null"
                        :rating="$product['rating'] ?? null"
                        :rating-count="$product['ratingCount'] ?? null"
                        :seller="$product['seller'] ?? null"
                        :is-new="$product['isNew'] ?? false"
                        :key-features="$product['keyFeatures'] ?? []"
                        :image="$product['image'] ?? null"
                        :href="$product['href'] ?? '#'"
                        :is-verified="$product['isVerified'] ?? true"
                        :is-admin-approved="$product['isAdminApproved'] ?? true"
                    />
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_reviews'))
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
                                <div class="flex items-center gap-1 text-gray-950 mb-3">
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
    @endif

    @if (feature_enabled('home_popular_searches'))
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
                    <a href="{{ Route::has('search') ? route('search', ['q' => $tag]) : '#' }}" class="bg-white hover:bg-amber-50 hover:text-amber-800 hover:border-amber-300 border border-gray-200 text-xs font-medium text-gray-700 px-5 py-2 rounded-lg shadow-2xs transition-colors">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_newsletter'))
        {{-- STAY UPDATED (NEWSLETTER) --}}
        <section class="pt-8 pb-5 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="rounded-lg bg-gradient-to-r from-brand-300 to-brand-400 px-6 sm:px-10 py-5 flex flex-col lg:flex-row lg:items-center gap-5 lg:gap-10">
                <div class="flex items-center gap-4 lg:w-5/12">
                    <svg class="w-10 h-10 text-gray-950 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm0 0l9 6 9-6" /></svg>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-950 leading-tight">{{ $newsletter['title'] }}</h2>
                        <p class="text-xs text-gray-900/80">{{ $newsletter['text'] }}</p>
                    </div>
                </div>

                <form action="{{ route('newsletter.subscribe') }}" method="POST" data-newsletter-form class="flex flex-1 flex-col sm:flex-row gap-3">
                    @csrf
                    <input
                        type="email"
                        name="email"
                        placeholder="{{ $newsletter['placeholder'] }}"
                        class="flex-1 bg-white border-0 rounded-md px-4 py-3 text-sm text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-900"
                        required
                    >
                    <button
                        type="submit"
                        class="bg-gray-950 hover:bg-black text-white font-bold px-8 py-3 rounded-md text-sm transition-colors"
                    >
                        {{ $newsletter['button'] }}
                    </button>
                </form>
            </div>
        </section>
    @endif

@endsection
