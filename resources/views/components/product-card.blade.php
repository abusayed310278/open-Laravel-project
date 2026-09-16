@props([
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
])

@php
    $categoryHeading = $category ?: ($brand ?: 'Electronics');
    $savings = ($comparePrice && $comparePrice > $price) ? ($comparePrice - $price) : null;
@endphp

<div class="group bg-white border border-gray-100 hover:border-gray-200 rounded-2xl p-4 sm:p-5 flex flex-col items-center text-center shadow-2xs hover:shadow-md transition-all duration-200 h-full">
    {{-- Product Image --}}
    <a href="{{ $href }}" class="w-full h-40 sm:h-44 flex items-center justify-center p-2 mb-3 shrink-0 overflow-hidden">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $title }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-200" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
            <div class="hidden w-full h-full flex items-center justify-center text-gray-300">
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

    {{-- Category Heading (Bold, Centered) --}}
    <h3 class="font-bold text-gray-950 text-sm sm:text-base leading-snug tracking-tight mb-1 text-center w-full truncate">
        {{ $categoryHeading }}
    </h3>

    {{-- Product Title (Centered, 2 lines clamp) --}}
    <a href="{{ $href }}" class="text-xs sm:text-[13px] text-gray-800 hover:text-brand-600 line-clamp-2 leading-relaxed text-center mb-2 transition-colors">
        {{ $title }}
    </a>

    {{-- Price & Promo --}}
    <div class="mt-auto flex flex-col items-center justify-center w-full pt-1">
        <span class="text-sm sm:text-base font-bold text-gray-950 text-center font-mono">
            Tk {{ number_format($price) }}
        </span>

        @if ($savings && $savings > 0)
            <span class="text-[11px] sm:text-xs font-semibold text-purple-700 mt-1 text-center leading-tight">
                Save Extra Tk {{ number_format($savings) }} on various offer
            </span>
        @endif
    </div>
</div>
