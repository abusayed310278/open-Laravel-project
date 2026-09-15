@props(['name', 'city' => null, 'rating' => 5, 'body'])

<div class="bg-white border border-gray-100 rounded-md p-5">
    <div class="flex items-center gap-0.5 text-brand-500 mb-3">
        @for ($i = 1; $i <= 5; $i++)
            <svg class="w-3.5 h-3.5 {{ $i <= $rating ? '' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.951-.69l1.285-3.958z" /></svg>
        @endfor
    </div>

    <p class="text-sm text-gray-600 leading-relaxed">&ldquo;{{ $body }}&rdquo;</p>

    <div class="flex items-center gap-2.5 mt-4">
        <div class="w-8 h-8 bg-brand-100 text-brand-700 rounded-md flex items-center justify-center text-xs font-semibold">
            {{ strtoupper(substr($name, 0, 1)) }}
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800">{{ $name }}</p>
            @if ($city)
                <p class="text-xs text-gray-400">{{ $city }}</p>
            @endif
        </div>
    </div>
</div>
