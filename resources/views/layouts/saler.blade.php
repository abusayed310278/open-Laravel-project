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
                    ['route' => 'saler.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Listings',
                'items' => [
                    ['route' => 'saler.products.index', 'label' => 'Products', 'icon' => $icon['box']],
                    ['route' => 'saler.inventory.index', 'label' => 'Inventory', 'icon' => $icon['tag']],
                ],
            ],
            [
                'label' => 'Verification',
                'items' => [
                    ['route' => 'saler.verification.index', 'label' => 'Verification', 'icon' => $icon['shield']],
                    ['route' => 'saler.appointments.index', 'label' => 'Appointments', 'icon' => $icon['calendar']],
                ],
            ],
            [
                'label' => 'Sales',
                'items' => [
                    ['route' => 'saler.orders.index', 'label' => 'Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'saler.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'saler.payment-verifications.index', 'label' => 'Payment Verifications', 'icon' => $icon['banknotes']],
                    ['route' => 'saler.refunds.index', 'label' => 'Refunds', 'icon' => $icon['banknotes']],
                ],
            ],
            [
                'label' => 'Account',
                'items' => [
                    ['route' => 'saler.store.edit', 'label' => 'Seller Profile', 'icon' => $icon['building']],
                    ['route' => 'saler.subscriptions.index', 'label' => 'Subscriptions', 'icon' => $icon['credit-card'], 'permission' => 'subscriptions.manage'],
                    ['route' => 'saler.payment-settings.edit', 'label' => 'Payment Settings', 'icon' => $icon['banknotes'], 'permission' => 'payments.manage'],
                    ['route' => 'saler.payouts.index', 'label' => 'Payouts', 'icon' => $icon['wallet']],
                ],
            ],
            [
                'label' => 'Support',
                'items' => [
                    ['route' => 'saler.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                    ['route' => 'saler.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                    ['route' => 'saler.reports.index', 'label' => 'Reports', 'icon' => $icon['chart']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Seller" />

    <div class="main-content-wrapper lg:ml-56 flex flex-col min-h-screen min-w-0 max-w-full">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6 min-w-0 max-w-full">
            @yield('content')
        </main>
    </div>

    <x-support-chat-widget />

</body>
</html>
