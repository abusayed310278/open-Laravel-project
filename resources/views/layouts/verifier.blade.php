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
                'label' => 'Overview',
                'items' => [
                    ['route' => 'verifier.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Verification & Quality',
                'items' => [
                    ['route' => 'verifier.products.index', 'label' => 'Seller Products', 'icon' => $icon['tag']],
                    ['route' => 'verifier.appointments.index', 'label' => 'Inspection Queue', 'icon' => $icon['calendar']],
                    ['route' => 'verifier.history.index', 'label' => 'Inspection History', 'icon' => $icon['clock']],
                ],
            ],
            [
                'label' => 'Communication',
                'items' => [
                    ['route' => 'verifier.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Verifier Portal" />

    <div class="main-content-wrapper lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
