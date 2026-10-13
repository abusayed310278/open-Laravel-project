@php
    $activePageSlugs = \Illuminate\Support\Facades\Cache::remember('published_page_slugs', 3600, function () {
        return \App\Models\Page::published()->pluck('slug')->all();
    });
@endphp

<footer style="background-color: #0c0f1d; color: #94a3b8;" class="text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-2 md:grid-cols-5 gap-8 lg:gap-10">
        {{-- Brand & About --}}
        <div class="col-span-2 md:col-span-1">
            <a href="{{ route('home') }}" class="inline-block mb-4">
                <x-brand-logo :dark="true" :footer="true" />
            </a>
            <p class="text-xs text-gray-400 leading-relaxed mb-4">
                {{ setting('brand_footer_description') ?: "Bangladesh's premier verified electronics marketplace. Every device inspected, graded, and warehoused for authentic peace of mind." }}
            </p>
            <p class="text-xs text-gray-500 font-medium">
                {{ setting('brand_footer_address') ?: 'Dhaka, Bangladesh' }}
            </p>
        </div>

        {{-- Marketplace --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Marketplace</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="{{ Route::has('shop') ? route('shop') : '#' }}" class="hover:text-amber-400 transition-colors">Shop All</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['category' => 'phones']) : '#' }}" class="hover:text-amber-400 transition-colors">Phones</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['category' => 'laptops']) : '#' }}" class="hover:text-amber-400 transition-colors">Laptops</a></li>
                <li><a href="{{ Route::has('shop') ? route('shop', ['condition' => 'refurbished']) : '#' }}" class="hover:text-amber-400 transition-colors">Refurbished Deals</a></li>
                <li><a href="{{ Route::has('grading-system') ? route('grading-system') : '#' }}" class="hover:text-amber-400 transition-colors">Grading Guide</a></li>
            </ul>
        </div>

        {{-- Sell --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Sell</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                <li><a href="{{ Route::has('register.saler') ? route('register.saler') : (Route::has('register') ? route('register') : '#') }}" class="hover:text-amber-400 transition-colors">Sell as Saler</a></li>
                <li><a href="{{ Route::has('register.business') ? route('register.business') : (Route::has('register') ? route('register') : '#') }}" class="hover:text-amber-400 transition-colors">Sell as Business</a></li>
                <li><a href="{{ Route::has('stores.index') ? route('stores.index') : '#' }}" class="hover:text-amber-400 transition-colors">Browse Stores</a></li>
                @if (in_array('warehouse-custody', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'warehouse-custody') }}" class="hover:text-amber-400 transition-colors">Warehouse Custody</a></li>
                @endif
                @if (in_array('fee-calculator', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'fee-calculator') }}" class="hover:text-amber-400 transition-colors">Fee Calculator</a></li>
                @endif
            </ul>
        </div>

        {{-- Support --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Support</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                @if (in_array('help-center', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'help-center') }}" class="hover:text-amber-400 transition-colors">Help Center</a></li>
                @endif
                @if (in_array('track-order', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'track-order') }}" class="hover:text-amber-400 transition-colors">Track Order</a></li>
                @endif
                @if (in_array('returns-refund-policy', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'returns-refund-policy') }}" class="hover:text-amber-400 transition-colors">Returns &amp; Refund Policy</a></li>
                @endif
                @if (in_array('escrow-protection', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'escrow-protection') }}" class="hover:text-amber-400 transition-colors">Escrow Protection</a></li>
                @endif
                @if (in_array('contact-support', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'contact-support') }}" class="hover:text-amber-400 transition-colors">Contact Support</a></li>
                @endif
            </ul>
        </div>

        {{-- Company --}}
        <div>
            <h3 class="text-white text-xs font-bold uppercase tracking-wider mb-4" style="color: #ffffff;">Company</h3>
            <ul class="space-y-2.5 text-xs text-gray-400">
                @if (in_array('about-openbox', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'about-openbox') }}" class="hover:text-amber-400 transition-colors">About Openbox</a></li>
                @endif
                @if (in_array('terms-of-service', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'terms-of-service') }}" class="hover:text-amber-400 transition-colors">Terms of Service</a></li>
                @endif
                @if (in_array('privacy-policy', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'privacy-policy') }}" class="hover:text-amber-400 transition-colors">Privacy Policy</a></li>
                @endif
                @if (in_array('delete-policy', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'delete-policy') }}" class="hover:text-amber-400 transition-colors">Delete Policy</a></li>
                @endif
                @if (in_array('trust-safety', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'trust-safety') }}" class="hover:text-amber-400 transition-colors">Trust &amp; Safety</a></li>
                @endif
                @if (in_array('careers', $activePageSlugs))
                    <li><a href="{{ route('pages.show', 'careers') }}" class="hover:text-amber-400 transition-colors">Careers</a></li>
                @endif
                @if (in_array('blog', $activePageSlugs))
                    <li><a href="{{ route('blog.index') }}" class="hover:text-amber-400 transition-colors">Blog</a></li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Dedicated App & Social Bar (Star Tech Style) --}}
    @php
        $footerAppHeading = setting('footer_app_heading') ?: ('Experience ' . config('app.name', 'Openbox') . ' App on your mobile:');
        $footerAppLinks = \App\Support\FooterAppLinks::getAppLinks();
        $footerSocialLinks = \App\Support\FooterAppLinks::getSocialLinks();
        $hasFooterAppOrSocial = !empty($footerAppLinks) || !empty($footerSocialLinks);
    @endphp

    @if ($hasFooterAppOrSocial)
    <div style="background-color: #080c18; border-top: 1px solid rgba(255, 255, 255, 0.08); border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5 flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Left: Heading / Label + App Badges --}}
            @if (!empty($footerAppLinks))
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:gap-4 text-center sm:text-left">
                    <span class="text-xs sm:text-sm text-gray-400 font-normal whitespace-nowrap">
                        {{ $footerAppHeading }}
                    </span>

                    <div class="flex items-center gap-2.5 flex-wrap justify-center">
                        @foreach ($footerAppLinks as $item)
                            <a
                                href="{{ $item['url'] }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-md bg-black/60 hover:bg-black/90 border border-white/20 hover:border-white/40 text-white transition-all shadow-xs group"
                                title="{{ $item['title'] }}"
                            >
                                <span class="text-white shrink-0 group-hover:scale-105 transition-transform">{!! $item['icon_svg'] !!}</span>
                                <span class="flex flex-col text-left leading-none">
                                    <span class="text-[9px] text-gray-400 font-normal leading-tight">{{ $item['subtitle'] }}</span>
                                    <span class="text-xs font-bold tracking-tight text-white mt-0.5 leading-tight">{{ $item['title'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div></div>
            @endif

            {{-- Right: Social Media Icons --}}
            @if (!empty($footerSocialLinks))
                <div class="flex items-center gap-2.5 sm:gap-3 flex-shrink-0">
                    @foreach ($footerSocialLinks as $item)
                        <a
                            href="{{ $item['url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-slate-800/90 hover:bg-slate-700 text-white flex items-center justify-center transition-all shadow-xs hover:scale-105"
                            title="{{ $item['name'] }}"
                        >
                            {!! $item['icon_svg'] !!}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Bottom Copyright Bar --}}
    <div style="background-color: #05070f;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div>
                <span>{!! setting('brand_footer_copyright') ?: ('&copy; ' . now()->year . ' ' . config('app.name', 'Openbox') . ' Ltd | All rights reserved') !!}</span>
            </div>
            <div>
                <span>{{ setting('brand_footer_powered_by') ?: ('Powered By: ' . config('app.name', 'Openbox')) }}</span>
            </div>
        </div>
    </div>
</footer>
