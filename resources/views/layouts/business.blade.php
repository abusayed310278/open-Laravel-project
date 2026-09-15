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
                    ['route' => 'business.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Catalog',
                'items' => [
                    ['route' => 'business.products.index', 'label' => 'Products', 'icon' => $icon['box']],
                    ['route' => 'business.products.create', 'label' => 'Add Product', 'icon' => $icon['plus-circle']],
                    ['route' => 'business.inventory.index', 'label' => 'Inventory', 'icon' => $icon['tag']],
                ],
            ],
            [
                'label' => 'Sales',
                'items' => [
                    ['route' => 'business.orders.index', 'label' => 'Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'business.customers.index', 'label' => 'Customers', 'icon' => $icon['users']],
                    ['route' => 'business.invoices.index', 'label' => 'Invoices', 'icon' => $icon['document']],
                    ['route' => 'business.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'business.payment-verifications.index', 'label' => 'Payment Verifications', 'icon' => $icon['banknotes']],
                    ['route' => 'business.refunds.index', 'label' => 'Refunds', 'icon' => $icon['banknotes']],
                ],
            ],
            [
                'label' => 'Store',
                'items' => [
                    ['route' => 'business.store.edit', 'label' => 'Store Profile', 'icon' => $icon['building']],
                    ['route' => 'business.verification.index', 'label' => 'Verification', 'icon' => $icon['shield']],
                    ['route' => 'business.subscription.index', 'label' => 'Subscription', 'icon' => $icon['credit-card']],
                    ['route' => 'business.payment-settings.edit', 'label' => 'Payment Settings', 'icon' => $icon['banknotes']],
                    ['route' => 'business.payouts.index', 'label' => 'Payouts', 'icon' => $icon['wallet']],
                ],
            ],
            [
                'label' => 'Support',
                'items' => [
                    ['route' => 'business.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                    ['route' => 'business.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                    ['route' => 'business.reports.index', 'label' => 'Reports', 'icon' => $icon['chart']],
                    ['route' => 'business.settings.index', 'label' => 'Settings', 'icon' => $icon['cog']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Business Portal" back-href="{{ url('/') }}" />

    <div class="lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
