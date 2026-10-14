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
    localStorage.removeItem('theme');
    document.documentElement.classList.remove('dark');
</script>

@fonts
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('layouts.partials.branding-style')

<style>
    [x-cloak] {
        display: none !important;
    }

    /* Vertical Scrollbar Always Visible */
    html {
        overflow-y: scroll !important;
        scrollbar-width: thin;
        scrollbar-color: #94a3b8 #f1f5f9;
    }

    /* WebKit (Chrome, Edge, Safari, Opera) Scrollbar */
    ::-webkit-scrollbar {
        width: 10px !important;
        height: 10px !important;
    }

    ::-webkit-scrollbar-track {
        background: #f1f5f9 !important;
    }

    ::-webkit-scrollbar-thumb {
        background: #94a3b8 !important;
        border-radius: 9999px !important;
        border: 2px solid #f1f5f9 !important;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #64748b !important;
    }

    .dark html {
        scrollbar-color: #475569 #0f172a;
    }

    .dark ::-webkit-scrollbar-track {
        background: #0f172a !important;
    }

    .dark ::-webkit-scrollbar-thumb {
        background: #475569 !important;
        border: 2px solid #0f172a !important;
    }

    .dark ::-webkit-scrollbar-thumb:hover {
        background: #64748b !important;
    }
</style>

