@props(['size' => 'md', 'dark' => false])

@php
    $customLogo = setting('brand_logo');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 sm:gap-3']) }}>
    @if ($customLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($customLogo))
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($customLogo) }}" alt="{{ config('app.name', 'OPENBOX') }}" class="{{ $size === 'lg' ? 'h-12' : 'h-8 sm:h-9' }} w-auto max-w-[220px] object-contain" />
    @else
        <img src="{{ asset('buy-and-sale.png') }}" alt="Buy & Sell" class="{{ $size === 'lg' ? 'h-14' : 'h-10' }} w-auto object-contain" />
        <img src="{{ asset('icon.png') }}" alt="OPENBOX" class="{{ $size === 'lg' ? 'h-10' : 'h-8' }} w-auto object-contain" />
    @endif
</span>
