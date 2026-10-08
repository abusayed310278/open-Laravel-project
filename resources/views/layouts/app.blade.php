<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-white text-gray-800 antialiased">

    @include('layouts.partials.announcement-bar')
    @include('layouts.partials.public-header')
    @include('layouts.partials.mobile-drawer')

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.public-footer')
    @include('layouts.partials.toast')
    @include('layouts.partials.compare')

    {{-- Floating Compare Button --}}
    <a href="{{ route('compare') }}" data-site-compare-link title="Compare" aria-label="Compare"
       class="fixed bottom-24 right-3 sm:right-4 z-40 flex h-12 w-12 items-center justify-center rounded-md bg-white text-gray-700 shadow-lg ring-1 ring-gray-200 transition-colors duration-200 hover:bg-brand-500 hover:text-white hover:ring-brand-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
    </a>

</body>
</html>
