{{-- Site Management Navigation Header & Tabs --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Manage Whole Site</h1>
        <p class="text-sm text-gray-500 mt-1">Control website feature sections, RBAC permissions, and dynamic dashboard menus.</p>
    </div>
    <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('admin.site-management.cache.clear') }}" class="inline-block m-0">
            @csrf
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold shadow-2xs transition-all cursor-pointer"
                title="Clear Site Management Cache"
            >
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Clear Cache</span>
            </button>
        </form>
    </div>
</div>

{{-- Flash Status Message --}}
@if (session('status'))
    <div class="p-4 mb-6 rounded-xl bg-green-50 border border-green-200 text-sm font-medium text-green-800 flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('status') }}</span>
        </div>
    </div>
@endif

{{-- Tab Bar --}}
<div class="border-b border-gray-100 flex items-center gap-6 mb-6 overflow-x-auto no-scrollbar">
    <a
        href="{{ route('admin.site-management.index') }}"
        class="pb-3 text-sm font-medium transition-colors whitespace-nowrap border-b-2 {{ request()->routeIs('admin.site-management.index') ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
    >
        Overview
    </a>
    <a
        href="{{ route('admin.site-management.features', ['group' => 'homepage']) }}"
        class="pb-3 text-sm font-medium transition-colors whitespace-nowrap border-b-2 {{ request()->routeIs('admin.site-management.features') && request('group', 'homepage') === 'homepage' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
    >
        Homepage Sections
    </a>
    <a
        href="{{ route('admin.site-management.features', ['group' => 'product_page']) }}"
        class="pb-3 text-sm font-medium transition-colors whitespace-nowrap border-b-2 {{ request()->routeIs('admin.site-management.features') && request('group') === 'product_page' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
    >
        Product Page Sections
    </a>
    <a
        href="{{ route('admin.site-management.roles') }}"
        class="pb-3 text-sm font-medium transition-colors whitespace-nowrap border-b-2 {{ request()->routeIs('admin.site-management.roles') ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
    >
        Roles & Permissions (RBAC)
    </a>
    <a
        href="{{ route('admin.site-management.menus') }}"
        class="pb-3 text-sm font-medium transition-colors whitespace-nowrap border-b-2 {{ request()->routeIs('admin.site-management.menus') ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
    >
        Dashboard Menu Customizer
    </a>
</div>
