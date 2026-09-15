@props([
    'title',
    'brand' => 'Electronics',
    'price',
    'comparePrice' => null,
    'rating' => 4.9,
    'ratingCount' => 38,
    'location' => 'Dhaka',
    'condition' => 'used',
    'keyFeatures' => [],
    'seller' => null,
    'isNew' => false,
    'image' => null,
    'href' => '#',
])

@php
    $discountPercent = null;
    if ($comparePrice && $comparePrice > $price) {
        $discountPercent = round((($comparePrice - $price) / $comparePrice) * 100);
    }
@endphp

<div class="group relative bg-white border border-gray-100 rounded-2xl p-4 flex flex-col justify-between hover:border-amber-300 hover:shadow-xl hover:-translate-y-1 transition-all duration-200">
    <div>
        {{-- Image Box --}}
        <div class="relative aspect-[4/3] w-full bg-gray-50/70 rounded-xl flex items-center justify-center p-3 mb-3 overflow-hidden">
            {{-- Wishlist Button --}}
            <button type="button" class="absolute top-2.5 right-2.5 w-7 h-7 rounded-full bg-white/90 hover:bg-white text-gray-400 hover:text-red-500 shadow-xs flex items-center justify-center transition-colors z-10" title="Add to Wishlist">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
            </button>

            {{-- Product Image --}}
            <a href="{{ $href }}" class="w-full h-full flex items-center justify-center">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                @else
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                    </svg>
                @endif
            </a>
        </div>

        {{-- Brand & Seller --}}
        <div class="flex items-center justify-between gap-2 mb-1">
            @if ($brand)
                <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">{{ $brand }}</p>
            @endif

            @if ($seller)
                <span class="text-[11px] text-gray-400 truncate max-w-[120px]" title="Seller: {{ $seller }}">
                    {{ $seller }}
                </span>
            @endif
        </div>

        {{-- Title --}}
        <a href="{{ $href }}" class="block text-sm font-bold text-gray-900 hover:text-amber-600 line-clamp-2 leading-snug mt-0.5 transition-colors">
            {{ $title }}
        </a>

        {{-- Key Features --}}
        @if (!empty($keyFeatures))
            <ul class="my-2.5 space-y-1 text-xs text-gray-600 bg-gray-50/90 rounded-xl p-2.5 border border-gray-100">
                @foreach (array_slice($keyFeatures, 0, 3) as $feature)
                    <li class="flex items-start gap-1.5 leading-snug">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"></span>
                        <span class="line-clamp-1 font-medium text-[11.5px] text-gray-700">{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Ratings & Location --}}
        <div class="flex items-center gap-1.5 mt-2 text-xs text-gray-500">
            <span class="text-amber-500 font-bold flex items-center">★ {{ number_format((float) $rating, 1) }}</span>
            <span class="text-gray-400">({{ $ratingCount ?? 42 }})</span>
            <span class="text-gray-300">•</span>
            <span class="text-gray-400 text-[11px]">{{ $location ?? 'Dhaka' }}</span>
        </div>
    </div>

    {{-- Price & Details Footer --}}
    <div class="pt-3 mt-3 border-t border-gray-50 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-base sm:text-lg font-black text-gray-950">৳{{ number_format($price) }}</span>
                @if ($comparePrice && $comparePrice > $price)
                    <span class="text-xs text-gray-400 line-through">৳{{ number_format($comparePrice) }}</span>
                    @if ($discountPercent && $discountPercent > 0)
                        <span class="text-[11px] font-extrabold text-red-600 bg-red-50 border border-red-100 px-1.5 py-0.5 rounded-md">
                            -{{ $discountPercent }}%
                        </span>
                    @endif
                @endif
            </div>
        </div>

        <a href="{{ $href }}" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-0.5 group-hover:translate-x-0.5 transition-transform">
            <span>View</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
        </a>
    </div>
</div>
