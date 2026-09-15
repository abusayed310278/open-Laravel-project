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

</body>
</html>
