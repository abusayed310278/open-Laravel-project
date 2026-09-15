<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name'))</title>

@if (setting('brand_favicon'))
    <link rel="icon" href="{{ Illuminate\Support\Facades\Storage::disk('public')->url(setting('brand_favicon')) }}">
@endif

@fonts
@vite(['resources/css/app.css', 'resources/js/app.js'])
@include('layouts.partials.branding-style')
