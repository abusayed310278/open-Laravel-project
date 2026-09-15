@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === config('app.name'))
<span style="display: inline-flex; align-items: center; gap: 8px; font-family: 'Inter', Helvetica, Arial, sans-serif;">
    @if ($logo = setting('brand_logo'))
    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ config('app.name') }}" width="32" height="32" style="display: inline-block; width: 32px; height: 32px; border-radius: 6px; vertical-align: middle;">
    @else
    <span style="display: inline-block; width: 32px; height: 32px; line-height: 32px; background-color: #111827; border-radius: 6px; color: #ffffff; font-weight: 800; font-size: 12px; text-align: center;">OB</span>
    @endif
    <span style="color: #111827; font-weight: 700; font-size: 20px;">{{ config('app.name') }}</span>
</span>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
