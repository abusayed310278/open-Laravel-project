@extends('layouts.admin')

@section('title', 'Settings — Cache Clear')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="space-y-6">
        {{-- Hero Card: Purge All Caches --}}
        <div class="bg-gradient-to-r from-amber-500 via-brand-500 to-amber-600 rounded-xl p-6 text-white shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 text-white backdrop-blur-xs">
                    ⚡ Instant Maintenance
                </div>
                <h2 class="text-xl font-bold tracking-tight text-white">Clear All System Caches</h2>
                <p class="text-xs text-amber-50 max-w-xl leading-relaxed">
                    Flushes application data cache, recompiled Blade views, route maps, and cached configuration in a single safe operation. Recommended after updating views, routes, or uploading new branding assets.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.settings.cache.clear', 'all-clear') }}" onsubmit="return confirm('Clear all application caches immediately?');">
                @csrf
                <button
                    type="submit"
                    class="px-6 py-3 rounded-lg bg-white text-gray-900 font-bold text-xs shadow-md hover:bg-gray-50 active:scale-95 transition-all flex items-center gap-2 whitespace-nowrap"
                >
                    <svg class="w-4 h-4 text-amber-600 animate-spin-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear All Caches (One-Click)
                </button>
            </form>
        </div>

        {{-- Granular Cache Control Cards --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Granular Cache Controls</h2>
            <p class="text-xs text-gray-500 mb-6">Target specific subsystems to invalidate cached data without clearing entire session stores.</p>

            <div class="grid md:grid-cols-2 gap-4">
                {{-- 1. Application Cache --}}
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/40 flex flex-col justify-between hover:border-gray-300 transition-colors">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Application Data Cache</h3>
                                <span class="text-[10px] font-mono text-gray-400">php artisan cache:clear</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 mb-4 leading-relaxed">
                            Clears cached database query results, storefront counts, user preferences, and temporary analytics data.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.cache.clear', 'cache-clear') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear App Cache
                        </button>
                    </form>
                </div>

                {{-- 2. View Template Cache --}}
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/40 flex flex-col justify-between hover:border-gray-300 transition-colors">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Compiled Blade Views</h3>
                                <span class="text-[10px] font-mono text-gray-400">php artisan view:clear</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 mb-4 leading-relaxed">
                            Deletes all compiled Blade templates from `storage/framework/views` so updated layouts and blade components reload.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.cache.clear', 'view-clear') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Clear Blade Views
                        </button>
                    </form>
                </div>

                {{-- 3. Route URL Cache --}}
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/40 flex flex-col justify-between hover:border-gray-300 transition-colors">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Route URL Manifest</h3>
                                <span class="text-[10px] font-mono text-gray-400">php artisan route:clear</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 mb-4 leading-relaxed">
                            Clears cached route manifests so newly added web endpoints, controllers, and admin menus take effect immediately.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.cache.clear', 'route-clear') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Clear Route Cache
                        </button>
                    </form>
                </div>

                {{-- 4. Configuration Cache --}}
                <div class="border border-gray-200 rounded-xl p-5 bg-gray-50/40 flex flex-col justify-between hover:border-gray-300 transition-colors">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">Configuration Cache</h3>
                                <span class="text-[10px] font-mono text-gray-400">php artisan config:clear</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 mb-4 leading-relaxed">
                            Invalidates the cached configuration files, ensuring updated environment variables in `.env` are reloaded.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.settings.cache.clear', 'config-clear') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                            Clear Config Cache
                        </button>
                    </form>
                </div>
            </div>

            {{-- Additional Storage Symlink Utility --}}
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50 p-4 rounded-xl">
                <div>
                    <h4 class="text-xs font-semibold text-gray-800">Public Storage Symlink (`storage:link`)</h4>
                    <p class="text-[11px] text-gray-500">Ensure uploaded media and public assets are accessible via `/storage`.</p>
                </div>
                <form method="POST" action="{{ route('admin.settings.cache.clear', 'storage-link') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold shadow-xs transition-colors whitespace-nowrap">
                        Re-link Storage
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
