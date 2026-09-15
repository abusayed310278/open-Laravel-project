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
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Catalog & Sellers',
                'items' => [
                    ['route' => 'admin.users.index', 'label' => 'Users', 'icon' => $icon['users']],
                    ['route' => 'admin.verifications.index', 'label' => 'KYC Verifications', 'icon' => $icon['shield']],
                    ['route' => 'admin.verification-requirements.index', 'label' => 'KYC Requirements', 'icon' => $icon['sliders']],
                    ['route' => 'admin.products.index', 'label' => 'Products', 'icon' => $icon['box']],
                    ['route' => 'admin.inventory.index', 'label' => 'Inventory', 'icon' => $icon['box']],
                    ['route' => 'admin.categories.builder', 'label' => 'Category Builder', 'icon' => $icon['sliders']],
                    ['route' => 'admin.categories.index', 'label' => 'Categories', 'icon' => $icon['tag']],
                    ['route' => 'admin.attribute-groups.index', 'label' => 'Attribute Groups', 'icon' => $icon['document']],
                    ['route' => 'admin.attributes.index', 'label' => 'Attributes', 'icon' => $icon['sliders']],
                    ['route' => 'admin.brands.index', 'label' => 'Brands', 'icon' => $icon['tag']],
                ],
            ],
            [
                'label' => 'Verification & Warehouse',
                'items' => [
                    ['route' => 'admin.verification-locations.index', 'label' => 'Locations', 'icon' => $icon['map-pin']],
                    ['route' => 'admin.verifiers.index', 'label' => 'Verifiers', 'icon' => $icon['users']],
                    ['route' => 'admin.verification-checklists.index', 'label' => 'Checklists', 'icon' => $icon['sliders']],
                    ['route' => 'admin.warehouses.index', 'label' => 'Warehouses', 'icon' => $icon['building']],
                ],
            ],
            [
                'label' => 'Commerce',
                'items' => [
                    ['route' => 'admin.subscriptions.plans.index', 'label' => 'Subscription Plans', 'icon' => $icon['credit-card']],
                    ['route' => 'admin.subscriptions.index', 'label' => 'Subscriptions', 'icon' => $icon['credit-card']],
                    ['route' => 'admin.orders.index', 'label' => 'Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'admin.payment-verifications.index', 'label' => 'Payment Verifications', 'icon' => $icon['banknotes']],
                    ['route' => 'admin.refunds.index', 'label' => 'Refunds', 'icon' => $icon['banknotes']],
                    ['route' => 'admin.payments.index', 'label' => 'Payments', 'icon' => $icon['banknotes']],
                    ['route' => 'admin.payouts.index', 'label' => 'Payouts', 'icon' => $icon['banknotes']],
                    ['route' => 'admin.commission-rules.index', 'label' => 'Commission Rules', 'icon' => $icon['percent']],
                ],
            ],
            [
                'label' => 'Engagement',
                'items' => [
                    ['route' => 'admin.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'admin.review-reports.index', 'label' => 'Review Reports', 'icon' => $icon['star']],
                    ['route' => 'admin.chat.index', 'label' => 'Chat', 'icon' => $icon['chat']],
                    ['route' => 'admin.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                ],
            ],
            [
                'label' => 'Content',
                'items' => [
                    ['route' => 'admin.blog.index', 'label' => 'Blog', 'icon' => $icon['newspaper']],
                    ['route' => 'admin.pages.index', 'label' => 'Pages', 'icon' => $icon['document']],
                    ['route' => 'admin.banners.index', 'label' => 'Banners', 'icon' => $icon['image']],
                    ['route' => 'admin.social-types.index', 'label' => 'Social Types', 'icon' => $icon['share']],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    ['route' => 'admin.reports.index', 'label' => 'Reports', 'icon' => $icon['chart']],
                    ['route' => 'admin.visitor-reports.index', 'label' => 'Visitor Reports', 'icon' => $icon['visitor']],
                    ['route' => 'admin.settings.branding', 'label' => 'Settings', 'icon' => $icon['cog']],
                    ['route' => 'admin.activity-logs.index', 'label' => 'Activity Logs', 'icon' => $icon['clock']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Admin" />

    <div class="lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6">
            @yield('content')
        </main>
    </div>

</body>
</html>
