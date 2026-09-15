@php
    $user = auth()->user();
    $layout = match($user?->role?->value) {
        'admin' => 'layouts.admin',
        'business' => 'layouts.business',
        'saler' => 'layouts.saler',
        'verifier' => 'layouts.verifier',
        default => 'layouts.customer',
    };

    $nameParts = array_filter(explode(' ', trim($user?->name ?? 'User')));
    $initials = '';
    if (count($nameParts) >= 2) {
        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
    } elseif (!empty($nameParts)) {
        $initials = strtoupper(substr($nameParts[0], 0, min(2, strlen($nameParts[0]))));
    } else {
        $initials = 'U';
    }

    $avatarUrl = $user?->profile?->avatar ? \Illuminate\Support\Facades\Storage::url($user->profile->avatar) : null;

    $company = $user?->businessProfile?->business_name ?? ($user?->salerProfile?->display_name ?? '');
    $location = $user?->salerProfile?->location ?? '';
    $designation = match($user?->role?->value) {
        'admin' => 'Admin',
        'business' => 'Business Owner',
        'saler' => 'Individual Seller',
        'verifier' => 'Verifier',
        default => 'Customer',
    };

    $activeTab = (session('status') === 'password-updated' || $errors->updatePassword->isNotEmpty()) ? 'password' : 'profile';
@endphp

