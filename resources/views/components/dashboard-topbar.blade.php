@props(['title'])

@php
    $unread = auth()->user()?->unreadNotifications()->limit(6)->get() ?? collect();
    $unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
    $user = auth()->user();

    $nameParts = array_filter(explode(' ', trim($user?->name ?? 'User')));
    $initials = '';
    if (count($nameParts) >= 2) {
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
    } elseif (!empty($nameParts)) {
        $initials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0]))));
    } else {
        $initials = 'U';
    }

    $roleLabel = match($user?->role?->value) {
        'admin' => 'Admin',
        'business' => 'Business',
        'saler' => 'Seller',
        'verifier' => 'Verifier',
        default => 'Customer',
    };

    $settingsUrl = match($user?->role?->value) {
        'admin' => Route::has('admin.settings.branding') ? route('admin.settings.branding') : '#',
        'business' => Route::has('business.store.edit') ? route('business.store.edit') : (Route::has('business.settings.index') ? route('business.settings.index') : '#'),
        'saler' => Route::has('saler.store.edit') ? route('saler.store.edit') : (Route::has('saler.settings.index') ? route('saler.settings.index') : '#'),
        default => Route::has('account.profile.edit') ? route('account.profile.edit') : '#',
    };
    $avatarUrl = $user?->profile?->avatar ? \Illuminate\Support\Facades\Storage::url($user->profile->avatar) : null;
@endphp

