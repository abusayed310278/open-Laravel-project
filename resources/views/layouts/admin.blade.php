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
                    ['route' => 'admin.site-management.index', 'label' => 'Manage Whole Site', 'icon' => $icon['sliders']],
                    ['route' => 'admin.reports.index', 'label' => 'Reports', 'icon' => $icon['chart']],
                    ['route' => 'admin.visitor-reports.index', 'label' => 'Visitor Reports', 'icon' => $icon['visitor']],
                    ['route' => 'admin.settings.branding', 'label' => 'Settings', 'icon' => $icon['cog']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="Admin" />

    <div class="main-content-wrapper lg:ml-56 flex flex-col min-h-screen min-w-0 max-w-full">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'Dashboard')" />

        <main class="flex-1 px-6 lg:px-8 py-6 space-y-6 min-w-0 max-w-full">
            @yield('content')
        </main>

        {{-- Admin Dashboard Footer --}}
        <footer class="mt-auto border-t border-gray-100 bg-white px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
            <div class="flex items-center gap-3">
                @php
                    $footerIconUrl = \App\Support\MediaUrl::resolve(setting('brand_footer_icon'));
                @endphp
                @if (!empty($footerIconUrl))
                    <img src="{{ $footerIconUrl }}" alt="{{ config('app.name', 'Openbox') }}" class="h-6 w-auto max-h-6 object-contain" />
                @else
                    <img src="{{ asset('icon.png') }}" alt="{{ config('app.name', 'Openbox') }}" class="h-6 w-auto max-h-6 object-contain" />
                @endif
                <span class="font-medium text-gray-700">&copy; {{ date('Y') }} {{ config('app.name', 'Openbox') }}. All rights reserved.</span>
            </div>
            <div class="flex items-center gap-4 text-gray-400">
                <span>Admin Dashboard</span>
                <span>•</span>
                <span>v1.0.0</span>
            </div>
        </footer>
    </div>


</body>
</html>
