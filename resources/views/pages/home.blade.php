@extends('layouts.app')

@section('title', 'Openbox — Premium Electronics Marketplace')

@section('content')

<style>
    :root {
        --site-header-height: 140px;
    }
    @media (min-width: 768px) {
        #home-first-screen {
            height: calc(100dvh - var(--site-header-height, 140px));
            min-height: 480px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        #home-first-screen [data-hero-slider] {
            flex: 1 1 0%;
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        #home-first-screen [data-hero-viewport] {
            flex: 1 1 0%;
            min-height: 0;
            height: 100% !important;
            aspect-ratio: auto !important;
        }
        #home-first-screen [data-hero-track],
        #home-first-screen [data-hero-slide],
        #home-first-screen [data-hero-slide] > a,
        #home-first-screen [data-hero-slide] > div {
            height: 100% !important;
        }
        #home-first-screen [data-hero-slide] img {
            height: 100% !important;
            width: 100% !important;
            object-fit: cover !important;
        }
        #home-first-screen [data-home-categories] {
            flex-shrink: 0;
        }
    }
</style>

    @php
        $heroImageUrl = \App\Support\MediaUrl::resolve(setting('home_hero_image'));
        $hasHero = feature_enabled('home_hero_section') && ($heroSlides->isNotEmpty() || $heroImageUrl);
        $hasCategories = feature_enabled('home_browse_categories') && isset($categories) && $categories->isNotEmpty();
        $wrapFirstScreen = $hasHero && $hasCategories;
    @endphp

    @if ($wrapFirstScreen)
        <div id="home-first-screen" class="w-full bg-white dark:bg-[#0b0f19]">
    @endif

    @if (feature_enabled('home_hero_section') && $heroSlides->isNotEmpty())
        {{-- HERO SLIDER (admin-managed slides, each linking to a product) --}}
        <section class="w-full bg-white dark:bg-[#0b0f19]" data-hero-slider>
            <div data-hero-viewport class="relative overflow-hidden w-full" style="aspect-ratio:3.5/1;">
                <div data-hero-track style="display:flex;height:100%;transition:transform 700ms ease-in-out;will-change:transform;">
                @foreach ($heroSlides as $slide)
                    @php $slideUrl = $slide->product ? route('products.show', $slide->product) : null; @endphp
                    <div data-hero-slide style="flex:0 0 100%;min-width:0;height:100%;" class="relative">
                        @if ($slideUrl)
                            <a href="{{ $slideUrl }}" title="{{ $slide->product->title }}" class="block relative w-full h-full group" style="height:100%;">
                                <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->product->title }}" class="block w-full h-full" style="width:100%;height:100%;object-fit:cover;">
                                @if ($slide->enable_overlay)
                                    @include('pages.partials.hero-slide-overlay', ['slide' => $slide, 'isLink' => true])
                                @endif
                            </a>
                        @else
                            <div class="relative w-full h-full" style="height:100%;">
                                <img src="{{ $slide->imageUrl() }}" alt="{{ config('app.name') }}" class="block w-full h-full" style="width:100%;height:100%;object-fit:cover;">
                                @if ($slide->enable_overlay)
                                    @include('pages.partials.hero-slide-overlay', ['slide' => $slide, 'isLink' => false])
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
                </div>

                @if ($heroSlides->count() > 1)
                    <button type="button" data-hero-prev aria-label="Previous slide" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-brand-500 hover:bg-brand-600 shadow text-white flex items-center justify-center cursor-pointer">&larr;</button>
                    <button type="button" data-hero-next aria-label="Next slide" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-brand-500 hover:bg-brand-600 shadow text-white flex items-center justify-center cursor-pointer">&rarr;</button>
                    <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2">
                        @foreach ($heroSlides as $slide)
                            <button type="button" data-hero-dot="{{ $loop->index }}" aria-label="Go to slide {{ $loop->iteration }}" class="w-2.5 h-2.5 rounded-full bg-white/70 cursor-pointer"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        <script>
            (() => {
                const syncHeaderAndViewport = () => {
                    const ann = document.getElementById('site-announcement-bar');
                    const hdr = document.getElementById('site-header');
                    const annH = ann ? ann.getBoundingClientRect().height : 0;
                    const hdrH = hdr ? hdr.getBoundingClientRect().height : 0;
                    const total = Math.round(annH + hdrH);
                    if (total > 0) {
                        document.documentElement.style.setProperty('--site-header-height', total + 'px');
                    }

                    const root = document.querySelector('[data-hero-slider]');
                    if (!root) return;
                    const viewport = root.querySelector('[data-hero-viewport]');
                    const firstImage = root.querySelector('[data-hero-slide] img');
                    if (viewport) {
                        if (window.innerWidth < 768 && firstImage && firstImage.naturalWidth && firstImage.naturalHeight) {
                            viewport.style.aspectRatio = `${firstImage.naturalWidth} / ${firstImage.naturalHeight}`;
                        } else {
                            viewport.style.aspectRatio = 'auto';
                        }
                    }
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', syncHeaderAndViewport);
                } else {
                    syncHeaderAndViewport();
                }

                window.addEventListener('resize', syncHeaderAndViewport);
                window.addEventListener('load', syncHeaderAndViewport);
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
                        dots.forEach((el, i) => {
                            el.classList.toggle('bg-brand-500', i === index);
                            el.classList.toggle('bg-white/70', i !== index);
                        });
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
        <section class="w-full bg-white dark:bg-[#0b0f19]">
            <img src="{{ $heroImageUrl }}" alt="{{ config('app.name') }}" class="block w-full h-auto">
        </section>
    @elseif (feature_enabled('home_hero_section'))
        {{-- HERO SECTION --}}
        <section class="relative bg-white dark:bg-[#0b0f19] pt-14 sm:pt-20 pb-14 sm:pb-16 overflow-hidden">
            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 text-center">
                {{-- Top Pill Badge --}}
                @php
                    $heroBadge = setting('home_hero_badge', 'First 3 Months Free for New Vendors');
                    $heroTitle = setting('home_hero_title') ?: 'Premium Electronics';
                    $heroHighlight = setting('home_hero_highlight', 'Marketplace');
                    $heroSubtitle = setting('home_hero_subtitle', 'Certified & graded electronics from verified sellers across Bangladesh. Every pre-owned item inspected, graded, and covered by our Openbox Guarantee.');
                @endphp

                @if (filled($heroBadge))
                    <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 bg-gray-100 dark:bg-slate-800 border border-gray-200/80 dark:border-slate-700 shadow-2xs mb-6">
                        <svg class="w-4 h-4 text-gray-900 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" /></svg>
                        <span>{{ $heroBadge }}</span>
                    </div>
                @endif

                {{-- Main Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight mb-5">
                    <span class="block text-gray-950 dark:text-white">{{ $heroTitle }}</span>
                    @if (filled($heroHighlight))
                        <span class="block text-amber-600 dark:text-amber-400">{{ $heroHighlight }}</span>
                    @endif
                </h1>

                {{-- Subheading --}}
                @if (filled($heroSubtitle))
                    <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base lg:text-lg max-w-2xl mx-auto leading-relaxed mb-8">{{ $heroSubtitle }}</p>
                @endif

                {{-- Popular Keywords (Well-spaced tags) --}}
                <div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3">
                    <span class="text-xs font-semibold text-gray-400 mr-1.5">Popular:</span>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'iPhone 15']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-950 dark:hover:text-white border border-gray-200 dark:border-slate-700 hover:border-gray-900 dark:hover:border-amber-400 shadow-2xs transition-all">
                        iPhone 15 Pro Max
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'MacBook']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-950 dark:hover:text-white border border-gray-200 dark:border-slate-700 hover:border-gray-900 dark:hover:border-amber-400 shadow-2xs transition-all">
                        MacBook Air M2
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'PlayStation 5']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-950 dark:hover:text-white border border-gray-200 dark:border-slate-700 hover:border-gray-900 dark:hover:border-amber-400 shadow-2xs transition-all">
                        PlayStation 5
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'Apple Watch']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-950 dark:hover:text-white border border-gray-200 dark:border-slate-700 hover:border-gray-900 dark:hover:border-amber-400 shadow-2xs transition-all">
                        Apple Watch Ultra
                    </a>
                    <a href="{{ Route::has('search') ? route('search', ['q' => 'Sony XM5']) : '#' }}" class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-gray-950 dark:hover:text-white border border-gray-200 dark:border-slate-700 hover:border-gray-900 dark:hover:border-amber-400 shadow-2xs transition-all">
                        Sony XM5
                    </a>
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_browse_categories') && isset($categories) && $categories->isNotEmpty())
        {{-- BROWSE CATEGORIES (Matches Screenshot 1) --}}
        <section data-home-categories class="py-2.5 sm:py-3.5 max-w-7xl mx-auto px-4 sm:px-6 w-full shrink-0">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-11 gap-2 sm:gap-2.5 lg:gap-3">
                @foreach ($categories as $cat)
                    @php
                        $catName = is_array($cat) ? $cat['name'] : $cat->name;
                        $catSlug = is_array($cat) ? $cat['slug'] : $cat->slug;
                        $catImage = is_array($cat) ? ($cat['image'] ?? null) : $cat->imageUrl();
                        $isDeals = (is_array($cat) && !empty($cat['is_deals'])) || $catSlug === 'deals';
                        $catUrl = $isDeals
                            ? (Route::has('shop') ? route('shop', ['deal' => 1]) : '#')
                            : (Route::has('shop') ? route('shop', ['category' => $catSlug]) : '#');
                    @endphp
                    <a href="{{ $catUrl }}" class="group bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-amber-300 dark:hover:border-amber-400 rounded-2xl py-2.5 px-2 sm:py-3 sm:px-2 flex flex-col items-center justify-center text-center shadow-2xs hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 {{ $isDeals ? 'hover:border-red-300 dark:hover:border-red-400' : '' }}">
                        <div class="mb-1.5 sm:mb-2 flex items-center justify-center transition-colors {{ $isDeals ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-200 group-hover:text-amber-500 dark:group-hover:text-amber-400' }}">
                            @if (!empty($catImage))
                                <img src="{{ $catImage }}" alt="{{ $catName }}" class="w-7 h-7 sm:w-8 sm:h-8 object-contain" />
                            @else
                                <x-category-icon :slug="$catSlug" class="w-7 h-7 sm:w-8 sm:h-8" />
                            @endif
                        </div>
                        <span class="text-xs sm:text-[13px] font-semibold text-gray-800 dark:text-gray-200 transition-colors line-clamp-1 {{ $isDeals ? 'font-bold group-hover:text-red-600 dark:group-hover:text-red-400' : 'group-hover:text-amber-600 dark:group-hover:text-amber-400' }}" title="{{ $catName }}">
                            {{ $catName }}
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($wrapFirstScreen)
        </div>
    @endif

    @if (feature_enabled('home_banners') && isset($banners) && $banners->isNotEmpty())
        {{-- PROMOTIONAL BANNERS --}}
        <section class="py-6 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 {{ $banners->count() > 1 ? 'md:grid-cols-2' : '' }} gap-6">
                @foreach ($banners as $b)
                    @php $mediaItems = $b->media_items; @endphp
                    @if (count($mediaItems) > 1)
                        {{-- Multi-Slide Banner (Images + Video with auto-advance on video end) --}}
                        <div class="banner-slider relative rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group border border-gray-100 dark:border-slate-800 bg-gray-900 w-full" style="aspect-ratio: 1024 / 339; min-height: 200px;" data-banner-id="{{ $b->id }}">
                            @if ($b->link)
                                <a href="{{ $b->link }}" class="absolute inset-0 z-20" aria-label="{{ $b->title }}"></a>
                            @endif

                            <div class="banner-slides-wrapper relative w-full h-full overflow-hidden" style="width: 100%; height: 100%; min-height: 200px;">
                                @foreach ($mediaItems as $idx => $item)
                                    <div class="banner-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out {{ $idx === 0 ? 'opacity-100 z-10 pointer-events-auto' : 'opacity-0 z-0 pointer-events-none' }}" style="width: 100%; height: 100%;" data-slide-index="{{ $idx }}" data-slide-type="{{ $item['type'] }}">
                                        @if ($item['type'] === 'video')
                                            <video src="{{ $item['url'] }}" playsinline muted class="w-full h-full object-cover block" style="width: 100%; height: 100%; object-fit: cover; display: block;" preload="auto"></video>
                                        @else
                                            <img src="{{ $item['url'] }}" alt="{{ $b->title }}" class="w-full h-full object-cover select-none block" style="width: 100%; height: 100%; object-fit: cover; display: block;" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Navigation Dots --}}
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-30 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/40 backdrop-blur-xs">
                                @foreach ($mediaItems as $idx => $item)
                                    <button type="button" class="banner-dot w-2 h-2 rounded-full transition-all duration-300 {{ $idx === 0 ? 'w-5 bg-white' : 'bg-white/50 hover:bg-white/80' }}" data-dot-index="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>

                            {{-- Prev / Next Arrows on Hover --}}
                            <button type="button" class="banner-prev absolute left-3 top-1/2 -translate-y-1/2 z-30 w-8 h-8 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer shadow-sm" aria-label="Previous Slide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" class="banner-next absolute right-3 top-1/2 -translate-y-1/2 z-30 w-8 h-8 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer shadow-sm" aria-label="Next Slide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    @else
                        {{-- Single Item Banner --}}
                        <a href="{{ $b->link ?: '#' }}" class="block rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 group relative border border-gray-100 dark:border-slate-800 bg-gray-900 w-full" style="aspect-ratio: 1024 / 339; min-height: 200px; width: 100%;">
                            @if ($b->video_url)
                                <video src="{{ $b->video_url }}" autoplay loop muted playsinline class="block w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" style="width: 100%; height: 100%; object-fit: cover; display: block;"></video>
                            @else
                                <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="block w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.parentElement.style.display='none'">
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <script>
            (function () {
                function initBannerSliders() {
                    document.querySelectorAll('.banner-slider').forEach(function (slider) {
                        if (slider.dataset.sliderInitialized === 'true') return;
                        slider.dataset.sliderInitialized = 'true';

                        const slides = slider.querySelectorAll('.banner-slide');
                        const dots = slider.querySelectorAll('.banner-dot');
                        const prevBtn = slider.querySelector('.banner-prev');
                        const nextBtn = slider.querySelector('.banner-next');
                        let currentIndex = 0;
                        let timer = null;
                        const total = slides.length;
                        if (total <= 1) return;

                        function activateSlide(index) {
                            if (timer) {
                                clearTimeout(timer);
                                timer = null;
                            }

                            // Stop previous video if any
                            const prevSlide = slides[currentIndex];
                            if (prevSlide) {
                                const prevVideo = prevSlide.querySelector('video');
                                if (prevVideo) {
                                    prevVideo.pause();
                                    prevVideo.currentTime = 0;
                                }
                            }

                            currentIndex = (index + total) % total;

                            // Switch slide visibility
                            slides.forEach(function (slide, i) {
                                if (i === currentIndex) {
                                    slide.classList.remove('opacity-0', 'pointer-events-none', 'z-0');
                                    slide.classList.add('opacity-100', 'pointer-events-auto', 'z-10');
                                } else {
                                    slide.classList.remove('opacity-100', 'pointer-events-auto', 'z-10');
                                    slide.classList.add('opacity-0', 'pointer-events-none', 'z-0');
                                }
                            });

                            // Switch dots
                            dots.forEach(function (dot, i) {
                                if (i === currentIndex) {
                                    dot.classList.remove('bg-white/50');
                                    dot.classList.add('w-5', 'bg-white');
                                } else {
                                    dot.classList.remove('w-5', 'bg-white');
                                    dot.classList.add('bg-white/50');
                                }
                            });

                            // Determine slide media behavior
                            const currentSlide = slides[currentIndex];
                            const slideType = currentSlide.getAttribute('data-slide-type');

                            if (slideType === 'video') {
                                const video = currentSlide.querySelector('video');
                                if (video) {
                                    video.currentTime = 0;
                                    video.muted = true;

                                    let advanced = false;
                                    const advanceNext = function () {
                                        if (advanced) return;
                                        advanced = true;
                                        video.removeEventListener('ended', advanceNext);
                                        activateSlide(currentIndex + 1);
                                    };

                                    video.addEventListener('ended', advanceNext);

                                    const playPromise = video.play();
                                    if (playPromise !== undefined) {
                                        playPromise.catch(function () {
                                            // Fallback if autoplay is restricted
                                            timer = setTimeout(advanceNext, 6000);
                                        });
                                    }

                                    // Fallback safety timeout based on duration
                                    const fallbackDuration = (video.duration && !isNaN(video.duration) && video.duration > 0)
                                        ? (video.duration * 1000) + 1000
                                        : 8000;
                                    timer = setTimeout(advanceNext, Math.min(fallbackDuration, 60000));
                                } else {
                                    timer = setTimeout(function () {
                                        activateSlide(currentIndex + 1);
                                    }, 5000);
                                }
                            } else {
                                // Standard image slide timer (5 seconds)
                                timer = setTimeout(function () {
                                    activateSlide(currentIndex + 1);
                                }, 5000);
                            }
                        }

                        // Dot buttons
                        dots.forEach(function (dot, idx) {
                            dot.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                activateSlide(idx);
                            });
                        });

                        // Navigation arrows
                        if (prevBtn) {
                            prevBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                activateSlide(currentIndex - 1);
                            });
                        }
                        if (nextBtn) {
                            nextBtn.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                activateSlide(currentIndex + 1);
                            });
                        }

                        // Start first slide
                        activateSlide(0);
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initBannerSliders);
                } else {
                    initBannerSliders();
                }
            })();
        </script>
    @endif

    @if (feature_enabled('home_featured_products'))
        {{-- FEATURED PRODUCTS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950 dark:text-white">Featured Products</h2>
                <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 dark:text-gray-300 hover:text-black dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
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
                        :quantity="$product['quantity'] ?? null"
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
        <section class="py-8 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-1 h-4 rounded-full bg-brand-500"></span>
                <h2
                    class="text-gray-950 dark:text-white tracking-tight"
                    style="font-size: {{ $whyBuyContent['heading_size'] ?? 18 }}px; font-weight: {{ $whyBuyContent['heading_weight'] ?? '700' }}; color: {{ $whyBuyContent['heading_color'] ?? '#030712' }};"
                >
                    {{ $whyBuyContent['heading'] }}
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($whyBuyContent['items'] as $item)
                    <div
                        class="flex items-center gap-4 rounded-xl border border-gray-100 dark:border-slate-700 px-5 py-4 shadow-2xs transition-shadow duration-200 hover:shadow-md"
                        style="background-color: {{ $item['card_bg'] ?? '#ffffff' }};"
                    >
                        <div class="shrink-0 flex items-center justify-center" style="color: {{ $item['icon_color'] }};">
                            @if (!empty($item['custom_icon']))
                                <img
                                    src="{{ $item['custom_icon'] }}"
                                    alt="{{ $item['title'] }}"
                                    class="object-contain"
                                    style="width: {{ $item['icon_size'] ?? 24 }}px; height: {{ $item['icon_size'] ?? 24 }}px;"
                                >
                            @else
                                <svg
                                    style="width: {{ $item['icon_size'] ?? 24 }}px; height: {{ $item['icon_size'] ?? 24 }}px;"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $item['icon_svg_d'] }}" />
                                </svg>
                            @endif
                        </div>

                        <div class="min-w-0">
                            <h3
                                class="leading-snug"
                                style="font-size: {{ $item['title_size'] ?? 14 }}px; font-weight: {{ $item['title_weight'] ?? '700' }}; color: {{ $item['title_color'] ?? '#0f172a' }};"
                            >
                                {{ $item['title'] }}
                            </h3>
                            <p
                                class="mt-0.5 leading-snug"
                                style="font-size: {{ $item['text_size'] ?? 12 }}px; font-weight: {{ $item['text_weight'] ?? '400' }}; color: {{ $item['text_color'] ?? '#64748b' }};"
                            >
                                {{ $item['text'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_latest_drops'))
        {{-- LATEST DROPS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950 dark:text-white">Latest Drops</h2>
                <a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 dark:text-gray-300 hover:text-black dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
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
                        :quantity="$product['quantity'] ?? null"
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

    @if (feature_enabled('home_why_buy') && collect($promoCards)->contains(fn ($card) => $card['product']))
        {{-- PRODUCT PROMO CARDS (real products, admin-editable text) --}}
        @php
            $promoStyles = [
                1 => [
                    'gradient' => 'background: linear-gradient(135deg, #ad7f67 0%, #c6a38f 45%, #e8dedc 100%);',
                    'title' => 'promo-card-title text-gray-950',
                    'text' => 'text-gray-900',
                    'body' => 'text-xs mt-1.5 leading-snug',
                    'img' => 'h-[85%] w-2/5 object-contain p-2 group-hover:scale-105 transition-transform duration-300',
                ],
                2 => [
                    'gradient' => 'background: linear-gradient(135deg, #ebeef3 0%, #ccd6e2 100%);',
                    'title' => 'promo-card-title text-gray-950',
                    'text' => 'text-gray-900',
                    'body' => 'text-sm font-semibold mt-1 leading-snug',
                    'img' => 'h-[85%] w-2/5 object-contain p-2 group-hover:scale-105 transition-transform duration-300',
                ],
                3 => [
                    'gradient' => 'background: linear-gradient(135deg, #6283c4 0%, #85a2dc 50%, #c1d3f7 100%);',
                    'title' => 'promo-card-title text-gray-950',
                    'text' => 'text-gray-900',
                    'body' => 'text-xs mt-1.5 leading-snug',
                    'img' => 'h-[85%] w-2/5 object-contain p-2 group-hover:scale-105 transition-transform duration-300',
                ],
            ];
        @endphp
        <section class="pb-6 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach ($promoCards as $n => $card)
                    @continue(! $card['product'])
                    @php
                        $style = $promoStyles[$n] ?? [];
                        $bgStart = $card['bg_color_start'] ?? ($n === 1 ? '#ad7f67' : ($n === 2 ? '#ebeef3' : '#6283c4'));
                        $bgEnd = $card['bg_color_end'] ?? ($n === 1 ? '#e8dedc' : ($n === 2 ? '#ccd6e2' : '#c1d3f7'));
                        $gradientStyle = "background: linear-gradient(135deg, {$bgStart} 0%, {$bgEnd} 100%);";
                        $titleColor = $card['title_color'] ?? '#030712';
                        $textColor = $card['text_color'] ?? '#1f2937';
                        $btnBg = $card['button_bg_color'] ?? null;
                        $btnText = $card['button_text_color'] ?? null;
                    @endphp
                    <a href="{{ route('products.show', $card['product']) }}" title="{{ $card['product']->title }}" style="{{ $gradientStyle }}" class="group relative flex items-center justify-between gap-3 h-44 rounded-xl overflow-hidden px-6 py-5 shadow-2xs hover:shadow-lg transition-shadow">
                        <div class="relative z-10 max-w-[58%] h-full flex flex-col justify-between py-1">
                            <div>
                                <h3 class="text-xl font-extrabold leading-tight promo-card-title" style="color: {{ $titleColor }};">{!! nl2br(e($card['title'])) !!}</h3>
                                <p class="{{ $style['body'] ?? 'text-xs mt-1.5 leading-snug' }}" style="color: {{ $textColor }};">{!! nl2br(e($card['text'])) !!}</p>
                            </div>
                            <div class="pt-2">
                                <span class="inline-flex items-center gap-2 promo-card-btn text-xs font-bold px-4 py-2 rounded-md transition-colors w-fit" @if($btnBg || $btnText) style="{{ $btnBg ? "background-color: {$btnBg} !important;" : '' }} {{ $btnText ? "color: {$btnText} !important;" : '' }}" @endif>{{ $card['button'] }} <span>→</span></span>
                            </div>
                        </div>
                        @php
                            $promoImage = $card['product']->primaryImageUrl();
                            if ($n === 3 && str_contains($promoImage, '/image/upload/')) {
                                $promoImage = str_replace('/image/upload/', '/image/upload/e_make_transparent:15/', $promoImage);
                            }
                        @endphp
                        <img src="{{ $promoImage }}" alt="{{ $card['product']->title }}" class="relative z-10 {{ $style['img'] }}" loading="lazy">
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
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">⚡ Certified Refurbished</h2>
                    <span class="bg-gray-50 dark:bg-slate-800 text-gray-900 dark:text-gray-200 text-[11px] font-bold px-2.5 py-1 rounded-md border border-gray-200 dark:border-slate-700">Save up to 40%</span>
                </div>
                <a href="{{ Route::has('shop') ? route('shop', ['condition' => 'refurbished']) : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 dark:text-gray-300 hover:text-black dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
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
                        :quantity="$product['quantity'] ?? null"
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
        <section class="py-14 bg-white dark:bg-[#0b0f19]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <h2 class="text-2xl font-extrabold text-gray-950 dark:text-white mb-2">The Openbox Guarantee</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Shop pre-owned and new electronics with 100% confidence and zero hassle.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- 100% Inspected --}}
                    <div class="bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-slate-700 mb-4">
                            Quality Guaranteed
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">Certified Diagnostic Check</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 line-clamp-4">
                            Every device undergoes thorough hardware and component inspections to ensure pristine operational condition.
                        </p>
                        <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1.5 border-t border-gray-50 dark:border-slate-700/60 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> Multi-point diagnostic testing</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> Battery health verification</li>
                        </ul>
                    </div>

                    {{-- 7-Day Return --}}
                    <div class="bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-slate-700 mb-4">
                            Zero Risk Policy
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">7-Day Replacement</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 line-clamp-4">
                            If your item doesn't match its listing description, return it within 7 days for a full refund or direct exchange.
                        </p>
                        <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1.5 border-t border-gray-50 dark:border-slate-700/60 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> Fast and easy return pickup</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> Instant escrow refund processing</li>
                        </ul>
                    </div>

                    {{-- Secure Escrow --}}
                    <div class="bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 rounded-2xl p-6 shadow-2xs hover:shadow-md transition-all">
                        <div class="inline-flex items-center px-5 py-2 rounded-lg text-xs font-semibold bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-slate-700 mb-4">
                            Safe &amp; Secure
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white mb-2">Escrow Protected Checkout</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 line-clamp-4">
                            Your payment is held safely in escrow and only released to the seller after you receive and inspect your package.
                        </p>
                        <ul class="text-xs text-gray-500 dark:text-gray-400 space-y-1.5 border-t border-gray-50 dark:border-slate-700/60 pt-3">
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> 100% payment protection</li>
                            <li class="flex items-center gap-1.5"><span class="text-gray-900 dark:text-amber-400 font-bold">✓</span> Doorstep delivery nationwide</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_mobile_tech'))
        {{-- USED PRODUCTS --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-950 dark:text-white">Used</h2>
                <a href="{{ Route::has('shop') ? route('shop', ['condition' => 'used']) : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 dark:text-gray-300 hover:text-black dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
                    <span>View All</span>
                    <span>→</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($usedProducts as $product)
                    <x-product-card
                        :id="$product['id'] ?? null"
                        :title="$product['title']"
                        :category="$product['category'] ?? $product['brand'] ?? 'Electronics'"
                        :brand="$product['brand']"
                        :price="$product['price']"
                        :compare-price="$product['comparePrice'] ?? null"
                        :quantity="$product['quantity'] ?? null"
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

    @if (feature_enabled('home_start_selling_cta'))
        {{-- START SELLING CTA BANNER (admin: Settings → Start Selling) --}}
        @php
            $sell = \App\Support\HomeContent::startSelling();
            $safeLink = fn (?string $link, string $fallback): string => match (true) {
                blank($link) => $fallback,
                str_starts_with($link, '/') && ! str_starts_with($link, '//') => url($link),
                (bool) preg_match('#^https?://#i', $link) => $link,
                default => $fallback,
            };
            $primaryHref = $safeLink($sell['primary_url'], Route::has('register.saler') ? route('register.saler') : (Route::has('register') ? route('register') : '#'));
            $secondaryHref = $safeLink($sell['secondary_url'], Route::has('shop') ? route('shop') : '#');
            $sizeStyle = fn (?int $size, int $vw): ?string => $size ? 'font-size:clamp('.round($size * 0.6).'px,'.$vw.'vw,'.$size.'px)' : null;
            $hasPhone = filled($sell['phone_image']);
            $hasStores = filled($sell['ios_url']) || filled($sell['android_url']);
            $hasApp = filled($sell['qr_image']) || $hasStores;
            $isRich = $hasPhone || $hasApp;
        @endphp
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="relative bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-700 rounded-3xl p-8 sm:p-12 shadow-2xs overflow-hidden">
                <div @class([
                    'relative items-center',
                    'max-w-2xl mx-auto text-center' => ! $isRich,
                    'grid grid-cols-1 gap-10 lg:gap-8 text-center lg:text-left' => $isRich,
                    'lg:grid-cols-[1.25fr_auto_1fr]' => $hasPhone && $hasApp,
                    'lg:grid-cols-[1.4fr_auto]' => $hasPhone && ! $hasApp,
                    'lg:grid-cols-[1.4fr_1fr]' => ! $hasPhone && $hasApp,
                ])>
                    <div>
                        <h2 class="text-2xl sm:text-3xl lg:text-6xl font-black text-gray-950 dark:text-white mb-4 tracking-tight leading-[1.05]" style="{{ $sizeStyle($sell['title_size'], 5) }}">{{ $sell['title'] }}</h2>
                        <p class="text-gray-600 dark:text-gray-300 text-xs sm:text-sm lg:text-lg leading-relaxed mb-8" style="{{ $sizeStyle($sell['text_size'], 3) }}">{{ $sell['text'] }}</p>

                        <div @class(['flex flex-col sm:flex-row items-center gap-4', 'justify-center' => ! $isRich, 'justify-center lg:justify-start' => $isRich])>
                            <a href="{{ $primaryHref }}" class="w-full sm:w-auto brand-btn-primary font-bold text-sm px-8 py-3.5 rounded-xl shadow-xs transition-all inline-flex items-center justify-center gap-2">{{ $sell['primary_label'] }} <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6l6 6-6 6"/></svg></a>
                            <a href="{{ $secondaryHref }}" class="w-full sm:w-auto bg-white dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700 text-gray-900 dark:text-white font-semibold text-sm px-7 py-3.5 rounded-xl border border-gray-200 dark:border-slate-600 shadow-2xs transition-all inline-flex items-center justify-center gap-2">{{ $sell['secondary_label'] }}</a>
                        </div>
                    </div>

                    @if ($hasPhone)
                        <div @class(['flex justify-center', 'lg:border-l lg:border-gray-200 dark:lg:border-slate-700 lg:pl-8' => true])>
                            <img src="{{ $sell['phone_image'] }}" alt="{{ config('app.name') }} mobile app" loading="lazy" class="h-80 sm:h-96 lg:h-[26rem] w-auto object-contain drop-shadow-xl">
                        </div>
                    @endif

                    @if ($hasApp)
                        <div @class(['flex flex-col items-center text-center', 'lg:border-l lg:border-gray-200 dark:lg:border-slate-700 lg:pl-8' => ! $hasPhone])>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-gray-950 dark:text-white mb-1 text-center">{{ $sell['app_heading'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 text-center">{{ $sell['app_text'] }}</p>
                            @if (filled($sell['qr_image']))
                                <div class="w-full flex flex-col items-center justify-center text-center my-1">
                                    <div class="bg-white rounded-xl p-2 border border-gray-100 shadow-2xs mb-1 inline-block">
                                        <img src="{{ $sell['qr_image'] }}" alt="QR code to download the app" loading="lazy" class="w-28 h-28 sm:w-32 sm:h-32 object-contain mx-auto">
                                    </div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mb-4 text-center block">{{ $sell['qr_caption'] }}</span>
                                </div>
                            @endif

                            @if ($hasStores)
                                <div class="w-full flex flex-wrap justify-center lg:justify-center gap-3">
                                    @if (filled($sell['ios_url']))
                                        <a href="{{ $sell['ios_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2.5 bg-black text-white rounded-xl px-4 py-2.5 hover:bg-gray-800 transition-colors">
                                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.37 12.62c-.02-2.1 1.72-3.11 1.8-3.16-.98-1.43-2.5-1.63-3.05-1.65-1.3-.13-2.54.77-3.2.77-.66 0-1.67-.75-2.75-.73-1.41.02-2.72.82-3.45 2.08-1.47 2.55-.38 6.32 1.06 8.39.7 1.01 1.54 2.15 2.64 2.11 1.06-.04 1.46-.69 2.74-.69 1.28 0 1.64.69 2.76.67 1.14-.02 1.86-1.03 2.55-2.05.8-1.18 1.13-2.32 1.15-2.38-.03-.01-2.2-.84-2.25-3.36zM14.28 6.43c.58-.71.98-1.69.87-2.67-.84.03-1.86.56-2.46 1.26-.54.62-1.01 1.62-.88 2.58.94.07 1.89-.47 2.47-1.17z"/></svg>
                                            <span class="text-left leading-tight"><span class="block text-[10px]">{{ $sell['ios_subtitle'] ?: 'Download on the' }}</span><span class="block text-base font-semibold">{{ $sell['ios_title'] ?: 'App Store' }}</span></span>
                                        </a>
                                    @endif
                                    @if (filled($sell['android_url']))
                                        <a href="{{ $sell['android_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2.5 bg-black text-white rounded-xl px-4 py-2.5 hover:bg-gray-800 transition-colors">
                                            <svg class="w-6 h-6" viewBox="0 0 24 24" aria-hidden="true"><path fill="#00d7fe" d="M3.6 2.2c-.2.2-.3.6-.3 1v17.6c0 .4.1.8.3 1l.1.1 9.9-9.9v-.2L3.7 2.1z"/><path fill="#ffce00" d="M16.9 15.4l-3.3-3.3v-.2l3.3-3.3.1.1 3.9 2.2c1.1.6 1.1 1.7 0 2.4l-3.9 2.2z"/><path fill="#ff3a44" d="M17 15.3l-3.4-3.4-10 10c.4.4 1 .4 1.7.1z"/><path fill="#00f076" d="M17 8.5L5.3 1.9c-.7-.4-1.3-.3-1.7.1l10 10z"/></svg>
                                            <span class="text-left leading-tight"><span class="block text-[10px] uppercase">{{ $sell['android_subtitle'] ?: 'Get it on' }}</span><span class="block text-base font-semibold">{{ $sell['android_title'] ?: 'Google Play' }}</span></span>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    @if (feature_enabled('home_top_vendors'))
        {{-- TOP RATED VENDORS (Scrollable Carousel) --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6 overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">Top Rated Vendors</h2>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Scroll navigation buttons --}}
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            id="vendors-scroll-prev"
                            aria-label="Previous vendors"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 dark:hover:border-amber-500 flex items-center justify-center transition-all cursor-pointer shadow-2xs hover:scale-105"
                        >
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button
                            type="button"
                            id="vendors-scroll-next"
                            aria-label="Next vendors"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 dark:hover:border-amber-500 flex items-center justify-center transition-all cursor-pointer shadow-2xs hover:scale-105"
                        >
                            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>

                    <a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="text-xs sm:text-sm font-bold text-gray-950 dark:text-gray-300 hover:text-black dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
                        <span>View All</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            @php
                $displayVendors = collect($vendors)->values();
                while ($displayVendors->count() < 10 && $displayVendors->isNotEmpty()) {
                    $displayVendors = $displayVendors->concat($vendors);
                }
                $vendorDuration = max(24, $displayVendors->count() * 3.5);
            @endphp

            <div class="relative overflow-hidden py-2" id="vendors-viewport">
                {{-- Left & Right Fade Masks --}}
                <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-4 sm:w-8 z-10 bg-gradient-to-r from-white dark:from-[#0b0f19] to-transparent"></div>
                <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-4 sm:w-8 z-10 bg-gradient-to-l from-white dark:from-[#0b0f19] to-transparent"></div>

                {{-- Continuous Auto-Scrolling Track --}}
                <div
                    id="vendors-track"
                    class="vendors-marquee-track flex items-stretch gap-4 w-max select-none py-1"
                    style="--scroll-duration: {{ $vendorDuration }}s;"
                >
                    {{-- Set 1 --}}
                    @foreach ($displayVendors as $vendor)
                        <a
                            href="{{ $vendor['storeUrl'] ?? (Route::has('stores.index') ? route('stores.index') : '#') }}"
                            class="group shrink-0 w-[155px] sm:w-[185px] bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all cursor-pointer block"
                        >
                            <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 overflow-hidden flex items-center justify-center text-sm mb-2.5 shadow-2xs group-hover:scale-105 transition-transform shrink-0">
                                @if (!empty($vendor['avatar']))
                                    <img
                                        src="{{ $vendor['avatar'] }}"
                                        alt="{{ $vendor['name'] }}"
                                        class="w-full h-full object-cover object-top"
                                        style="object-position: top center !important;"
                                        loading="lazy"
                                    >
                                @else
                                    <span class="font-medium text-gray-800 dark:text-white text-sm">
                                        {{ strtoupper(substr($vendor['name'], 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-100 line-clamp-1 group-hover:text-black transition-colors w-full">{{ $vendor['name'] }}</span>
                            <div class="flex items-center gap-1 mt-1 text-[11px] text-gray-600 dark:text-amber-400 font-normal">
                                <span class="text-amber-500">★</span>
                                <span>{{ number_format((float) ($vendor['rating'] ?? 4.9), 1) }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-normal">{{ $vendor['salesCount'] ?? '120' }}+ sales</span>
                        </a>
                    @endforeach

                    {{-- Set 2 (Duplicate for continuous loop) --}}
                    @foreach ($displayVendors as $vendor)
                        <a
                            href="{{ $vendor['storeUrl'] ?? (Route::has('stores.index') ? route('stores.index') : '#') }}"
                            class="group shrink-0 w-[155px] sm:w-[185px] bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 rounded-2xl p-4 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all cursor-pointer block"
                            aria-hidden="true"
                        >
                            <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 overflow-hidden flex items-center justify-center text-sm mb-2.5 shadow-2xs group-hover:scale-105 transition-transform shrink-0">
                                @if (!empty($vendor['avatar']))
                                    <img
                                        src="{{ $vendor['avatar'] }}"
                                        alt="{{ $vendor['name'] }}"
                                        class="w-full h-full object-cover object-top"
                                        style="object-position: top center !important;"
                                        loading="lazy"
                                    >
                                @else
                                    <span class="font-medium text-gray-800 dark:text-white text-sm">
                                        {{ strtoupper(substr($vendor['name'], 0, 2)) }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-xs font-medium text-gray-800 dark:text-gray-100 line-clamp-1 group-hover:text-black transition-colors w-full">{{ $vendor['name'] }}</span>
                            <div class="flex items-center gap-1 mt-1 text-[11px] text-gray-600 dark:text-amber-400 font-normal">
                                <span class="text-amber-500">★</span>
                                <span>{{ number_format((float) ($vendor['rating'] ?? 4.9), 1) }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-normal">{{ $vendor['salesCount'] ?? '120' }}+ sales</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <style>
            @keyframes topVendorsScroll {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(-50%);
                }
            }
            .vendors-marquee-track {
                animation: topVendorsScroll var(--scroll-duration, 30s) linear infinite;
                will-change: transform;
            }
            .vendors-marquee-track:hover,
            .vendors-marquee-track:focus-within {
                animation-play-state: paused !important;
            }
            @media (prefers-reduced-motion: reduce) {
                .vendors-marquee-track {
                    animation: none;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const track = document.getElementById('vendors-track');
                const prevBtn = document.getElementById('vendors-scroll-prev');
                const nextBtn = document.getElementById('vendors-scroll-next');

                if (!track) return;

                let manualOffset = 0;
                const cardStep = 200;

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        manualOffset += cardStep;
                        track.style.transition = 'margin-left 0.4s ease';
                        track.style.marginLeft = manualOffset + 'px';
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        manualOffset -= cardStep;
                        track.style.transition = 'margin-left 0.4s ease';
                        track.style.marginLeft = manualOffset + 'px';
                    });
                }
            });
        </script>
    @endif

    @if (feature_enabled('home_reviews') && $reviews->isNotEmpty())
        {{-- WHAT OUR COMMUNITY SAYS (Automatically Scrollable) --}}
        <section class="py-14 bg-white dark:bg-[#0b0f19] overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 mb-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 text-center sm:text-left">
                    <div>
                        <h2 class="text-2xl font-extrabold text-gray-950 dark:text-white mb-2">What Our Community Says</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Real feedback from verified buyers and sellers nationwide.</p>
                    </div>

                    <div class="hidden sm:flex items-center gap-2 self-center sm:self-end">
                        <button
                            type="button"
                            id="reviews-scroll-prev"
                            aria-label="Previous review"
                            class="w-8 h-8 rounded-full border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 dark:hover:border-amber-500 flex items-center justify-center transition-all cursor-pointer shadow-2xs hover:scale-105"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button
                            type="button"
                            id="reviews-scroll-next"
                            aria-label="Next review"
                            class="w-8 h-8 rounded-full border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-600 dark:text-gray-300 hover:text-amber-600 hover:border-amber-300 dark:hover:border-amber-500 flex items-center justify-center transition-all cursor-pointer shadow-2xs hover:scale-105"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            @php
                $displayReviews = $reviews->values();
                // Ensure sufficient items for seamless infinite auto-scroll
                while ($displayReviews->count() < 8 && $displayReviews->isNotEmpty()) {
                    $displayReviews = $displayReviews->concat($reviews);
                }
                $duration = max(30, $displayReviews->count() * 4.5);
            @endphp

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 overflow-hidden py-2" id="reviews-viewport" style="container-type: inline-size;">
                {{-- Left & Right Fade Masks --}}
                <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-4 sm:w-6 z-10 bg-gradient-to-r from-white dark:from-[#0b0f19] to-transparent"></div>
                <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-4 sm:w-6 z-10 bg-gradient-to-l from-white dark:from-[#0b0f19] to-transparent"></div>

                {{-- Continuous Auto-Scrolling Track --}}
                <div
                    id="reviews-track"
                    class="reviews-marquee-track flex items-stretch gap-4 w-max select-none"
                    style="--scroll-duration: {{ $duration }}s;"
                >
                    {{-- Set 1 --}}
                    @foreach ($displayReviews as $review)
                        <div class="review-card h-[208px] shrink-0 bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 rounded-2xl p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition-shadow">
                            <div>
                                {{-- Stars --}}
                                <div class="flex items-center gap-1 text-amber-400 mb-3">
                                    @for ($i = 0; $i < ($review['rating'] ?? 5); $i++)
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
                                    @endfor
                                    @for ($i = ($review['rating'] ?? 5); $i < 5; $i++)
                                        <svg class="w-4 h-4 text-gray-200 dark:text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
                                    @endfor
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 line-clamp-4">
                                    &ldquo;{{ $review['body'] }}&rdquo;
                                </p>
                            </div>

                            <div class="border-t border-gray-50 dark:border-slate-700/60 pt-3 flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $review['name'] }}">{{ Str::before(trim($review['name'] ?? ''), ' ') ?: ($review['name'] ?? 'Customer') }}</span>
                                <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full shrink-0 whitespace-nowrap">Verified Buyer</span>
                            </div>
                        </div>
                    @endforeach

                    {{-- Set 2 (Duplicate for continuous loop) --}}
                    @foreach ($displayReviews as $review)
                        <div class="review-card h-[208px] shrink-0 bg-white dark:bg-slate-800/90 border border-gray-100 dark:border-slate-700 rounded-2xl p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition-shadow" aria-hidden="true">
                            <div>
                                {{-- Stars --}}
                                <div class="flex items-center gap-1 text-amber-400 mb-3">
                                    @for ($i = 0; $i < ($review['rating'] ?? 5); $i++)
                                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
                                    @endfor
                                    @for ($i = ($review['rating'] ?? 5); $i < 5; $i++)
                                        <svg class="w-4 h-4 text-gray-200 dark:text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
                                    @endfor
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 line-clamp-4">
                                    &ldquo;{{ $review['body'] }}&rdquo;
                                </p>
                            </div>

                            <div class="border-t border-gray-50 dark:border-slate-700/60 pt-3 flex items-center justify-between gap-2">
                                <span class="text-xs font-bold text-gray-900 dark:text-white truncate" title="{{ $review['name'] }}">{{ Str::before(trim($review['name'] ?? ''), ' ') ?: ($review['name'] ?? 'Customer') }}</span>
                                <span class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-full shrink-0 whitespace-nowrap">Verified Buyer</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <style>
            @keyframes communityReviewsScroll {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(-50%);
                }
            }
            #reviews-viewport .review-card {
                width: calc((100cqw - 1rem) / 2);
            }
            @media (min-width: 640px) {
                #reviews-viewport .review-card {
                    width: calc((100cqw - 3rem) / 4);
                }
            }
            .reviews-marquee-track {
                animation: communityReviewsScroll var(--scroll-duration, 40s) linear infinite;
                will-change: transform;
            }
            .reviews-marquee-track:hover,
            .reviews-marquee-track:focus-within {
                animation-play-state: paused !important;
            }
            @media (prefers-reduced-motion: reduce) {
                .reviews-marquee-track {
                    animation: none;
                }
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const track = document.getElementById('reviews-track');
                const prevBtn = document.getElementById('reviews-scroll-prev');
                const nextBtn = document.getElementById('reviews-scroll-next');

                if (!track) return;

                let manualOffset = 0;
                const cardStep = 320 + 16;

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        manualOffset += cardStep;
                        track.style.transition = 'margin-left 0.4s ease';
                        track.style.marginLeft = manualOffset + 'px';
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        manualOffset -= cardStep;
                        track.style.transition = 'margin-left 0.4s ease';
                        track.style.marginLeft = manualOffset + 'px';
                    });
                }
            });
        </script>
    @endif

    @if (feature_enabled('home_popular_searches'))
        {{-- POPULAR SEARCHES --}}
        <section class="py-10 max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Popular Searches</h3>
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
                    <a href="{{ Route::has('search') ? route('search', ['q' => $tag]) : '#' }}" class="bg-white dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-slate-700 hover:text-amber-800 dark:hover:text-amber-400 hover:border-amber-300 dark:hover:border-amber-400 border border-gray-200 dark:border-slate-700 text-xs font-medium text-gray-700 dark:text-gray-300 px-5 py-2 rounded-lg shadow-2xs transition-colors">
                        {{ $tag }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if (feature_enabled('home_newsletter'))
        {{-- STAY UPDATED (NEWSLETTER) --}}
        <section class="pt-8 pb-5 max-w-7xl mx-auto px-4 sm:px-6">
            <div class="rounded-lg newsletter-brand-banner px-6 sm:px-10 py-5 flex flex-col lg:flex-row lg:items-center gap-5 lg:gap-10">
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
                        class="flex-1 bg-white dark:bg-slate-900 border-0 rounded-md px-4 py-3 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:focus:ring-amber-500"
                        required
                    >
                    <button
                        type="submit"
                        style="color: {{ $newsletter['button_text_color'] ?? '#ffffff' }} !important; background-color: {{ $newsletter['button_bg_color'] ?? '#090d16' }} !important;"
                        class="font-bold px-8 py-3 rounded-md text-sm transition-opacity hover:opacity-90 shadow-sm shrink-0"
                    >
                        {{ $newsletter['button'] }}
                    </button>
                </form>
            </div>
        </section>
    @endif

@endsection
