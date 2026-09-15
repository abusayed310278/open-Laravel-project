@props(['name', 'rating' => null, 'salesCount' => null, 'href' => '#'])

<a href="{{ $href }}" class="flex flex-col items-center gap-2 p-4 rounded-md border border-gray-100 hover:border-brand-300 text-center transition-colors">
    <div class="w-12 h-12 bg-gray-100 rounded-md flex items-center justify-center text-gray-500 font-semibold text-sm">
        {{ strtoupper(substr($name, 0, 2)) }}
    </div>
    <span class="text-xs font-medium text-gray-700">{{ $name }}</span>
    @if ($rating)
        <span class="text-xs text-brand-500">★ {{ number_format($rating, 1) }}</span>
    @endif
    @if ($salesCount)
        <span class="text-xs text-gray-400">{{ $salesCount }} sales</span>
    @endif
</a>
