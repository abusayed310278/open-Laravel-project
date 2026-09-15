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
                'label' => 'Shopping',
                'items' => [
                    ['route' => 'account.dashboard', 'label' => 'Overview', 'icon' => $icon['home']],
                    ['route' => 'account.orders.index', 'label' => 'My Orders', 'icon' => $icon['shopping-bag']],
                    ['route' => 'account.wishlist.index', 'label' => 'Wishlist', 'icon' => $icon['heart']],
                    ['route' => 'account.invoices.index', 'label' => 'Invoices', 'icon' => $icon['document']],
                    ['route' => 'account.returns.index', 'label' => 'Returns', 'icon' => $icon['undo']],
                ],
            ],
            [
                'label' => 'Engagement',
                'items' => [
                    ['route' => 'account.messages.index', 'label' => 'Messages', 'icon' => $icon['chat']],
                    ['route' => 'notifications.index', 'label' => 'Notifications', 'icon' => $icon['bell']],
                    ['route' => 'account.reviews.index', 'label' => 'Reviews', 'icon' => $icon['star']],
                    ['route' => 'account.support.index', 'label' => 'Support', 'icon' => $icon['support']],
                ],
            ],
            [
                'label' => 'Account Settings',
                'items' => [
                    ['route' => 'account.addresses.index', 'label' => 'Addresses', 'icon' => $icon['map-pin']],
                    ['route' => 'account.profile.edit', 'label' => 'Profile Settings', 'icon' => $icon['cog']],
                    ['route' => 'account.security.edit', 'label' => 'Security', 'icon' => $icon['shield-check']],
                ],
            ],
        ];
    @endphp

    <x-dashboard-sidebar :nav-groups="$navGroups" portal-label="My Account" back-href="{{ url('/') }}" back-label="Back to Marketplace" />

    <div class="lg:ml-56 flex flex-col min-h-screen">
        <x-dashboard-topbar :title="$__env->yieldContent('title', 'My Account')" />

        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 space-y-6">
            @if (View::hasSection('account-content'))
                @yield('account-content')
            @else
                @yield('content')
            @endif
        </main>
    </div>

</body>
</html>
