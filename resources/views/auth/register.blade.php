@php
    $maxWidth = 'max-w-lg';
    $requestedType = request('type', request('role', 'customer'));
    if ($requestedType === 'saler') {
        $requestedType = 'customer';
    }
    $activeTab = old('account_type', $requestedType);
    if (!in_array($activeTab, ['customer', 'business'], true)) {
        $activeTab = 'customer';
    }
@endphp
@extends('layouts.auth')

@section('title', 'Create your account')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Create your account</h1>
    <p id="register-subtitle" class="text-sm text-gray-500 mb-6 transition-all duration-200">
        {{ $activeTab === 'business' 
            ? 'Register your business to set up a storefront, list inventory, and sell at scale.' 
            : 'Shop, buy, or sell products as an individual member.' }}
    </p>

<style>
    .account-tab-btn[data-active="true"],
    .account-tab-btn.active {
        color: #000080 !important;
        border-color: #000080 !important;
        font-weight: 700 !important;
    }
    .account-tab-btn[data-active="true"] .tab-text,
    .account-tab-btn.active .tab-text {
        color: #000080 !important;
    }
    .account-tab-btn[data-active="false"],
    .account-tab-btn:not(.active):not([data-active="true"]) {
        color: #6b7280 !important;
        border-color: transparent !important;
        font-weight: 600 !important;
    }
    .account-tab-btn[data-active="false"] .tab-text,
    .account-tab-btn:not(.active):not([data-active="true"]) .tab-text {
        color: #6b7280 !important;
    }
    .account-tab-btn:hover {
        color: #000080 !important;
    }
    .account-tab-btn:hover .tab-text {
        color: #000080 !important;
    }
