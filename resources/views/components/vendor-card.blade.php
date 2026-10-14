@props(['name', 'image' => null, 'rating' => null, 'salesCount' => null, 'href' => '#'])

<a href="{{ $href }}" class="flex flex-col items-center gap-2.5 p-4 rounded-xl border border-gray-100 dark:border-slate-700 hover:border-gray-900 dark:hover:border-slate-500 text-center transition-all bg-white dark:bg-slate-800 shadow-2xs hover:shadow-md group">
    <div class="w-14 h-14 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-full overflow-hidden flex items-center justify-center text-gray-700 dark:text-gray-200 font-bold text-sm shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
        @if (!empty($image))
            <img
                src="{{ $image }}"
                alt="{{ $name }}"
                class="w-full h-full object-cover object-top"
                style="object-position: top center !important;"
                loading="lazy"
            >
        @else
            <span class="text-sm font-bold text-gray-800 dark:text-white">
                {{ strtoupper(substr($name, 0, 2)) }}
            </span>
        @endif
    </div>
    <span class="text-xs font-bold text-gray-900 dark:text-white line-clamp-1 group-hover:text-brand-600 transition-colors">{{ $name }}</span>
    @if ($rating)
        <span class="text-xs text-brand-500 font-semibold">★ {{ number_format($rating, 1) }}</span>
    @endif
    @if ($salesCount)
        <span class="text-[11px] text-gray-400">{{ $salesCount }} sales</span>
    @endif
</a>
