@extends('layouts.admin')

@section('title', 'Manage Whole Site — Roles & Permissions')

@section('content')
<div class="space-y-6">
    @include('admin.site-management._tabs')

    @php
        $rolesList = $roles->values();
        $currentIndex = $rolesList->search(fn($r) => $r->slug === $activeRoleSlug);
        if ($currentIndex === false) {
            $currentIndex = 0;
        }
        $prevRole = $currentIndex > 0 ? $rolesList[$currentIndex - 1] : null;
        $nextRole = $currentIndex < ($rolesList->count() - 1) ? $rolesList[$currentIndex + 1] : null;
        $totalRolesCount = $rolesList->count();
    @endphp

    {{-- Status Alerts --}}
    @if (session('status'))
        <x-alert type="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif

    {{-- Role Selector Dropdown Control Bar --}}
    <div class="relative z-30 bg-white border border-gray-200/90 rounded-2xl p-2.5 sm:p-3 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-3">

            {{-- Left: Role Dropdown Trigger & Quick Select --}}
            <div class="flex items-center gap-3 flex-1 min-w-[260px]">
                <div class="relative flex-1 max-w-md" id="role-menu-container">
                    {{-- Main Dropdown Button --}}
                    <button
                        type="button"
                        id="role-dropdown-button"
                        onclick="toggleRoleMenu(event)"
                        class="w-full flex items-center justify-between gap-3 px-3.5 py-2.5 bg-gray-50/90 hover:bg-gray-100 border border-gray-200 hover:border-brand-500 rounded-xl text-left transition-all duration-150 cursor-pointer shadow-2xs group focus:outline-none focus:ring-2 focus:ring-brand-500/20"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand-500 ring-4 ring-brand-100 shrink-0"></span>
                            <div class="flex flex-col min-w-0">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider leading-none">ROLE-BASED ACCESS CONTROL</span>
                                <span class="text-sm font-bold text-gray-900 group-hover:text-brand-600 transition-colors truncate mt-0.5">{{ $activeRole->name }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-gray-400 group-hover:text-gray-700 shrink-0">
                            <span class="hidden sm:inline-block font-mono text-[11px] font-semibold text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-md shadow-2xs">{{ $activeRole->slug }}</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-brand-50 text-brand-700 rounded-md border border-brand-200">{{ $activeRole->permissions->count() }} Perms</span>
                            <svg id="role-dropdown-arrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Floating Dropdown Menu --}}
                    <div
                        id="role-dropdown-panel"
                        class="hidden absolute left-0 top-full mt-2 w-full sm:w-[440px] max-w-[95vw] bg-white border border-gray-100 rounded-2xl shadow-2xl z-50 overflow-hidden transform origin-top-left transition-all duration-150"
                    >
                        {{-- Search Header --}}
                        <div class="p-3 border-b border-gray-100 bg-gray-50/60">
                            <div class="relative">
                                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    type="text"
                                    id="role-filter-input"
                                    oninput="filterRoleMenuItems(this.value)"
                                    placeholder="Search roles (e.g. Admin, Store Owner, User)..."
                                    class="w-full pl-9 pr-8 py-2 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs"
                                    autocomplete="off"
                                />
                                <button
                                    type="button"
                                    onclick="clearRoleFilter()"
                                    id="role-filter-clear"
                                    class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer"
                                    title="Clear search"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- List of Roles --}}
                        <div class="max-h-[340px] overflow-y-auto p-2 space-y-1">
                            @foreach ($rolesList as $r)
                                @php
                                    $isRoleActive = $r->slug === $activeRoleSlug;
                                @endphp
                                <a
                                    href="{{ route('admin.site-management.roles', ['role' => $r->slug]) }}"
                                    class="role-menu-item flex items-start gap-3 p-3 rounded-xl text-left transition-colors {{ $isRoleActive ? 'bg-brand-50 border border-brand-200 text-brand-900 font-bold shadow-2xs' : 'hover:bg-gray-50 text-gray-700 border border-transparent' }}"
                                    data-label="{{ strtolower($r->name . ' ' . $r->slug . ' ' . $r->description) }}"
                                >
                                    <div class="w-2 h-2 rounded-full {{ $isRoleActive ? 'bg-brand-500' : 'bg-gray-300' }} mt-1.5 shrink-0"></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 min-w-0 truncate">
                                                <span class="text-xs font-bold truncate {{ $isRoleActive ? 'text-brand-700' : 'text-gray-900' }}">{{ $r->name }}</span>
                                                <span class="font-mono text-[10px] text-gray-400 shrink-0">({{ $r->slug }})</span>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">{{ $r->permissions->count() }} perms</span>
                                                @if ($isRoleActive)
                                                    <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-[11px] text-gray-400 font-normal leading-tight truncate mt-1">{{ $r->description }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        {{-- Footer --}}
                        <div class="px-3 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500">
                            <span>Total: <strong>{{ $totalRolesCount }}</strong> Configurable Roles</span>
                            <span class="text-brand-600 font-semibold cursor-pointer" onclick="toggleRoleMenu(event)">Close Esc</span>
                        </div>
                    </div>
                </div>

                {{-- Native Select for Quick Mobile / Keyboard Switch --}}
                <div class="hidden lg:block relative shrink-0">
                    <select
                        onchange="if(this.value) window.location.href=this.value;"
                        aria-label="Quick Select Role"
                        class="text-xs font-semibold py-2 px-3 bg-white border border-gray-200 rounded-xl text-gray-700 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 cursor-pointer shadow-2xs"
                    >
                        @foreach ($rolesList as $r)
                            <option value="{{ route('admin.site-management.roles', ['role' => $r->slug]) }}" {{ $r->slug === $activeRoleSlug ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->slug }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Right: Previous & Next Quick Jump Buttons --}}
            <div class="flex items-center gap-1.5 shrink-0 ml-auto">
                @if ($prevRole)
                    <a
                        href="{{ route('admin.site-management.roles', ['role' => $prevRole->slug]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 hover:text-gray-900 transition-colors shadow-2xs"
                        title="Previous Role: {{ $prevRole->name }}"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        <span class="hidden md:inline">{{ $prevRole->name }}</span>
                    </a>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </span>
                @endif

                <span class="text-xs font-bold text-gray-400 px-1">{{ $currentIndex + 1 }} / {{ $totalRolesCount }}</span>

                @if ($nextRole)
                    <a
                        href="{{ route('admin.site-management.roles', ['role' => $nextRole->slug]) }}"
                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 hover:text-gray-900 transition-colors shadow-2xs"
                        title="Next Role: {{ $nextRole->name }}"
                    >
                        <span class="hidden md:inline">{{ $nextRole->name }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-2 rounded-xl text-xs font-semibold bg-gray-50 border border-gray-100 text-gray-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                @endif
            </div>

        </div>
    </div>

    {{-- Permissions Matrix Form Card for the Selected Role --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
        {{-- Selected Role Header Banner --}}
        <div class="p-5 bg-gray-50/70 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-extrabold text-gray-900">{{ $activeRole->name }}</h3>
                    <span class="font-mono text-xs text-gray-600 bg-white px-2 py-0.5 rounded border border-gray-200">{{ $activeRole->slug }}</span>
                    @if ($activeRole->is_system)
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">System Role</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $activeRole->description }}</p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button
                    type="button"
                    onclick="setAllPermissions(true)"
                    class="px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg shadow-2xs transition-colors cursor-pointer"
                >
                    Select All
                </button>
                <button
                    type="button"
                    onclick="setAllPermissions(false)"
                    class="px-2.5 py-1 text-xs font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg shadow-2xs transition-colors cursor-pointer"
                >
                    Deselect All
                </button>
                <div class="text-xs text-gray-500 font-semibold ml-2">
                    <span id="selected-permissions-count" class="text-brand-600 font-bold text-sm">{{ $activeRole->permissions->count() }}</span> permissions assigned
                </div>
            </div>
        </div>

        {{-- Permissions Matrix Form --}}
        <form method="POST" action="{{ route('admin.site-management.roles.permissions', $activeRole) }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                @foreach ($permissionsGrouped as $group => $permissions)
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                                <span>{{ strtoupper($group) }} PERMISSIONS</span>
                            </span>
                            <span class="text-[11px] font-normal text-gray-400 lowercase">({{ $permissions->count() }} available)</span>
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach ($permissions as $perm)
                                @php
                                    $isAssigned = $activeRole->permissions->contains('id', $perm->id);
                                @endphp
                                <label class="perm-card flex items-start gap-3 p-3 rounded-xl border {{ $isAssigned ? 'bg-brand-50/40 border-brand-200 shadow-2xs' : 'bg-gray-50/50 border-gray-100 hover:bg-gray-50' }} transition-all cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $perm->id }}"
                                        {{ $isAssigned ? 'checked' : '' }}
                                        onchange="handlePermissionToggle(this)"
                                        class="perm-checkbox mt-0.5 w-4 h-4 rounded text-brand-500 focus:ring-brand-400 border-gray-300"
                                    >
                                    <div>
                                        <div class="text-xs font-bold text-gray-900">{{ $perm->name }}</div>
                                        <div class="font-mono text-[10px] text-gray-400 mt-0.5">{{ $perm->slug }}</div>
                                        <div class="text-[11px] text-gray-500 mt-1 leading-snug">{{ $perm->description }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 pt-5 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-gray-400">Saving will immediately update permissions for all active <strong>{{ $activeRole->name }}</strong> accounts.</span>
                <button
                    type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow-sm transition-all cursor-pointer"
                >
                    Save {{ $activeRole->name }} Permissions
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleRoleMenu(e) {
        if (e) e.stopPropagation();
        const panel = document.getElementById('role-dropdown-panel');
        const btn = document.getElementById('role-dropdown-button');
        const arrow = document.getElementById('role-dropdown-arrow');
        const searchInput = document.getElementById('role-filter-input');
        if (!panel) return;

        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
            if (btn) btn.setAttribute('aria-expanded', 'true');
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 50);
            }
        } else {
            closeRoleMenu();
        }
    }

    function closeRoleMenu() {
        const panel = document.getElementById('role-dropdown-panel');
        const btn = document.getElementById('role-dropdown-button');
        const arrow = document.getElementById('role-dropdown-arrow');
        if (panel) panel.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
        if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    function filterRoleMenuItems(query) {
        const q = (query || '').toLowerCase().trim();
        const items = document.querySelectorAll('.role-menu-item');
        const clearBtn = document.getElementById('role-filter-clear');
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', q.length === 0);
        }

        items.forEach(el => {
            const label = el.getAttribute('data-label') || '';
            const matches = !q || label.includes(q);
            el.style.display = matches ? 'flex' : 'none';
        });
    }

    function clearRoleFilter() {
        const searchInput = document.getElementById('role-filter-input');
        if (searchInput) {
            searchInput.value = '';
            filterRoleMenuItems('');
            searchInput.focus();
        }
    }

    function handlePermissionToggle(checkbox) {
        const card = checkbox.closest('.perm-card');
        if (checkbox.checked) {
            card.classList.add('bg-brand-50/40', 'border-brand-200', 'shadow-2xs');
            card.classList.remove('bg-gray-50/50', 'border-gray-100');
        } else {
            card.classList.remove('bg-brand-50/40', 'border-brand-200', 'shadow-2xs');
            card.classList.add('bg-gray-50/50', 'border-gray-100');
        }
        updatePermissionCount();
    }

    function setAllPermissions(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
            cb.checked = checked;
            handlePermissionToggle(cb);
        });
    }

    function updatePermissionCount() {
        const total = document.querySelectorAll('.perm-checkbox:checked').length;
        const countSpan = document.getElementById('selected-permissions-count');
        if (countSpan) {
            countSpan.textContent = total;
        }
    }

    // Close on click outside or Escape
    document.addEventListener('click', function(e) {
        const container = document.getElementById('role-menu-container');
        if (container && !container.contains(e.target)) {
            closeRoleMenu();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRoleMenu();
        }
    });
</script>
@endsection