</style>

    <div class="grid grid-cols-2 gap-2 mb-3">
        <button type="button" data-tab="customer" data-account-tab data-active="{{ $activeTab === 'customer' ? 'true' : 'false' }}"
            style="{{ $activeTab === 'customer' ? 'color: #000080 !important; border-color: #000080 !important;' : 'border-color: transparent;' }}"
            class="account-tab-btn border-b-2 {{ $activeTab === 'customer' ? 'active font-bold' : 'border-transparent text-gray-500' }} text-sm font-semibold py-2 rounded-t-md transition-colors text-center cursor-pointer">
            <span class="tab-text" style="{{ $activeTab === 'customer' ? 'color: #000080 !important;' : '' }}">Personal</span>
            <span class="text-xs font-normal text-gray-400 block sm:inline sm:ml-1">(Buy & Sell)</span>
        </button>
        <button type="button" data-tab="business" data-account-tab data-active="{{ $activeTab === 'business' ? 'true' : 'false' }}"
            style="{{ $activeTab === 'business' ? 'color: #000080 !important; border-color: #000080 !important;' : 'border-color: transparent;' }}"
            class="account-tab-btn border-b-2 {{ $activeTab === 'business' ? 'active font-bold' : 'border-transparent text-gray-500' }} text-sm font-semibold py-2 rounded-t-md transition-colors text-center cursor-pointer">
            <span class="tab-text" style="{{ $activeTab === 'business' ? 'color: #000080 !important;' : '' }}">Store Owner</span>
            <span class="text-xs font-normal text-gray-400 block sm:inline sm:ml-1">(Business)</span>
        </button>
    </div>

    <div id="account-type-badge" class="mb-5 px-3.5 py-2.5 rounded-lg bg-gray-50 border border-gray-100 text-xs text-gray-600 flex items-start gap-2.5 transition-all duration-200">
        <svg id="account-type-icon-personal" style="color: #000080;" class="w-4 h-4 shrink-0 mt-0.5 {{ $activeTab === 'business' ? 'hidden' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <svg id="account-type-icon-business" style="color: #000080;" class="w-4 h-4 shrink-0 mt-0.5 {{ $activeTab === 'business' ? '' : 'hidden' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <span id="account-type-text" class="leading-relaxed">
            {{ $activeTab === 'business'
                ? 'Store Owner account: Set up a verified business storefront, manage bulk inventory, and reach verified buyers.'
                : 'Personal account: Buy products and sell individual pre-owned or new items on the marketplace.' }}
        </span>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4" id="register-form">
        @csrf
        <input type="hidden" name="account_type" id="account_type" value="{{ $activeTab }}">

        <x-input label="Full name" name="name" type="text" autofocus autocomplete="name" :value="old('name')" required />

        <div class="grid sm:grid-cols-2 gap-4">
            <x-input label="Email" name="email" type="email" autocomplete="username" :value="old('email')" required />
            <x-input label="Phone (optional)" name="phone" type="tel" autocomplete="tel" :value="old('phone')" />
        </div>

        <div data-tab-content="business" class="{{ $activeTab === 'business' ? '' : 'hidden' }} grid gap-4">
            <x-input label="Business name" name="business_name" type="text" :value="old('business_name')" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative">
                <input
                    id="password" name="password" type="password" autocomplete="new-password" required
                    class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB] pr-11"
                >
                <button type="button" data-password-toggle="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password">
                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <div id="password-strength-wrap" class="hidden transition-all duration-200 mt-2">
                <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div id="password-strength-bar" class="h-full rounded-full transition-all duration-300 w-0"></div>
                </div>
                <div class="flex justify-between items-center text-[11px] mt-1 text-gray-400">
                    <span id="password-strength-text">Password strength</span>
                    <span>Min. 8 characters</span>
                </div>
            </div>
            @error('password')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
            <div class="relative">
                <input
                    id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB] pr-11"
                >
                <button type="button" data-password-toggle="password_confirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password confirmation">
                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <div id="password-match-wrap" class="hidden transition-all duration-200 mt-2">
                <div class="h-1.5 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                    <div id="password-match-bar" class="h-full rounded-full transition-all duration-300 w-0"></div>
                </div>
                <div class="flex justify-between items-center text-[11px] mt-1 text-gray-400">
                    <span id="password-match-text">Confirm password</span>
                </div>
            </div>
            @error('password_confirmation')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-start gap-2 text-sm text-gray-600">
            <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required class="w-4 h-4 rounded accent-brand-500 mt-0.5">
            <span>I agree to the Openbox Terms of Service and Privacy Policy.</span>
        </label>
        @error('terms')
            <p class="text-xs text-red-500 -mt-2">{{ $message }}</p>
        @enderror

        <x-button type="submit" class="w-full transition-all duration-150 active:scale-[0.98] cursor-pointer shadow-xs hover:shadow-sm" id="register-submit-btn">
            <span class="btn-text">Create Account</span>
            <span class="btn-loader hidden items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span>Creating account...</span>
            </span>
        </x-button>
    </form>

    <div class="mt-6">
        <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="flex-shrink mx-4 text-xs text-gray-400 font-medium uppercase tracking-wider">Or continue with</span>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <a
                href="{{ Route::has('auth.google') ? route('auth.google') : '#' }}"
                class="flex items-center justify-center gap-2.5 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50/80 hover:border-gray-300 transition-all shadow-xs cursor-pointer group active:scale-[0.98]"
            >
                <svg class="w-5 h-5 shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Google</span>
            </a>

            <a
                href="{{ Route::has('auth.facebook') ? route('auth.facebook') : '#' }}"
                class="flex items-center justify-center gap-2.5 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50/80 hover:border-gray-300 transition-all shadow-xs cursor-pointer group active:scale-[0.98]"
            >
                <svg class="w-5 h-5 shrink-0 fill-[#1877F2] group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>Facebook</span>
            </a>
        </div>
    </div>

    <p class="text-center text-sm text-gray-500 mt-6">
        Already have an account?
        <a href="{{ route('login') }}" style="color: #000080;" class="font-bold hover:underline transition-colors">Log in</a>
    </p>

    <script>
        // Dynamic relevant text & account type sync on tab switch
        const accountTypeInput = document.getElementById('account_type');
        const registerSubtitle = document.getElementById('register-subtitle');
        const accountTypeBadge = document.getElementById('account-type-badge');
        const accountTypeText = document.getElementById('account-type-text');
        const iconPersonal = document.getElementById('account-type-icon-personal');
        const iconBusiness = document.getElementById('account-type-icon-business');
        const businessFieldsWrap = document.querySelector('[data-tab-content="business"]');

        const setAccountTypeContent = (type) => {
            if (accountTypeInput) {
                accountTypeInput.value = type;
            }

            // Update tab button active styles with #000080
            document.querySelectorAll('[data-account-tab]').forEach((tabBtn) => {
                const isActive = tabBtn.dataset.tab === type;
                const tabText = tabBtn.querySelector('.tab-text');
                tabBtn.setAttribute('data-active', isActive ? 'true' : 'false');
                tabBtn.classList.toggle('active', isActive);
                tabBtn.classList.toggle('font-bold', isActive);
                if (isActive) {
                    tabBtn.style.setProperty('color', '#000080', 'important');
                    tabBtn.style.setProperty('border-color', '#000080', 'important');
                    if (tabText) {
                        tabText.style.setProperty('color', '#000080', 'important');
                    }
                    tabBtn.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    tabBtn.style.setProperty('color', '#6b7280', 'important');
                    tabBtn.style.setProperty('border-color', 'transparent', 'important');
                    if (tabText) {
                        tabText.style.setProperty('color', '#6b7280', 'important');
                    }
                    tabBtn.classList.add('border-transparent', 'text-gray-500');
                }
            });

            if (businessFieldsWrap) {
                if (type === 'business') {
                    businessFieldsWrap.classList.remove('hidden');
                } else {
                    businessFieldsWrap.classList.add('hidden');
                }
            }

            if (accountTypeBadge) {
                accountTypeBadge.style.opacity = '0.35';
                accountTypeBadge.style.transform = 'translateY(-2px)';
            }
            if (registerSubtitle) {
                registerSubtitle.style.opacity = '0.35';
            }

            setTimeout(() => {
                if (type === 'business') {
                    if (registerSubtitle) {
                        registerSubtitle.textContent = 'Register your business to set up a storefront, list inventory, and sell at scale.';
                    }
                    if (accountTypeText) {
                        accountTypeText.textContent = 'Store Owner account: Set up a verified business storefront, manage bulk inventory, and reach verified buyers.';
                    }
                    if (iconPersonal) iconPersonal.classList.add('hidden');
                    if (iconBusiness) iconBusiness.classList.remove('hidden');
                } else {
                    if (registerSubtitle) {
                        registerSubtitle.textContent = 'Shop, buy, or sell products as an individual member.';
                    }
                    if (accountTypeText) {
                        accountTypeText.textContent = 'Personal account: Buy products and sell individual pre-owned or new items on the marketplace.';
                    }
                    if (iconPersonal) iconPersonal.classList.remove('hidden');
                    if (iconBusiness) iconBusiness.classList.add('hidden');
                }

                if (accountTypeBadge) {
                    accountTypeBadge.style.opacity = '1';
                    accountTypeBadge.style.transform = 'translateY(0)';
                }
                if (registerSubtitle) {
                    registerSubtitle.style.opacity = '1';
                }
            }, 80);
        };

        document.querySelectorAll('[data-account-tab]').forEach((btn) => {
            btn.addEventListener('click', () => {
                setAccountTypeContent(btn.dataset.tab);
            });
        });

        // Submit button loading animation
        const registerForm = document.getElementById('register-form');
        const registerBtn = document.getElementById('register-submit-btn');
        if (registerForm && registerBtn) {
            registerForm.addEventListener('submit', () => {
                const btnText = registerBtn.querySelector('.btn-text');
                const btnLoader = registerBtn.querySelector('.btn-loader');
                if (btnText && btnLoader) {
                    btnText.classList.add('hidden');
                    btnLoader.classList.remove('hidden');
                    btnLoader.classList.add('inline-flex');
                }
                registerBtn.classList.add('opacity-90', 'cursor-wait');
            });
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

        // Password strength and confirm match indicators (shown on click/focus)
        const passInput = document.getElementById('password');
        const passWrap = document.getElementById('password-strength-wrap');
        const passBar = document.getElementById('password-strength-bar');
        const passText = document.getElementById('password-strength-text');

        const confirmInput = document.getElementById('password_confirmation');
        const confirmWrap = document.getElementById('password-match-wrap');
        const confirmBar = document.getElementById('password-match-bar');
        const confirmText = document.getElementById('password-match-text');

        const updatePasswordStrength = () => {
            const val = passInput ? passInput.value : '';
            if (!val) {
                if (passBar) passBar.className = 'h-full rounded-full transition-all duration-300 w-0';
                if (passText) {
                    passText.textContent = 'Password strength';
                    passText.className = 'text-gray-400';
                }
                return;
            }

            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            if (val.length < 6) {
                score = Math.min(score, 1);
            }

            const levels = [
                { width: 'w-1/4', color: 'bg-red-500', text: 'Too short', textColor: 'text-red-500' },
                { width: 'w-1/4', color: 'bg-red-500', text: 'Weak', textColor: 'text-red-500' },
                { width: 'w-2/4', color: 'bg-orange-500', text: 'Fair', textColor: 'text-orange-500' },
                { width: 'w-3/4', color: 'bg-amber-500', text: 'Good', textColor: 'text-amber-600' },
                { width: 'w-full', color: 'bg-emerald-500', text: 'Strong', textColor: 'text-emerald-600' },
            ];

            const level = levels[score] || levels[0];
            if (passBar) passBar.className = `h-full rounded-full transition-all duration-300 ${level.width} ${level.color}`;
            if (passText) {
                passText.textContent = level.text;
                passText.className = `font-medium ${level.textColor}`;
            }
        };

        const updateConfirmMatch = () => {
            const confirmVal = confirmInput ? confirmInput.value : '';
            const passVal = passInput ? passInput.value : '';

            if (!confirmVal) {
                if (confirmBar) confirmBar.className = 'h-full rounded-full transition-all duration-300 w-0';
                if (confirmText) {
                    confirmText.textContent = 'Confirm password';
                    confirmText.className = 'text-gray-400';
                }
                return;
            }

            if (!passVal) {
                if (confirmBar) confirmBar.className = 'h-full rounded-full transition-all duration-300 w-1/4 bg-gray-300';
                if (confirmText) {
                    confirmText.textContent = 'Enter password first';
                    confirmText.className = 'text-gray-500 font-medium';
                }
                return;
            }

            if (confirmVal === passVal) {
                if (confirmBar) confirmBar.className = 'h-full rounded-full transition-all duration-300 w-full bg-emerald-500';
                if (confirmText) {
                    confirmText.textContent = 'Passwords match';
                    confirmText.className = 'text-emerald-600 font-medium';
                }
            } else {
                const isPrefix = passVal.startsWith(confirmVal);
                if (confirmBar) confirmBar.className = `h-full rounded-full transition-all duration-300 ${isPrefix ? 'w-1/2 bg-amber-500' : 'w-1/2 bg-red-500'}`;
                if (confirmText) {
                    confirmText.textContent = isPrefix ? 'Typing confirmation...' : 'Passwords do not match';
                    confirmText.className = `${isPrefix ? 'text-amber-600' : 'text-red-500'} font-medium`;
                }
            }
        };

        if (passInput && passWrap) {
            passInput.addEventListener('focus', () => {
                passWrap.classList.remove('hidden');
                updatePasswordStrength();
            });
            passInput.addEventListener('click', () => {
                passWrap.classList.remove('hidden');
            });
            passInput.addEventListener('input', () => {
                passWrap.classList.remove('hidden');
                updatePasswordStrength();
                if (confirmInput && confirmInput.value.length > 0) {
                    updateConfirmMatch();
                }
            });
            passInput.addEventListener('blur', () => {
                if (passInput.value.length === 0) {
                    passWrap.classList.add('hidden');
                }
            });

            if (passInput.value.length > 0) {
                passWrap.classList.remove('hidden');
                updatePasswordStrength();
            }
        }

        if (confirmInput && confirmWrap) {
            confirmInput.addEventListener('focus', () => {
                confirmWrap.classList.remove('hidden');
                updateConfirmMatch();
            });
            confirmInput.addEventListener('click', () => {
                confirmWrap.classList.remove('hidden');
            });
            confirmInput.addEventListener('input', () => {
                confirmWrap.classList.remove('hidden');
                updateConfirmMatch();
            });
            confirmInput.addEventListener('blur', () => {
                if (confirmInput.value.length === 0) {
                    confirmWrap.classList.add('hidden');
                }
            });

            if (confirmInput.value.length > 0) {
                confirmWrap.classList.remove('hidden');
                updateConfirmMatch();
            }
        }
    </script>
@endsection