@extends($layout)

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl space-y-6">
    {{-- Status Alerts --}}
    @if (session('status') === 'Profile updated.' || session('status') === 'profile-updated')
        <div class="flex items-center gap-2.5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl shadow-2xs">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="font-medium">Profile updated successfully.</span>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="flex items-center gap-2.5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl shadow-2xs">
            <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="font-medium">Password changed successfully.</span>
        </div>
    @endif

    {{-- Tabs matching Branding / Settings Navigation style --}}
    <div class="border-b border-gray-100 flex items-center gap-6 mb-6">
        <button
            type="button"
            id="tab-btn-profile"
            onclick="switchProfileTab('profile')"
            class="text-sm font-medium py-3 border-b-2 -mb-px transition-colors cursor-pointer {{ $activeTab === 'profile' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
        >
            Profile
        </button>
        <button
            type="button"
            id="tab-btn-password"
            onclick="switchProfileTab('password')"
            class="text-sm font-medium py-3 border-b-2 -mb-px transition-colors cursor-pointer {{ $activeTab === 'password' ? 'border-brand-500 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}"
        >
            Password
        </button>
    </div>

    {{-- Profile Information Tab Card --}}
    <div id="tab-content-profile" class="{{ $activeTab === 'profile' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 shadow-xs">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-6">Profile Information</h2>

            <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- Avatar Photo Upload Section --}}
                <div class="flex items-center gap-5">
                    <div class="relative w-20 h-20 rounded-full bg-brand-400 text-gray-950 font-bold text-2xl flex items-center justify-center overflow-hidden shadow-xs flex-shrink-0">
                        @if ($avatarUrl)
                            <img id="avatar-preview-img" src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            <span id="avatar-preview-initials" class="hidden">{{ $initials }}</span>
                        @else
                            <img id="avatar-preview-img" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                            <span id="avatar-preview-initials">{{ $initials }}</span>
                        @endif
                    </div>

                    <div>
                        <label for="avatar-input" class="inline-flex items-center px-4 py-1.5 bg-[#f8fafc] hover:bg-gray-100 border border-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors cursor-pointer shadow-2xs">
                            Choose Photo
                        </label>
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                        <p class="text-xs text-gray-400 mt-1.5">Max 2MB. Cropped to 300×300.</p>
                        @error('avatar')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Form Fields in 2 Columns --}}
                <div class="grid sm:grid-cols-2 gap-5">
                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-1.5">Full Name *</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username / Email --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">Email / Username *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1.5">Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $user->phone) }}"
                            placeholder="+971 ..."
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                        @error('phone')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Company --}}
                    <div>
                        <label for="company" class="block text-xs font-semibold text-gray-700 mb-1.5">Company</label>
                        <input
                            type="text"
                            id="company"
                            name="company"
                            value="{{ old('company', $company) }}"
                            placeholder="Openbox Inc."
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                        @error('company')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Designation --}}
                    <div>
                        <label for="designation" class="block text-xs font-semibold text-gray-700 mb-1.5">Designation</label>
                        <input
                            type="text"
                            id="designation"
                            name="designation"
                            value="{{ old('designation', $designation) }}"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                    </div>

                    {{-- Nationality / Location --}}
                    <div>
                        <label for="location" class="block text-xs font-semibold text-gray-700 mb-1.5">Nationality / Location</label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location', $location) }}"
                            placeholder="Dubai, UAE"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                {{-- Action Button (Aligned to right) --}}
                <div class="flex justify-end pt-3">
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-xs transition-colors cursor-pointer">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Change Password Tab Card --}}
    <div id="tab-content-password" class="{{ $activeTab === 'password' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 shadow-xs">
            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-6">Change Password</h2>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Current Password --}}
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-gray-700 mb-1.5">Current Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11 transition-all"
                        >
                        <button type="button" data-password-toggle="current_password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show current password">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('current_password'))
                        <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('current_password') }}</p>
                    @endif
                </div>

                {{-- New Password --}}
                <div>
                    <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">New Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            data-strength-for="profile-strength-bar"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11 transition-all"
                        >
                        <button type="button" data-password-toggle="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show new password">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    <div id="profile-strength-bar" class="h-1 rounded-md transition-all mt-2 bg-gray-100"></div>
                    @if ($errors->updatePassword->has('password'))
                        <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password') }}</p>
                    @endif
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11 transition-all"
                        >
                        <button type="button" data-password-toggle="password_confirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password confirmation">
                            <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('password_confirmation'))
                        <p class="text-xs text-red-500 mt-1">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                    @endif
                </div>

                {{-- Action Button (Aligned to right) --}}
                <div class="flex justify-end pt-3">
                    <button type="submit" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-xs transition-colors cursor-pointer">
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function switchProfileTab(tab) {
        const profileTab = document.getElementById('tab-content-profile');
        const passwordTab = document.getElementById('tab-content-password');
        const profileBtn = document.getElementById('tab-btn-profile');
        const passwordBtn = document.getElementById('tab-btn-password');

        if (!profileTab || !passwordTab || !profileBtn || !passwordBtn) return;

        const activeClasses = ['border-brand-500', 'text-brand-600'];
        const inactiveClasses = ['border-transparent', 'text-gray-500', 'hover:text-gray-800'];

        if (tab === 'profile') {
            profileTab.classList.remove('hidden');
            passwordTab.classList.add('hidden');

            profileBtn.classList.remove(...inactiveClasses);
            profileBtn.classList.add(...activeClasses);

            passwordBtn.classList.remove(...activeClasses);
            passwordBtn.classList.add(...inactiveClasses);

            if (window.history.replaceState) {
                window.history.replaceState(null, null, '#profile');
            }
        } else {
            passwordTab.classList.remove('hidden');
            profileTab.classList.add('hidden');

            passwordBtn.classList.remove(...inactiveClasses);
            passwordBtn.classList.add(...activeClasses);

            profileBtn.classList.remove(...activeClasses);
            profileBtn.classList.add(...inactiveClasses);

            if (window.history.replaceState) {
                window.history.replaceState(null, null, '#password');
            }
        }
    }

    // Auto-select tab if URL hash is present
    if (window.location.hash === '#password') {
        switchProfileTab('password');
    } else if (window.location.hash === '#profile') {
        switchProfileTab('profile');
    }

    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('avatar-preview-img');
                const initials = document.getElementById('avatar-preview-initials');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (initials) initials.classList.add('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Toggle eye and eye-slash icons on password show/hide
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const openEye = btn.querySelector('.eye-open');
            const closedEye = btn.querySelector('.eye-closed');
            if (openEye && closedEye) {
                openEye.classList.toggle('hidden');
                closedEye.classList.toggle('hidden');
            }
        });
    });
</script>
@endsection
