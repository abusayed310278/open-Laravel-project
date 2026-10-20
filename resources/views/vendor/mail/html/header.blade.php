@props(['url'])
@php
    $logoSrc = null;
    $customLogo = setting('brand_logo');
    if (!empty($customLogo)) {
        $resolved = \App\Support\MediaUrl::resolve($customLogo);
        if (!empty($resolved)) {
            $logoSrc = (str_starts_with($resolved, 'http://') || str_starts_with($resolved, 'https://'))
                ? $resolved
                : url($resolved);
        }
    }

    if (!$logoSrc && file_exists(public_path('buy-and-sale.png'))) {
        $logoSrc = url('buy-and-sale.png');
    }

    if (!$logoSrc && file_exists(public_path('icon.png'))) {
        $logoSrc = url('icon.png');
    }
@endphp
<tr>
<td class="header" style="padding: 25px 0; text-align: center;">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === config('app.name'))
<span style="display: inline-flex; align-items: center; gap: 10px; font-family: 'Inter', Helvetica, Arial, sans-serif;">
    @if ($logoSrc)
        <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" height="38" style="display: inline-block; height: 38px; width: auto; max-width: 220px; vertical-align: middle; border: 0;" />
    @else
        <span style="display: inline-block; width: 36px; height: 36px; line-height: 36px; background-color: #f59e0b; border-radius: 8px; color: #09090b; font-weight: 800; font-size: 14px; text-align: center;">OB</span>
        <span style="color: #09090b; font-weight: 700; font-size: 20px; letter-spacing: -0.02em;">{{ config('app.name') }}</span>
    @endif
</span>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
