@props(['navGroups' => [], 'portalLabel' => 'Dashboard', 'backHref' => null, 'backLabel' => 'Back to Marketplace'])

<aside
    id="sidebar"
    class="w-56 bg-white border-r border-gray-100 h-screen fixed left-0 top-0 flex flex-col z-40 transform -translate-x-full lg:translate-x-0 transition-transform duration-300"
>
    <div class="flex items-center justify-between gap-3 px-5 py-5 border-b border-gray-50">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center flex-shrink-0 shadow-2xs">
                <span class="text-gray-950 font-black text-xs">OB</span>
            </div>
            <div class="flex flex-col">
                <span class="font-black text-gray-900 text-sm tracking-tight">{{ config('app.name') }}</span>
                <span class="text-[10px] text-gray-400 font-medium">{{ $portalLabel }}</span>
            </div>
        </a>
        <button id="sidebar-close" type="button" onclick="toggleSidebar(false)" class="lg:hidden text-gray-400 hover:text-gray-600 p-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>

    <nav class="flex-1 px-3 py-3 space-y-4 overflow-y-auto">
        @foreach ($navGroups as $group)
            <div>
                @if (!empty($group['label']))
                    <p class="px-3 pb-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-wider" style="font-size: 10px; letter-spacing: 0.05em;">{{ $group['label'] }}</p>
                @endif
                <div class="space-y-0.5">
                    @foreach ($group['items'] as $item)
                        <x-dashboard.nav-item
                            :href="isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']) ? route($item['route']) : '#'"
                            :icon="$item['icon']"
                            :active="isset($item['route']) && request()->routeIs($item['route'] . '*')"
                        >
                            {{ $item['label'] }}
                        </x-dashboard.nav-item>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Sidebar Footer: Back to Store --}}
    @if (!empty($backHref))
        <div class="px-4 py-3 border-t border-gray-100 space-y-2 bg-gray-50/50">
            <a href="{{ $backHref }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg font-medium transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                <span>{{ $backLabel }}</span>
            </a>
        </div>
    @endif
</aside>

<div id="sidebar-overlay" onclick="toggleSidebar(false)" class="hidden fixed inset-0 bg-black/40 z-30 lg:hidden"></div>
