@props([
    'code',
    'title',
    'message',
    'icon',
    'primaryLabel' => 'Back to homepage',
    'primaryHref' => null,
])

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-28 text-center">
    <div class="w-16 h-16 mx-auto mb-6 rounded-md bg-brand-50 text-brand-500 flex items-center justify-center">
        {!! $icon !!}
    </div>

    <p class="text-sm font-mono font-semibold text-gray-400 mb-2">Error {{ $code }}</p>
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">{{ $title }}</h1>
    <p class="text-gray-500 max-w-md mx-auto mb-8 leading-relaxed">{{ $message }}</p>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <x-button as="a" :href="$primaryHref ?? route('home')">{{ $primaryLabel }}</x-button>

        {{ $slot }}
    </div>
</div>
