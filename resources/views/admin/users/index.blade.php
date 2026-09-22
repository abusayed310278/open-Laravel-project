@extends('layouts.admin')

@section('title', 'Users')

@section('content')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @session('error')
        <x-alert type="danger" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        {{-- Live Instant Search Toolbar (Instant Filtering On Keystroke) --}}
        <div class="mb-6">
            <div class="relative w-full">
                <div class="pointer-events-none text-gray-400 z-10" style="position: absolute; left: 14px; top: 0; bottom: 0; display: flex; align-items: center;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input
                    type="text"
                    id="userSearchInput"
                    placeholder="Search users by name, email, phone, role or status..."
                    class="w-full h-11 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-brand-400 transition-all shadow-2xs"
                    style="padding-left: 2.75rem !important; padding-right: 2.5rem !important;"
                    autocomplete="off"
                >
                <button
                    type="button"
                    id="clearSearchBtn"
                    onclick="clearUserSearch()"
                    class="hidden absolute right-3 top-0 bottom-0 my-auto h-7 w-7 text-gray-400 hover:text-gray-600 rounded-full flex items-center justify-center cursor-pointer transition-colors"
                    title="Clear search"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        {{-- Users Table --}}
        <x-table :headers="['User', 'Role', 'Status', 'Joined', 'Actions']" id="users-table">
            @forelse ($users as $user)
                <tr class="border-b border-gray-100 hover:bg-gray-50/70 transition-colors user-row" data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . ($user->phone ?? '') . ' ' . $user->role->label() . ' ' . $user->status->label()) }}">
                    {{-- User Details --}}
                    <td class="px-4 py-3.5">
                        @php
                            $palette = [
                                ['bg' => '#e0e7ff', 'text' => '#4338ca', 'border' => '#c7d2fe'], // Indigo
                                ['bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'], // Emerald
                                ['bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd'], // Sky
                                ['bg' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'], // Purple
                                ['bg' => '#ffe4e6', 'text' => '#be123c', 'border' => '#fecdd3'], // Rose
                                ['bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'], // Teal
                                ['bg' => '#f1f5f9', 'text' => '#334155', 'border' => '#cbd5e1'], // Slate
                                ['bg' => '#fef3c7', 'text' => '#b45309', 'border' => '#fde68a'], // Amber
                            ];
                            $avatarColor = $palette[abs(crc32($user->email ?: $user->name)) % count($palette)];
                            $userAvatarUrl = $user->profile?->avatar ? \Illuminate\Support\Facades\Storage::url($user->profile->avatar) : null;
                        @endphp
                        <div class="flex items-center gap-3">
                            @if ($userAvatarUrl)
                                <img src="{{ $userAvatarUrl }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 border border-gray-200 shadow-2xs">
                            @else
                                <div
                                    class="w-9 h-9 rounded-full font-bold text-sm flex items-center justify-center shrink-0 shadow-2xs select-none transition-transform hover:scale-105"
                                    style="background-color: {{ $avatarColor['bg'] }}; color: {{ $avatarColor['text'] }}; border: 1px solid {{ $avatarColor['border'] }};"
                                >
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('admin.users.show', $user) }}" class="font-bold text-sm text-gray-900 hover:text-brand-600 truncate block transition-colors">
                                    {{ $user->name }}
                                </a>
                                <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                                @if($user->phone)
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $user->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Role Dropdown --}}
                    <td class="px-4 py-3.5">
                        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="m-0">
                            @csrf @method('PATCH')
                            <select
                                name="role"
                                onchange="this.form.submit()"
                                class="text-xs font-semibold rounded-lg border border-gray-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 shadow-2xs cursor-pointer hover:border-gray-300 transition-colors"
                                style="padding: 6px 12px !important;"
                            >
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>

                    {{-- Status Dropdown (Pending, Active, Suspended, Blocked) --}}
                    <td class="px-4 py-3.5">
                        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="m-0">
                            @csrf @method('PATCH')
                            <select
                                name="status"
                                onchange="this.form.submit()"
                                class="text-xs font-bold rounded-lg border focus:outline-none focus:ring-2 focus:ring-brand-400 shadow-2xs cursor-pointer transition-colors
                                    {{ $user->status === \App\Enums\UserStatus::Active ? 'border-emerald-200 bg-emerald-50/80 text-emerald-800 hover:bg-emerald-100/60' : '' }}
                                    {{ $user->status === \App\Enums\UserStatus::Pending ? 'border-amber-200 bg-amber-50/80 text-amber-800 hover:bg-amber-100/60' : '' }}
                                    {{ $user->status === \App\Enums\UserStatus::Suspended ? 'border-orange-200 bg-orange-50/80 text-orange-800 hover:bg-orange-100/60' : '' }}
                                    {{ $user->status === \App\Enums\UserStatus::Blocked ? 'border-red-200 bg-red-50/80 text-red-800 hover:bg-red-100/60' : '' }}
                                "
                                style="padding: 6px 12px !important;"
                            >
                                @foreach (\App\Enums\UserStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($user->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>

                    {{-- Joined Date --}}
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-medium text-gray-700 block">{{ $user->created_at->format('M j, Y') }}</span>
                        <span class="text-[11px] text-gray-400 block">{{ $user->created_at->diffForHumans() }}</span>
                    </td>

                    {{-- Actions (View, Edit, Delete Icons) --}}
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center gap-1.5 justify-end">
                            {{-- View Icon --}}
                            <a
                                href="{{ route('admin.users.show', $user) }}"
                                class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors border border-transparent hover:border-blue-100 cursor-pointer"
                                title="View User Details"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </a>

                            {{-- Edit Icon --}}
                            <button
                                type="button"
                                onclick="openEditModal({{ json_encode([
                                    'id' => $user->id,
                                    'name' => $user->name,
                                    'email' => $user->email,
                                    'phone' => $user->phone ?? '',
                                    'role' => $user->role->value,
                                    'status' => $user->status->value,
                                    'update_url' => route('admin.users.update', $user),
                                ]) }})"
                                class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors border border-transparent hover:border-amber-100 cursor-pointer"
                                title="Edit User"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            @if($user->id !== auth()->id())
                                <form
                                    method="POST"
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    onsubmit="return confirm('Are you sure you want to permanently delete user &quot;{{ $user->name }}&quot;? This action cannot be undone.')"
                                    class="inline-block m-0"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-100 cursor-pointer"
                                        title="Delete User"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                        <div class="max-w-xs mx-auto text-center">
                            <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <p class="font-medium text-gray-600">No users found</p>
                            <p class="text-xs text-gray-400 mt-1">There are no users to display.</p>
                        </div>
                    </td>
                </tr>
            @endforelse

            {{-- Live Instant Search No Results Row --}}
            <tr id="no-client-results" style="display: none;">
                <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                    <div class="max-w-xs mx-auto text-center">
                        <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <p class="font-medium text-gray-600">No matching users found</p>
                        <p class="text-xs text-gray-400 mt-1">Try a different name, email, phone or role.</p>
                    </div>
                </td>
            </tr>
        </x-table>

        <div class="mt-4">
            <x-pagination :paginator="$users" />
        </div>
    </x-card>

    {{-- Edit User Modal --}}
    <div id="edit-user-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-brand-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900">Edit User Details</h3>
                </div>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="edit-user-form" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PATCH')

                {{-- Name --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="modal-user-name"
                        required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs"
                        style="padding: 10px 14px !important;"
                    >
                </div>

                {{-- Email & Phone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email Address <span class="text-red-500">*</span></label>
                        <input
                            type="email"
                            name="email"
                            id="modal-user-email"
                            required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs"
                            style="padding: 10px 14px !important;"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input
                            type="text"
                            name="phone"
                            id="modal-user-phone"
                            placeholder="e.g. +1234567890"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs"
                            style="padding: 10px 14px !important;"
                        >
                    </div>
                </div>

                {{-- Role & Status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Role <span class="text-red-500">*</span></label>
                        <select
                            name="role"
                            id="modal-user-role"
                            required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs cursor-pointer"
                            style="padding: 10px 14px !important;"
                        >
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select
                            name="status"
                            id="modal-user-status"
                            required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs cursor-pointer"
                            style="padding: 10px 14px !important;"
                        >
                            @foreach (\App\Enums\UserStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- New Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">New Password (Leave blank to keep current)</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="modal-user-password"
                            placeholder="Min 8 characters"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400 transition-all shadow-2xs"
                            style="padding: 10px 42px 10px 14px !important;"
                        >
                        <button
                            type="button"
                            onclick="togglePasswordVisibility('modal-user-password', this)"
                            class="absolute right-2 top-0 bottom-0 my-auto h-8 w-8 text-gray-400 hover:text-gray-600 flex items-center justify-center cursor-pointer transition-colors"
                            title="Toggle password visibility"
                        >
                            <svg class="w-4 h-4 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            <svg class="w-4 h-4 eye-off-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 013.722-.863c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-2.585 3.992M15 12a3 3 0 11-6 0 3 3 0 016 0zM3 3l18 18" /></svg>
                        </button>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        onclick="closeEditModal()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-sm font-semibold text-gray-700 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-sm font-semibold shadow-xs hover:shadow-md transition-all cursor-pointer"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Live keystroke search filtering
        const userSearchInput = document.getElementById('userSearchInput');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const noResultsRow = document.getElementById('no-client-results');

        if (userSearchInput) {
            userSearchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                let visibleCount = 0;

                if (clearSearchBtn) {
                    clearSearchBtn.classList.toggle('hidden', query.length === 0);
                }

                document.querySelectorAll('.user-row').forEach(row => {
                    const searchData = row.getAttribute('data-search') || '';
                    const matches = searchData.includes(query);
                    row.style.display = matches ? '' : 'none';
                    if (matches) visibleCount++;
                });

                if (noResultsRow) {
                    noResultsRow.style.display = (visibleCount === 0 && query.length > 0) ? '' : 'none';
                }
            });
        }

        function clearUserSearch() {
            if (userSearchInput) {
                userSearchInput.value = '';
                userSearchInput.dispatchEvent(new Event('input'));
                userSearchInput.focus();
            }
        }

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const eyeIcon = btn.querySelector('.eye-icon');
            const eyeOffIcon = btn.querySelector('.eye-off-icon');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon?.classList.add('hidden');
                eyeOffIcon?.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon?.classList.remove('hidden');
                eyeOffIcon?.classList.add('hidden');
            }
        }

        function openEditModal(userData) {
            const form = document.getElementById('edit-user-form');
            form.action = userData.update_url;

            document.getElementById('modal-user-name').value = userData.name || '';
            document.getElementById('modal-user-email').value = userData.email || '';
            document.getElementById('modal-user-phone').value = userData.phone || '';
            document.getElementById('modal-user-role').value = userData.role || '';
            document.getElementById('modal-user-status').value = userData.status || '';
            
            const pwdInput = document.getElementById('modal-user-password');
            if (pwdInput) {
                pwdInput.value = '';
                pwdInput.type = 'password';
                const eyeBtn = pwdInput.nextElementSibling;
                if (eyeBtn) {
                    eyeBtn.querySelector('.eye-icon')?.classList.remove('hidden');
                    eyeBtn.querySelector('.eye-off-icon')?.classList.add('hidden');
                }
            }

            const modal = document.getElementById('edit-user-modal');
            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-user-modal');
            modal.classList.add('hidden');
        }

        // Close on ESC key or clicking background
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEditModal();
        });

        document.getElementById('edit-user-modal')?.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) closeEditModal();
        });
    </script>
@endsection
