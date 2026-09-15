<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <a href="{{ route('home') }}" class="mb-8">
            <x-brand-logo size="lg" />
        </a>

        <div class="w-full {{ $maxWidth ?? 'max-w-md' }} bg-white border border-gray-100 rounded-md p-8">
            @yield('content')
        </div>
    </div>

</body>
</html>
