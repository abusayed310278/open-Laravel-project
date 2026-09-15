<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-50 antialiased">

    @php
        $icon = \App\Support\Icons::PATHS;

        $navGroups = [
            [
                'label' => null,
                'items' => [
                    ['route' => 'verifier.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Inspection Queue',
                'items' => [
                    ['route' => 'verifier.appointments.index', 'label' => 'Appointments', 'icon' => $icon['calendar']],
                    ['route' => 'verifier.products.index', 'label' => 'Products to Inspect', 'icon' => $icon['shield']],
                    ['route' => 'verifier.history.index', 'label' => 'History', 'icon' => $icon['clock']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Verifier Portal" back-href="{{ url('/') }}" />

    <div class="lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
