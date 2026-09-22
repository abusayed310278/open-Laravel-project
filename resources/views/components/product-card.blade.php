@props([
    'id' => null,
    'title',
    'category' => null,
    'brand' => null,
    'price',
    'comparePrice' => null,
    'rating' => null,
    'ratingCount' => null,
    'location' => null,
    'condition' => null,
    'keyFeatures' => [],
    'seller' => null,
    'isNew' => false,
    'image' => null,
    'href' => '#',
    'isVerified' => null,
    'isAdminApproved' => null,
    'product' => null,
])

@php
    $categoryHeading = $category ?: ($brand ?: 'Electronics');
    $discountPercent = ($comparePrice && $comparePrice > $price)
        ? (int) round((($comparePrice - $price) / $comparePrice) * 100)
        : null;
    $description = $keyFeatures instanceof \Illuminate\Support\Collection ? $keyFeatures->all() : $keyFeatures;
    $description = $description ? implode(', ', $description) : null;

    // Resolve Verifier Badge (1st Badge — Physical Verifier Inspection)
    $showVerifierBadge = true;
    if ($isVerified !== null) {
        $showVerifierBadge = (bool) $isVerified;
    } elseif ($product instanceof \App\Models\Product) {
        $showVerifierBadge = $product->isVerified() || $product->verification_status?->value === 'verified' || $product->verification_status === 'verified';
    }

    // Resolve Openbox Admin Badge (2nd Badge — Admin Approval)
    $showAdminBadge = true;
    if ($isAdminApproved !== null) {
        $showAdminBadge = (bool) $isAdminApproved;
    } elseif ($product instanceof \App\Models\Product) {
        $showAdminBadge = $product->approval_status === \App\Enums\ProductApprovalStatus::Approved || $product->approval_status?->value === 'approved' || $product->approval_status === 'approved';
    }
@endphp

<div class="group bg-white border border-gray-100 hover:border-gray-200 rounded-2xl overflow-hidden flex flex-col text-left shadow-2xs hover:shadow-md transition-all duration-200 h-full">
    {{-- Product Image --}}
    <div class="relative w-full h-72 sm:h-80 shrink-0 overflow-hidden bg-gray-50">
        @if ($discountPercent)
            <span class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full leading-none">-{{ $discountPercent }}%</span>
        @endif

        <a href="{{ $href }}" class="absolute inset-0 flex items-center justify-center">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                <div class="hidden w-full h-full items-center justify-center text-gray-300">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                    </svg>
                </div>
            @else
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                </svg>
            @endif
        </a>

        {{-- Hover Actions: Wishlist & Share --}}
        <div class="absolute top-2 right-2 z-20 flex flex-col gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity duration-200">
            <button
                type="button"
                data-wishlist-id="{{ $id }}"
                class="js-wishlist-btn w-8 h-8 rounded-full bg-white/95 hover:bg-white shadow-md flex items-center justify-center text-gray-600 hover:text-red-500 transition-colors cursor-pointer"
                title="Save to wishlist"
            >
                <svg class="w-4 h-4 js-wishlist-icon" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
            <button
                type="button"
                data-share-url="{{ $href }}"
                data-share-title="{{ $title }}"
                class="js-share-btn w-8 h-8 rounded-full bg-white/95 hover:bg-white shadow-md flex items-center justify-center text-gray-600 hover:text-brand-600 transition-colors cursor-pointer"
                title="Share"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="18" cy="5" r="2.5" />
                    <circle cx="6" cy="12" r="2.5" />
                    <circle cx="18" cy="19" r="2.5" />
                    <line x1="8.2" y1="13.4" x2="15.8" y2="17.6" />
                    <line x1="15.8" y1="6.4" x2="8.2" y2="10.6" />
                </svg>
            </button>
        </div>
    </div>

    <div class="p-3 sm:p-4 pt-2.5 flex flex-col grow">

    {{-- New Badge --}}
    @if ($isNew)
        <span class="self-start text-emerald-600 bg-white border border-emerald-200 text-[10px] font-semibold px-2 py-0.5 rounded-full leading-none mb-2">New</span>
    @endif

    {{-- Category Heading --}}
    <span class="text-[10px] sm:text-[11px] font-bold text-brand-600 uppercase tracking-wide mb-1">
        {{ $categoryHeading }}
    </span>

    {{-- Product Title --}}
    <a href="{{ $href }}" class="text-sm font-bold text-gray-950 hover:text-brand-600 leading-snug mb-1 transition-colors truncate">
        {{ $title }}
    </a>

    {{-- Description --}}
    @if ($description)
        <p class="text-xs text-gray-500 line-clamp-1 mb-2">
            {{ $description }}
        </p>
    @endif

    {{-- Price --}}
    <div class="flex items-baseline gap-2 mb-1.5">
        <span class="text-sm sm:text-base font-bold text-gray-950 font-mono">
            Tk {{ number_format($price) }}
        </span>
        @if ($discountPercent)
            <span class="text-xs text-gray-400 line-through font-mono">
                Tk {{ number_format($comparePrice) }}
            </span>
        @endif
    </div>

    {{-- Rating & Trust Badges (1st: Physical Verifier Badge, 2nd: Openbox Admin Badge) --}}
    @if ($rating || $showVerifierBadge || $showAdminBadge)
        <div class="flex items-center gap-1.5 mb-3">
            @if ($rating)
                <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20">
                    <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
                </svg>
                <span class="text-xs font-semibold text-gray-800">{{ number_format($rating, 1) }}</span>
                @if ($ratingCount)
                    <span class="text-xs text-gray-400">({{ $ratingCount }})</span>
                @endif
            @endif

            {{-- 1st Badge: Verifier Badge (Blue Checkmark) --}}
            @if ($showVerifierBadge)
                <span class="inline-flex text-blue-500 shrink-0" title="Verifier Checked (Physical Inspection Verified)">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif

            {{-- 2nd Badge: Openbox Admin Badge (Green Checkmark) --}}
            @if ($showAdminBadge)
                <span class="inline-flex text-emerald-500 shrink-0" title="Openbox Admin Approved">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif
        </div>
    @endif


    {{-- Seller & Add to Cart --}}
    <div class="mt-auto pt-2.5 flex items-center justify-between gap-2">
        <span class="text-xs text-gray-500 truncate">{{ $seller }}</span>
        <button type="button" data-product-id="{{ $id }}" class="js-add-to-cart shrink-0 inline-flex items-center gap-1 border border-brand-500 bg-white hover:bg-brand-500 text-brand-600 hover:text-white text-xs font-semibold px-3 py-1.5 rounded-full transition-all active:scale-95 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="js-btn-text">Add</span>
        </button>
    </div>

    </div>
</div>
