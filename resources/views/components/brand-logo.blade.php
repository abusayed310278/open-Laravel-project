@props(['size' => 'md', 'dark' => false, 'footer' => false, 'imgClass' => ''])

@php
    $customLogo = $footer ? (setting('brand_footer_icon') ?: setting('brand_logo')) : setting('brand_logo');
    $resolvedLogo = \App\Support\MediaUrl::resolve($customLogo);

    $customSizeClass = match ($size) {
        'sm' => 'h-8 sm:h-9 max-w-[200px]',
        'lg' => 'h-16 sm:h-20 max-w-[320px]',
        'xl' => 'h-20 sm:h-24 max-w-[360px]',
        default => 'h-12 sm:h-14 md:h-16 max-w-[280px]',
    };

    $fallbackSizeClass = match ($size) {
        'sm' => 'h-8 sm:h-9',
        'lg' => 'h-16 sm:h-20',
        'xl' => 'h-20 sm:h-24',
        default => 'h-12 sm:h-14 md:h-16',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 sm:gap-3']) }}>
    @if (!empty($resolvedLogo))
        <img src="{{ $resolvedLogo }}" alt="{{ config('app.name', 'OPENBOX') }}" class="{{ $imgClass ?: $customSizeClass }} w-auto object-contain" />
    @else
        <img src="{{ asset('buy-and-sale.png') }}" alt="{{ config('app.name', 'Openbox') }}" class="{{ $imgClass ?: $fallbackSizeClass }} w-auto object-contain" />
    @endif
</span>

