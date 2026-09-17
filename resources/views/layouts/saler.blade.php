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
                    ['route' => 'saler.products.create', 'label' => 'Add Product', 'icon' => $icon['plus-circle']],
                    ['route' => 'saler.inventory.index', 'label' => 'Inventory', 'icon' => $icon['tag']],
                    ['route' => 'saler.categories.builder', 'label' => 'Category Builder', 'icon' => $icon['sliders']],
                    ['route' => 'saler.categories.index', 'label' => 'Categories', 'icon' => $icon['tag']],
                    ['route' => 'saler.brands.index', 'label' => 'Brands', 'icon' => $icon['star']],
                    ['route' => 'saler.attribute-groups.index', 'label' => 'Attribute Groups', 'icon' => $icon['document']],
                    ['route' => 'saler.attributes.index', 'label' => 'Attributes', 'icon' => $icon['sliders']],
                    ['route' => 'saler.listing-credits.index', 'label' => 'Listing Credits', 'icon' => $icon['tag']],
                ],
            ],
            [
                'label' => 'Verification & Warehouse',
                'items' => [
                    ['route' => 'saler.verification.index', 'label' => 'Verification', 'icon' => $icon['shield']],
                    ['route' => 'saler.appointments.index', 'label' => 'Appointments', 'icon' => $icon['calendar']],
                    ['route' => 'saler.warehouse.index', 'label' => 'Warehouse', 'icon' => $icon['building']],
                ],
            ],
            [
                'label' => 'Sales',
                'items' => [
                    ['route' => 'saler.orders.index', 'label' => 'Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'saler.invoices.index', 'label' => 'Invoices', 'icon' => $icon['document']],
                    ['route' => 'saler.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'saler.payment-verifications.index', 'label' => 'Payment Verifications', 'icon' => $icon['banknotes']],
                    ['route' => 'saler.refunds.index', 'label' => 'Refunds', 'icon' => $icon['banknotes']],
                ],
            ],
            [
                'label' => 'Account',
                'items' => [
                    ['route' => 'saler.store.edit', 'label' => 'Store Profile', 'icon' => $icon['building']],
                    ['route' => 'saler.subscriptions.index', 'label' => 'Subscriptions', 'icon' => $icon['credit-card']],
                    ['route' => 'saler.payment-settings.edit', 'label' => 'Payment Settings', 'icon' => $icon['banknotes']],
                    ['route' => 'saler.payouts.index', 'label' => 'Payouts', 'icon' => $icon['wallet']],
                ],
            ],
            [
                'label' => 'Support',
                'items' => [
                    ['route' => 'saler.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                    ['route' => 'saler.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                    ['route' => 'saler.reports.index', 'label' => 'Reports', 'icon' => $icon['chart']],
                    ['route' => 'saler.settings.index', 'label' => 'Settings', 'icon' => $icon['cog']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Seller" />

    <div class="main-content-wrapper lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
