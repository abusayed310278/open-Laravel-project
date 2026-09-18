<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name'))</title>

@php
    $customFavicon = setting('brand_favicon');
    $faviconUrl = ($customFavicon && \Illuminate\Support\Facades\Storage::disk('public')->exists($customFavicon))
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($customFavicon)
        : asset('icon.png');
@endphp
<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="shortcut icon" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">

<script>
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('layouts.partials.branding-style')
