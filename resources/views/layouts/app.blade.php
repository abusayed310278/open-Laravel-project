<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-white dark:bg-[#0b0f19] text-gray-800 dark:text-gray-100 antialiased transition-colors duration-150">
    @include('layouts.partials.announcement-bar')
    @include('layouts.partials.public-header')
    @include('layouts.partials.mobile-drawer')

    <main>
        @yield('content')
    </main>

    @include('layouts.partials.public-footer')
    @include('layouts.partials.toast')
    @include('layouts.partials.compare')

    @include('layouts.partials.scroll-to-top')
    @include('layouts.partials.wishlist-script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('pageshow', function () {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) {
                Swal.close();
            }
        });
    </script>
</body>
</html>