<header class="bg-white border-b border-gray-100 flex items-center justify-between px-6 lg:px-8 py-3 sticky top-0 z-40 shadow-xs">
    <div class="flex items-center gap-3">
        <button id="sidebar-open" type="button" onclick="toggleDashboardSidebar(true)" class="lg:hidden text-gray-500 hover:text-gray-800 p-1.5 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Open Sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
        </button>
        <h1 class="text-lg font-bold text-gray-900">{{ $title }}</h1>
    </div>

    <div class="flex items-center gap-2.5 sm:gap-3.5">
        {{-- Notifications Dropdown --}}
        @if (Route::has('notifications.index'))
            <div class="relative" id="topbar-notif-container">
                <button id="notif-btn" type="button" onclick="toggleTopbarDropdown('notif-dropdown', event)" class="relative w-9 h-9 rounded-xl flex items-center justify-center text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors cursor-pointer" aria-expanded="false" aria-haspopup="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    @if ($unreadCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    @endif
                </button>

                <div id="notif-dropdown" class="hidden absolute right-0 top-full mt-2 w-80 z-50 transition-all duration-150 transform origin-top-right shadow-2xl rounded-2xl bg-white border border-gray-100 py-2 text-sm overflow-hidden">
                    <div class="px-4 py-2 flex items-center justify-between border-b border-gray-50">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Notifications</p>
                        @if($unreadCount > 0)
                            <span class="text-[10px] font-semibold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">{{ $unreadCount }} new</span>
                        @endif
                    </div>

                    @forelse ($unread as $notification)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="block">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-gray-50 border-t border-gray-50 first:border-0 transition-colors">
                                <p class="text-gray-800 font-medium text-xs">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                <p class="text-gray-500 text-xs mt-0.5 line-clamp-2">{{ $notification->data['body'] ?? '' }}</p>
                                <p class="text-gray-400 text-[10px] mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </button>
                        </form>
                    @empty
                        <p class="px-4 py-6 text-center text-gray-400 text-xs">You're all caught up.</p>
                    @endforelse

                    <a href="{{ route('notifications.index') }}" class="block px-4 py-2.5 text-center text-amber-600 text-xs font-semibold border-t border-gray-50 hover:bg-amber-50/50 transition-colors">View all notifications</a>
                </div>
            </div>
        @endif

        {{-- View Site Button --}}
        <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 bg-[#f8fafc] hover:bg-gray-100 border border-gray-200/90 rounded-xl text-xs sm:text-sm font-medium text-gray-700 transition-colors shadow-2xs">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
            <span class="whitespace-nowrap">View Site</span>
        </a>

        {{-- Vertical Divider --}}
        <div class="h-6 w-px bg-gray-200 mx-0.5 sm:mx-1"></div>

        {{-- User Account & Logout Menu --}}
        <div class="relative" id="topbar-user-container">
            <button id="user-btn" type="button" onclick="toggleTopbarDropdown('user-dropdown', event)" class="flex items-center gap-2 sm:gap-2.5 p-1 pr-1.5 sm:pr-2 rounded-xl hover:bg-gray-100/80 transition-colors cursor-pointer select-none group border border-transparent hover:border-gray-200" aria-expanded="false" aria-haspopup="true">
                {{-- Brand Amber Avatar with Initials or Photo --}}
                <div class="w-9 h-9 bg-brand-400 text-gray-950 font-bold text-xs sm:text-sm rounded-full flex items-center justify-center flex-shrink-0 shadow-2xs overflow-hidden">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $user?->name }}" class="w-full h-full object-cover">
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="hidden sm:flex flex-col text-left">
                    <span class="text-xs sm:text-sm font-bold text-gray-900 leading-tight">{{ $user?->name ?? 'User' }}</span>
                    <span class="text-[11px] text-gray-500 font-normal leading-tight mt-0.5">{{ $roleLabel }}</span>
                </div>
                <svg id="user-chevron" class="w-4 h-4 text-gray-400 transition-transform duration-200 group-hover:text-gray-700 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-64 z-50 transition-all duration-150 transform origin-top-right shadow-xl rounded-2xl bg-white border border-gray-100 py-2.5 text-sm overflow-hidden">
                <div class="px-5 py-2.5 border-b border-gray-100">
                    <p class="text-xs text-gray-400 font-medium">Signed in as</p>
                    <p class="text-sm font-bold text-gray-900 truncate mt-0.5">{{ $user?->email }}</p>
                </div>

                <div class="py-1.5">
                    @if (Route::has('account.profile.edit'))
                        <a href="{{ route('account.profile.edit') }}" class="flex items-center gap-3 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-950 font-normal transition-colors">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Update Profile</span>
                        </a>
                    @endif

                    <a href="{{ $settingsUrl }}" class="flex items-center gap-3 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-950 font-normal transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Settings</span>
                    </a>
                </div>

                <div class="border-t border-gray-100 my-1"></div>

                {{-- Logout Action Form --}}
                <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}" class="m-0 p-0">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-5 py-2.5 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 font-medium transition-colors text-left cursor-pointer">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleTopbarDropdown(dropdownId, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation?.();
            }

            const target = document.getElementById(dropdownId);
            if (!target) return;

            const isHidden = target.classList.contains('hidden');

            // Close all dropdowns
            document.querySelectorAll('#user-dropdown, #notif-dropdown').forEach(d => {
                d.classList.add('hidden');
            });

            // Reset chevron
            const chevron = document.getElementById('user-chevron');
            if (chevron) chevron.classList.remove('rotate-180');

            // Toggle target
            if (isHidden) {
                target.classList.remove('hidden');
                if (dropdownId === 'user-dropdown' && chevron) {
                    chevron.classList.add('rotate-180');
                }
            }
        }

        function toggleDashboardSidebar(open) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar) {
                if (open) {
                    sidebar.classList.remove('-translate-x-full');
                } else {
                    sidebar.classList.add('-translate-x-full');
                }
            }
            if (overlay) {
                if (open) {
                    overlay.classList.remove('hidden');
                } else {
                    overlay.classList.add('hidden');
                }
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const userContainer = document.getElementById('topbar-user-container');
            const notifContainer = document.getElementById('topbar-notif-container');
            const userDropdown = document.getElementById('user-dropdown');
            const notifDropdown = document.getElementById('notif-dropdown');
            const chevron = document.getElementById('user-chevron');

            if (userDropdown && userContainer && !userContainer.contains(e.target)) {
                userDropdown.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
            if (notifDropdown && notifContainer && !notifContainer.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
        });
    </script>
</header>
