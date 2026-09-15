@props(['rating' => 0])

<div class="flex items-center gap-0.5">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
        </svg>
    @endfor
</div>
