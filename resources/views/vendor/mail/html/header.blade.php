@props(['url'])
@php
    $logoSrc = null;
    $customLogo = setting('brand_logo');
    $resolvedUrl = \App\Support\MediaUrl::resolve($customLogo);
    if (!empty($resolvedUrl)) {
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists(ltrim(parse_url($customLogo, PHP_URL_PATH) ?: $customLogo, '/'))) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path(ltrim(parse_url($customLogo, PHP_URL_PATH) ?: $customLogo, '/'));
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $logoSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
        if (!$logoSrc) {
            $logoSrc = $resolvedUrl;
        }
    }

    if (!$logoSrc && file_exists(public_path('buy-and-sale.png'))) {
        $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('buy-and-sale.png')));
    }

    if (!$logoSrc && file_exists(public_path('icon.png'))) {
        $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents(public_path('icon.png')));
    }
@endphp
<tr>
<td class="header" style="padding: 25px 0; text-align: center;">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === config('app.name'))
<span style="display: inline-flex; align-items: center; gap: 10px; font-family: 'Inter', Helvetica, Arial, sans-serif;">
    @if ($logoSrc)
        <img src="{{ $logoSrc }}" alt="{{ config('app.name') }}" style="display: inline-block; height: 38px; width: auto; max-width: 220px; vertical-align: middle; border: 0;" />
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
