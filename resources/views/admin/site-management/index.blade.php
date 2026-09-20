@extends('layouts.admin')

@section('title', 'Manage Whole Site — Overview')

@section('content')
<div class="space-y-6">
    @include('admin.site-management._tabs')

    {{-- Overview Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Homepage Sections Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Homepage Sections</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                    {{ $homepageActiveCount }}/{{ $homepageFeaturesCount }}
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900 mb-1">{{ $homepageActiveCount }} Active</div>
            <p class="text-xs text-gray-500">{{ $homepageFeaturesCount - $homepageActiveCount }} sections currently toggled off.</p>
            <a href="{{ route('admin.site-management.features', ['group' => 'homepage']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 mt-3">
                <span>Manage Homepage Toggles</span>
                <span>→</span>
            </a>
        </div>

        {{-- Product Page Sections Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Product Page Sections</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">
                    {{ $productPageActiveCount }}/{{ $productPageFeaturesCount }}
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900 mb-1">{{ $productPageActiveCount }} Active</div>
            <p class="text-xs text-gray-500">{{ $productPageFeaturesCount - $productPageActiveCount }} components toggled off.</p>
            <a href="{{ route('admin.site-management.features', ['group' => 'product_page']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 mt-3">
                <span>Manage Product Toggles</span>
                <span>→</span>
            </a>
        </div>

        {{-- Roles & Permissions Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">RBAC Security</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    {{ $rolesCount }}
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900 mb-1">{{ $rolesCount }} Roles</div>
            <p class="text-xs text-gray-500">{{ $permissionsCount }} granular system permissions.</p>
            <a href="{{ route('admin.site-management.roles') }}" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 mt-3">
                <span>Configure Role Matrix</span>
                <span>→</span>
            </a>
        </div>

        {{-- Dashboard Menus Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Dynamic Menus</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                    {{ $menusCount }}
                </span>
            </div>
            <div class="text-2xl font-black text-gray-900 mb-1">{{ $menusCount }} Menu Items</div>
            <p class="text-xs text-gray-500">Dynamic sidebars across 5 user roles.</p>
            <a href="{{ route('admin.site-management.menus') }}" class="inline-flex items-center gap-1 text-xs font-bold text-brand-600 hover:text-brand-700 mt-3">
                <span>Customize Sidebar Menus</span>
                <span>→</span>
            </a>
        </div>
    </div>

    {{-- Quick Control Suite --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs hover:border-gray-900 transition-all">
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center font-bold mb-4">
                1
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-2">Homepage Control</h3>
            <p class="text-xs text-gray-500 leading-relaxed mb-4">
                Enable or disable any section of the homepage (Hero banner, Featured products, Refurbished deals, Top vendors, Newsletter, etc.) with real-time toggle switches.
            </p>
            <a href="{{ route('admin.site-management.features', ['group' => 'homepage']) }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl text-xs inline-block transition-colors">
                Configure Homepage →
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs hover:border-gray-900 transition-all">
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center font-bold mb-4">
                2
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-2">Product Detail Control</h3>
            <p class="text-xs text-gray-500 leading-relaxed mb-4">
                Control frontend component visibility on product pages (Buy Box, Hardware Verification Badge, Specifications table, Chat widget, Seller card).
            </p>
            <a href="{{ route('admin.site-management.features', ['group' => 'product_page']) }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl text-xs inline-block transition-colors">
                Configure Product Page →
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs hover:border-gray-900 transition-all">
            <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 text-brand-600 flex items-center justify-center font-bold mb-4">
                3
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-2">Role & Sidebar Customizer</h3>
            <p class="text-xs text-gray-500 leading-relaxed mb-4">
                Manage RBAC permissions per role and customize sidebar menu titles, icons, routes, and permissions for Customer, Store Owner, Seller, Verifier, and Admin.
            </p>
            <a href="{{ route('admin.site-management.menus') }}" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl text-xs inline-block transition-colors">
                Customize Sidebars →
            </a>
        </div>
    </div>
</div>
@endsection
