@php
    $tabs = [
        'logo' => ['label' => 'Logo', 'route' => 'admin.settings.logo'],
        'siteicon' => ['label' => 'Site Icon', 'route' => 'admin.settings.siteicon'],
        'font' => ['label' => 'Font', 'route' => 'admin.settings.font'],
        'color' => ['label' => 'Color', 'route' => 'admin.settings.color'],
        'cache' => ['label' => 'Cache Clear', 'route' => 'admin.settings.cache'],
        'mail' => ['label' => 'Mail (SMTP)', 'route' => 'admin.settings.mail'],
        'storage' => ['label' => 'Storage (R2)', 'route' => 'admin.settings.storage'],
        'payments' => ['label' => 'Payments', 'route' => 'admin.settings.payments'],
        'system' => ['label' => 'System', 'route' => 'admin.settings.system'],
    ];
@endphp

<div class="border-b border-gray-100 flex items-center gap-6 mb-6 overflow-x-auto whitespace-nowrap pb-px">
    @foreach ($tabs as $key => $tab)
        <a
            href="{{ route($tab['route']) }}"
            @class([
                'text-sm font-medium py-3 border-b-2 -mb-px transition-colors flex items-center gap-2 shrink-0',
                'border-brand-500 text-brand-600' => request()->routeIs($tab['route']) || (request()->routeIs('admin.settings.branding') && $key === 'logo'),
                'border-transparent text-gray-500 hover:text-gray-800' => ! (request()->routeIs($tab['route']) || (request()->routeIs('admin.settings.branding') && $key === 'logo')),
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
