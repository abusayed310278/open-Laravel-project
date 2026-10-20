<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body class="bg-gray-50 antialiased">

    @include('layouts.partials.page-transitions')
    @include('layouts.partials.dashboard-effects')

    @php
        $icon = \App\Support\Icons::PATHS;

        $navGroups = [
            [
                'label' => 'Overview',
                'items' => [
                    ['route' => 'account.dashboard', 'label' => 'Dashboard', 'icon' => $icon['home']],
                ],
            ],
            [
                'label' => 'Catalog',
                'items' => [
                    ['route' => 'saler.products.index', 'label' => 'My Products', 'icon' => $icon['box']],
                    ['route' => 'saler.orders.index', 'label' => 'Sales Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'saler.inventory.index', 'label' => 'Inventory', 'icon' => $icon['tag']],
                    ['route' => 'saler.verification.index', 'label' => 'Verification', 'icon' => $icon['shield']],
                    ['route' => 'saler.payouts.index', 'label' => 'Wallet & Payouts', 'icon' => $icon['wallet']],
                    ['route' => 'saler.store.edit', 'label' => 'Store & Settings', 'icon' => $icon['cog']],
                ],
            ],
            [
                'label' => 'Shopping',
                'items' => [
                    ['route' => 'account.orders.index', 'label' => 'My Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'account.wishlist.index', 'label' => 'Wishlist', 'icon' => $icon['heart']],
                    ['route' => 'account.returns.index', 'label' => 'Returns', 'icon' => $icon['undo']],
                ],
            ],
            [
                'label' => 'Engagement',
                'items' => [
                    ['route' => 'account.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                    ['route' => 'account.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'account.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="User" />

    <div class="main-content-wrapper lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'My Account')" />

        <main id="admin-main" class="flex-1 px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            @if (View::hasSection('account-content'))
                @yield('account-content')
            @else
                @yield('content')
            @endif
        </main>
    </div>

    <x-support-chat-widget />
    @include('layouts.partials.wishlist-script')

</body>
</html>
