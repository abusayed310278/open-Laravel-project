<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name'))</title>

@php
    $faviconUrl = \App\Support\MediaUrl::resolve(setting('brand_favicon')) ?: asset('icon.png');
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

<style>
    [x-cloak] {
        display: none !important;
    }
    /* Hide scrollbars globally */
    html, body, *, ::-webkit-scrollbar {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
    ::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }
</style>

