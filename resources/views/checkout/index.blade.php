@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>


        @if (session('error'))
            <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
        @endif

        @error('checkout')
            <x-alert type="error" class="mb-6">{{ $message }}</x-alert>
        @enderror

        @if ($errors->any() && !$errors->has('checkout'))
            <x-alert type="error" class="mb-6">
                <p class="font-semibold mb-1">Please review the form for the following issues:</p>
                <ul class="list-disc list-inside text-sm space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        @if (empty($summary['groups']))
            <x-alert type="info">Your cart is empty. <a href="{{ route('shop') }}" class="underline">Browse the marketplace</a>.</x-alert>
        @else
            <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
                @csrf
                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
        @if ($isGuest)
            {{-- Guest checkout: interactive Create Account and Sign In panels --}}
            <div id="checkout-guest-auth-section" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xs">
                <div class="mb-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                            <svg class="w-3.5 h-3.5 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Step 1: Account
                        </span>
                        <span class="text-xs text-gray-400">·</span>
                        <span class="text-xs text-gray-500 font-medium">Cart saved</span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900 mt-2">Sign in or create an account to place your order</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Choose an option below to fill in your account information right here on this page.</p>
                </div>

                {{-- Two Option Selector Cards --}}
                <div class="grid md:grid-cols-2 gap-3 mb-5">
                    {{-- Create Account Tab --}}
                    <button type="button" id="tab-btn-register" onclick="switchCheckoutAuthTab('register')"
                        class="text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border-2 border-brand-400 bg-amber-50/20 ring-4 ring-brand-100/70">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-brand-100 text-gray-900 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-gray-900">New customer?</span>
                                    <span class="tab-badge text-[11px] font-bold text-brand-800 bg-brand-100 px-2 py-0.5 rounded-full">Selected</span>
                                </div>
                                <span class="text-xs font-extrabold block mt-0.5" style="color: #000080;">Create Account</span>
                                <p class="text-xs text-gray-500 mt-0.5">Quick setup to complete order and track it.</p>
                            </div>
                        </div>
                    </button>

                    {{-- Sign In Tab --}}
                    <button type="button" id="tab-btn-login" onclick="switchCheckoutAuthTab('login')"
                        class="text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border border-gray-200 bg-white hover:border-gray-300">
                        <div class="flex items-start gap-3">
                            <span class="w-10 h-10 shrink-0 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 8 0 0116 0v1H4v-1z" /></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-gray-900">Returning customer?</span>
                                    <span class="tab-badge hidden text-[11px] font-bold text-brand-800 bg-brand-100 px-2 py-0.5 rounded-full">Selected</span>
                                </div>
                                <span class="text-xs font-extrabold block mt-0.5" style="color: #000080;">Sign in</span>
                                <p class="text-xs text-gray-500 mt-0.5">Access saved addresses and faster checkout.</p>
                            </div>
                        </div>
                    </button>
                </div>

                <input type="hidden" name="auth_mode" id="checkout-auth-mode" value="{{ old('auth_mode', 'register') }}">

                {{-- CREATE ACCOUNT PANEL --}}
                <div id="checkout-register-panel" class="border border-gray-100 rounded-xl p-5 bg-gray-50/70 transition-all duration-200">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Create your account</h3>
                            <p class="text-xs text-gray-500">Fill in your information to register as an individual buyer</p>
                        </div>
                        <span class="text-[11px] text-gray-400 font-medium">Customer (Buy & Sell)</span>
                    </div>

                    {{-- Social Quick Sign-up at top --}}
                    <div class="mb-4">
                        <div class="grid grid-cols-2 gap-2.5">
                            <a href="{{ Route::has('auth.google') ? route('auth.google') : '#' }}"
                                class="flex items-center justify-center gap-2 px-3 py-2.5 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all shadow-2xs">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                                <span>Google</span>
                            </a>
                            <a href="{{ Route::has('auth.facebook') ? route('auth.facebook') : '#' }}"
                                class="flex items-center justify-center gap-2 px-3 py-2.5 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all shadow-2xs">
                                <svg class="w-4 h-4 shrink-0 fill-[#1877F2]" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                        </div>
                        <div class="relative flex items-center justify-center my-4">
                            <div class="border-t border-gray-200 w-full"></div>
                            <span class="bg-gray-50/90 px-3 text-[11px] text-gray-400 font-semibold uppercase tracking-wider absolute">Or register with email</span>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        <div>
                            <label for="checkout-reg-name" class="block text-xs font-semibold text-gray-700 mb-1">Full name <span class="text-red-500">*</span></label>
                            <input type="text" id="checkout-reg-name" name="name" value="{{ old('name') }}" placeholder="John Doe" autocomplete="name"
                                class="w-full bg-white border @error('name') border-red-400 ring-1 ring-red-400 @else border-gray-200 @enderror rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB]">
                            @error('name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label for="checkout-reg-email" class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                                <input type="email" id="checkout-reg-email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email"
                                    class="w-full bg-white border @error('email') border-red-400 ring-1 ring-red-400 @else border-gray-200 @enderror rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB]">
                                @error('email')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="checkout-reg-phone" class="block text-xs font-semibold text-gray-700 mb-1">Phone <span class="text-gray-400 font-normal">(optional)</span></label>
                                <input type="tel" id="checkout-reg-phone" name="phone" value="{{ old('phone') }}" placeholder="e.g. +1 555-0100" autocomplete="tel"
                                    class="w-full bg-white border @error('phone') border-red-400 ring-1 ring-red-400 @else border-gray-200 @enderror rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB]">
                                @error('phone')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-3">
                            <div>
                                <label for="checkout-reg-password" class="block text-xs font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" id="checkout-reg-password" name="password" placeholder="At least 8 characters" autocomplete="new-password"
                                        class="w-full bg-white border @error('password') border-red-400 ring-1 ring-red-400 @else border-gray-200 @enderror rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB] pr-10">
                                    <button type="button" class="checkout-pass-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="checkout-reg-password" aria-label="Toggle password visibility">
                                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="checkout-reg-password-confirmation" class="block text-xs font-semibold text-gray-700 mb-1">Confirm password <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input type="password" id="checkout-reg-password-confirmation" name="password_confirmation" placeholder="Re-type password" autocomplete="new-password"
                                        class="w-full bg-white border border-gray-200 rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB] pr-10">
                                    <button type="button" class="checkout-pass-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="checkout-reg-password-confirmation" aria-label="Toggle password confirmation visibility">
                                        <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label class="flex items-start gap-2 text-xs text-gray-600 pt-1 cursor-pointer">
                            <input type="checkbox" id="checkout-reg-terms" name="terms" value="1" @checked(old('terms', true)) class="w-4 h-4 rounded accent-brand-500 mt-0.5">
                            <span>I agree to the Openbox Terms of Service and Privacy Policy.</span>
                        </label>
                        @error('terms')
                            <p class="text-xs text-red-500 -mt-1">{{ $message }}</p>
                        @enderror

                        <div class="pt-2 text-xs text-gray-500 flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-t border-gray-200/80 mt-3">
                            <span class="inline-flex items-center gap-1.5 text-gray-600 font-medium">
                                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                Next: Enter your shipping address & payment below
                            </span>
                            <button type="button" onclick="switchCheckoutAuthTab('login')" style="color: #000080;" class="font-bold hover:underline cursor-pointer text-left sm:text-right">Already have an account? Sign in</button>
                        </div>
                    </div>
                </div>

                {{-- SIGN IN PANEL --}}
                <div id="checkout-login-panel" class="hidden max-w-md mx-auto border border-gray-200 rounded-2xl p-6 sm:p-7 bg-white shadow-xs transition-all duration-200">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Sign in to your account</h3>
                            <p class="text-xs text-gray-500">Sign in to use your saved addresses and payment details</p>
                        </div>
                        <span class="text-[11px] text-gray-400 font-medium">Returning buyer</span>
                    </div>

                    <div id="checkout-login-alert" class="hidden mb-4 p-3 rounded-lg text-xs leading-relaxed"></div>

                    <div class="space-y-3.5">
                        <div>
                            <label for="checkout-login-id" class="block text-xs font-semibold text-gray-700 mb-1">Email or phone <span class="text-red-500">*</span></label>
                            <input type="text" id="checkout-login-id" placeholder="you@example.com or phone" autocomplete="username"
                                class="w-full bg-white border border-gray-200 rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB]">
                            <p id="checkout-login-err-id" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="checkout-login-password" class="text-xs font-semibold text-gray-700">Password <span class="text-red-500">*</span></label>
                                <a href="{{ route('password.request') }}" target="_blank" style="color: #000080;" class="text-xs font-semibold hover:underline">Forgot password?</a>
                            </div>
                            <div class="relative">
                                <input type="password" id="checkout-login-password" placeholder="Enter your password" autocomplete="current-password"
                                    class="w-full bg-white border border-gray-200 rounded-lg px-3.5 py-2 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#E5E7EB] focus:border-[#E5E7EB] pr-10">
                                <button type="button" class="checkout-pass-toggle absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" data-target="checkout-login-password" aria-label="Toggle password visibility">
                                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                </button>
                            </div>
                            <p id="checkout-login-err-password" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>

                        <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer pt-1">
                            <input type="checkbox" id="checkout-login-remember" checked class="w-4 h-4 rounded accent-brand-500">
                            <span>Remember me for 30 days</span>
                        </label>

                        <div class="pt-2">
                            <button type="button" id="btn-submit-checkout-login" onclick="submitCheckoutLogin()"
                                class="w-full inline-flex items-center justify-center gap-2 brand-btn-primary font-bold px-6 py-2.5 rounded-lg text-sm shadow-xs transition-all active:scale-[0.99] cursor-pointer">
                                <span class="btn-text">Sign In & Continue</span>
                                <span class="btn-loader hidden items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-current" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                    <span>Signing in...</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Social Quick Sign-in --}}
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                            <span>Or sign in with:</span>
                            <button type="button" onclick="switchCheckoutAuthTab('register')" style="color: #000080;" class="font-bold hover:underline cursor-pointer">New here? Create Account</button>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <a href="{{ Route::has('auth.google') ? route('auth.google') : '#' }}"
                                class="flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all shadow-2xs">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                                <span>Google</span>
                            </a>
                            <a href="{{ Route::has('auth.facebook') ? route('auth.facebook') : '#' }}"
                                class="flex items-center justify-center gap-2 px-3 py-2 border border-gray-200 rounded-lg text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-all shadow-2xs">
                                <svg class="w-4 h-4 shrink-0 fill-[#1877F2]" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

                        @php
                            $hasAddresses = $addresses->isNotEmpty();
                            $defaultAddressId = $hasAddresses ? (string)$addresses->first()->id : 'new';
                            if (old('shipping_address_id') !== null && old('shipping_address_id') !== '' && old('shipping_address_id') !== 'new') {
                                $defaultAddressId = (string)old('shipping_address_id');
                            } elseif (old('shipping_address_id') === 'new' || old('shipping.line1') !== null) {
                                $defaultAddressId = 'new';
                            }
                            $selectedMethod = old('payment_method', $paymentMethods[0]->value ?? 'cod');
                        @endphp

                        <x-card id="checkout-shipping-card" title="Shipping Address">
                            @if ($hasAddresses)
                                <div class="space-y-3 mb-4" id="shipping-addresses-container">
                                     @foreach ($addresses as $address)
                                        @php
                                            $isSelected = (string)$address->id === (string)$defaultAddressId;
                                        @endphp
                                        <div id="address-card-{{ $address->id }}" class="address-option-label relative flex items-start justify-between gap-3 border rounded-xl p-4 transition-all duration-150 {{ $isSelected ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/10' : 'border-gray-200 hover:border-gray-300' }}">
                                            <label class="flex items-start gap-3 flex-1 min-w-0 cursor-pointer">
                                                <input type="radio" name="shipping_address_id" value="{{ $address->id }}" 
                                                       class="shipping-address-radio mt-1 accent-amber-500"
                                                       @checked($isSelected)>
                                                <div class="flex-1 text-sm text-gray-700 min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        @if($address->label)
                                                            <span id="address-label-badge-{{ $address->id }}" class="text-xs font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md">{{ $address->label }}</span>
                                                        @endif
                                                        <strong id="address-name-{{ $address->id }}" class="text-gray-900 font-semibold">{{ $address->name }}</strong>
                                                        @if($address->is_default)
                                                            <span id="address-primary-badge-{{ $address->id }}" class="text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300">PRIMARY</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-xs text-gray-600 block mt-1"><span class="font-semibold text-gray-700">Phone:</span> <span id="address-phone-{{ $address->id }}">{{ $address->phone }}</span></span>
                                                    <span class="text-xs text-gray-600 block mt-0.5"><span class="font-semibold text-gray-700">Address:</span> <span id="address-oneline-{{ $address->id }}">{{ $address->oneLine() }}</span></span>
                                                </div>
                                            </label>

                                            <div class="shrink-0 flex items-center pt-0.5">
                                                <button type="button" 
                                                        onclick="openEditAddressModal({{ $address->id }})"
                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-800 hover:text-amber-950 bg-amber-50 hover:bg-amber-100 border border-amber-200/90 px-2.5 py-1.5 rounded-lg transition-colors shadow-2xs group"
                                                        title="Edit Address">
                                                    <svg class="w-3.5 h-3.5 text-amber-600 group-hover:text-amber-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                    <span>Edit</span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach

                                    @php
                                        $isNewSelected = $defaultAddressId === 'new';
                                    @endphp
                                    <label class="address-option-label flex items-center gap-3 border border-dashed rounded-xl p-4 cursor-pointer transition-all duration-150 hover:bg-gray-50 {{ $isNewSelected ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/10' : 'border-gray-300' }}">
                                        <input type="radio" name="shipping_address_id" value="new" 
                                               class="shipping-address-radio accent-amber-500"
                                               @checked($isNewSelected)>
                                        <span class="text-sm font-semibold text-gray-800">+ Use a new address</span>
                                    </label>
                                </div>
                            @endif

                            <div id="new-address-fields" class="grid sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100 {{ (!$hasAddresses || $defaultAddressId === 'new') ? '' : 'hidden' }}">
                                <x-input label="Address Label (optional)" name="shipping[label]" type="text" placeholder="Home, Office, Warehouse..." :value="old('shipping.label')" />
                                <x-input label="Full name" name="shipping[name]" type="text" placeholder="Enter your full name" :value="old('shipping.name', Auth::user()?->name)" />
                                <x-input label="Phone" name="shipping[phone]" type="tel" placeholder="e.g. +880 1700-000000" :value="old('shipping.phone', Auth::user()?->phone)" />
                                <x-input label="Address line 1" name="shipping[line1]" type="text" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" :value="old('shipping.line1')" />
                                <x-input label="Address line 2 (optional)" name="shipping[line2]" type="text" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" :value="old('shipping.line2')" />
                                <x-select label="Country" name="shipping[country]" id="checkout_country_select" placeholder="Select Country" :selected="old('shipping.country', 'United States')" />
                                <x-select label="State/Area (optional)" name="shipping[state]" id="checkout_state_select" placeholder="Select State/Area" :selected="old('shipping.state')" />
                                <x-select label="City" name="shipping[city]" id="checkout_city_select" placeholder="Select City" :selected="old('shipping.city')" />
                                <x-input label="Postal code (optional)" name="shipping[postal_code]" type="text" placeholder="e.g. 1207" :value="old('shipping.postal_code')" />
                                
                                <div class="sm:col-span-2 pt-1">
                                    <x-checkbox name="shipping[is_default]" :checked="!$hasAddresses">Save and set as primary address</x-checkbox>
                                </div>
                            </div>
                        </x-card>

                        <x-card id="checkout-payment-card" title="Payment">
                            @error('payment_method')
                                <x-alert type="error" class="mb-4">{{ $message }}</x-alert>
                            @enderror

                            @if (empty($paymentMethods))
                                <p class="text-sm text-gray-500">No payment method is available for this order yet. Please contact support.</p>
                            @else
                                <div class="space-y-3" id="payment-methods-container">
                                    @foreach ($paymentMethods as $method)
                                        @php
                                            $isMethodSelected = $selectedMethod === $method->value;
                                        @endphp
                                        <div class="payment-method-card border rounded-lg overflow-hidden transition-all duration-150 {{ $isMethodSelected ? 'border-brand-500 ring-1 ring-brand-500 bg-brand-50/10' : 'border-gray-200' }}">
                                            <label class="flex items-center gap-3 p-4 cursor-pointer">
                                                <input type="radio" name="payment_method" value="{{ $method->value }}" 
                                                    class="payment-method-radio accent-brand-500" @checked($isMethodSelected)>
                                                <div class="flex-1 flex items-center justify-between">
                                                    <div>
                                                        <span class="font-medium text-gray-900 text-sm block">{{ $method->label() }}</span>
                                                        @if($method->value === 'stripe')
                                                            <span class="text-xs text-gray-500">Redirects to Stripe Web Portal for secure card payment</span>
                                                        @elseif($method->value === 'paypal')
                                                            <span class="text-xs text-gray-500">Pay via your PayPal account or card</span>
                                                        @elseif($method->value === 'cod')
                                                            <span class="text-xs text-gray-500">Pay with cash upon delivery</span>
                                                        @elseif($method->value === 'manual_bank')
                                                            <span class="text-xs text-gray-500">Direct wire / bank transfer</span>
                                                        @endif
                                                    </div>
                                                    @if($method->value === 'stripe')
                                                        <div class="flex items-center gap-1.5 opacity-90 shrink-0">
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">Visa</span>
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">MC</span>
                                                            <span class="text-[10px] font-bold tracking-wider text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 uppercase">Amex</span>
                                                        </div>
                                                    @elseif($method->value === 'paypal')
                                                        <span class="text-[10px] font-bold tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded border border-blue-100 uppercase shrink-0">PayPal</span>
                                                    @endif
                                                </div>
                                            </label>

                                            @if($method->value === 'stripe')
                                                <div id="stripe-info-box" class="p-4 bg-indigo-50/50 border-t border-indigo-100 space-y-2 {{ $isMethodSelected ? '' : 'hidden' }}">
                                                    <div class="text-xs text-indigo-900 font-medium flex items-center gap-2">
                                                        <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                        </svg>
                                                        <span>Hosted Stripe Web Portal Gateway</span>
                                                    </div>
                                                    <p class="text-xs text-indigo-700/80 leading-relaxed">
                                                        When you place your order, you will be redirected to the official Stripe Checkout portal (<code class="text-[11px] bg-indigo-100 px-1 py-0.5 rounded text-indigo-800">pay.stripe.com</code>) to complete your card payment with 256-bit SSL encryption.
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <p class="text-xs text-gray-400 mt-3">Select your preferred payment method above to complete your order.</p>
                        </x-card>
                    </div>

                    <div>
                        <div class="bg-white border border-gray-100 rounded-md p-6 sticky top-20">
                            <h2 class="font-semibold text-gray-900 mb-4">Order Summary</h2>

                            @foreach ($summary['groups'] as $group)
                                <div class="flex justify-between text-sm text-gray-600 mb-2">
                                    <span>{{ $group['seller']->name }} ({{ $group['route'] === 'openbox' ? 'Openbox' : 'Seller' }} payment)</span>
                                    <span>${{ number_format($group['subtotal'] + $group['shipping'], 2) }}</span>
                                </div>
                            @endforeach

                            <hr class="border-gray-100 my-4">

                            <div class="flex justify-between text-sm text-gray-500 mb-1">
                                <span>Subtotal</span>
                                <span>${{ number_format($summary['subtotal'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500 mb-4">
                                <span>Shipping</span>
                                <span>${{ number_format($summary['shipping'], 2) }}</span>
                            </div>

                            <div class="flex justify-between text-base font-bold text-gray-900 mb-6">
                                <span>Total</span>
                                <span>${{ number_format($summary['total'], 2) }}</span>
                            </div>

                            <x-button type="submit" id="place-order-btn" class="w-full justify-center gap-2" :disabled="empty($paymentMethods)">
                                <svg id="stripe-btn-icon" class="w-4 h-4 {{ $selectedMethod === 'stripe' ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span id="place-order-btn-text">{{ $selectedMethod === 'stripe' ? 'Proceed to Stripe Payment' : 'Place Order' }}</span>
                            </x-button>

                            <div id="checkout-login-notice" class="hidden text-center py-2.5 px-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 font-semibold">
                                Please sign in on the left to complete your order.
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        @endif

        <!-- Edit Address Modal Popup -->
        <dialog id="checkout-edit-address-modal" class="rounded-2xl p-0 backdrop:bg-gray-900/50 backdrop:backdrop-blur-xs max-w-xl w-full border-0 shadow-2xl">
            <div class="p-6 bg-white rounded-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Edit Shipping Address</h3>
                        <p class="text-xs text-gray-500">Update recipient details and delivery location.</p>
                    </div>
                    <button type="button" onclick="document.getElementById('checkout-edit-address-modal').close()" class="text-gray-400 hover:text-gray-600 text-xl font-bold px-2">✕</button>
                </div>

                <form id="checkout-edit-address-form" method="POST" action="" class="space-y-4" onsubmit="handleCheckoutAddressUpdate(event)">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" id="edit_modal_address_id" value="">

                    <div id="edit-address-error" class="hidden p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg"></div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <x-input label="Address Label (optional)" name="label" id="edit_modal_label" placeholder="Home, Office, Warehouse..." />
                        <x-input label="Full Name" name="name" id="edit_modal_name" placeholder="Full recipient name" />
                        <x-input label="Phone" name="phone" id="edit_modal_phone" placeholder="e.g. +880 1700-000000" />
                        <x-input label="Address line 1" name="line1" id="edit_modal_line1" placeholder="House / Building #, Street name, Area" class="sm:col-span-2" />
                        <x-input label="Address line 2 (optional)" name="line2" id="edit_modal_line2" placeholder="Apartment, Suite, Unit, Floor (optional)" class="sm:col-span-2" />
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Country</label>
                            <select name="country" id="edit_modal_country" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">State/Area (optional)</label>
                            <select name="state" id="edit_modal_state" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">City</label>
                            <select name="city" id="edit_modal_city" class="w-full text-xs border border-gray-200 rounded-md px-3 py-2 bg-white focus:ring-1 focus:ring-amber-500 focus:border-amber-500">
                            </select>
                        </div>
                        <x-input label="Postal code (optional)" name="postal_code" id="edit_modal_postal_code" placeholder="e.g. 1207" />
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer select-none">
                            <input type="checkbox" name="is_default" id="edit_modal_is_default" value="1" class="rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                            <span>Set as primary shipping address</span>
                        </label>
                    </div>

                    <div class="pt-3 flex justify-end gap-3 border-t border-gray-100">
                        <x-button type="button" variant="secondary" onclick="document.getElementById('checkout-edit-address-modal').close()">Cancel</x-button>
                        <x-button type="submit" id="edit-address-submit-btn">Save Changes</x-button>
                    </div>
                </form>
            </div>
        </dialog>
    </div>

    <script>
        // Switch between Create Account and Sign In tabs
        window.switchCheckoutAuthTab = function(tab) {
            const btnReg = document.getElementById('tab-btn-register');
            const btnLog = document.getElementById('tab-btn-login');
            const panelReg = document.getElementById('checkout-register-panel');
            const panelLog = document.getElementById('checkout-login-panel');
            const shippingCard = document.getElementById('checkout-shipping-card');
            const paymentCard = document.getElementById('checkout-payment-card');
            const placeOrderBtn = document.getElementById('place-order-btn');
            const loginNotice = document.getElementById('checkout-login-notice');
            const authMode = document.getElementById('checkout-auth-mode');

            if (!btnReg || !btnLog) return;

            const badgeReg = btnReg.querySelector('.tab-badge');
            const badgeLog = btnLog.querySelector('.tab-badge');

            if (tab === 'register') {
                if (authMode) authMode.value = 'register';
                btnReg.className = 'text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border-2 border-brand-400 bg-amber-50/20 ring-4 ring-brand-100/70';
                btnLog.className = 'text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border border-gray-200 bg-white hover:border-gray-300';
                if (badgeReg) badgeReg.classList.remove('hidden');
                if (badgeLog) badgeLog.classList.add('hidden');
                if (panelReg) panelReg.classList.remove('hidden');
                if (panelLog) panelLog.classList.add('hidden');

                // Show shipping address & payment
                if (shippingCard) shippingCard.classList.remove('hidden');
                if (paymentCard) paymentCard.classList.remove('hidden');
                if (placeOrderBtn) placeOrderBtn.classList.remove('hidden');
                if (loginNotice) loginNotice.classList.add('hidden');
            } else {
                if (authMode) authMode.value = 'login';
                btnLog.className = 'text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border-2 border-brand-400 bg-amber-50/20 ring-4 ring-brand-100/70';
                btnReg.className = 'text-left rounded-xl p-4 transition-all duration-200 cursor-pointer border border-gray-200 bg-white hover:border-gray-300';
                if (badgeLog) badgeLog.classList.remove('hidden');
                if (badgeReg) badgeReg.classList.add('hidden');
                if (panelLog) panelLog.classList.remove('hidden');
                if (panelReg) panelReg.classList.add('hidden');

                // Hide shipping address & payment
                if (shippingCard) shippingCard.classList.add('hidden');
                if (paymentCard) paymentCard.classList.add('hidden');
                if (placeOrderBtn) placeOrderBtn.classList.add('hidden');
                if (loginNotice) loginNotice.classList.remove('hidden');
            }
        };

        // Eye toggle for password inputs in guest checkout
        document.querySelectorAll('.checkout-pass-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.target;
                const input = document.getElementById(targetId);
                if (!input) return;
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                const eyeOpen = btn.querySelector('.eye-open');
                const eyeClosed = btn.querySelector('.eye-closed');
                if (eyeOpen && eyeClosed) {
                    eyeOpen.classList.toggle('hidden', isPass);
                    eyeClosed.classList.toggle('hidden', !isPass);
                }
            });
        });

        // Auto-sync account name & phone to shipping address fields
        document.addEventListener('DOMContentLoaded', () => {
            const regName = document.getElementById('checkout-reg-name');
            const regPhone = document.getElementById('checkout-reg-phone');
            const shipName = document.querySelector('input[name="shipping[name]"]');
            const shipPhone = document.querySelector('input[name="shipping[phone]"]');

            if (regName && shipName) {
                regName.addEventListener('input', () => {
                    if (!shipName.dataset.userEdited) {
                        shipName.value = regName.value;
                    }
                });
                shipName.addEventListener('input', () => {
                    shipName.dataset.userEdited = 'true';
                });
            }

            if (regPhone && shipPhone) {
                regPhone.addEventListener('input', () => {
                    if (!shipPhone.dataset.userEdited) {
                        shipPhone.value = regPhone.value;
                    }
                });
                shipPhone.addEventListener('input', () => {
                    shipPhone.dataset.userEdited = 'true';
                });
            }

            const initialMode = document.getElementById('checkout-auth-mode')?.value;
            if (initialMode === 'login') {
                switchCheckoutAuthTab('login');
            }
        });

        // Submit checkout login via AJAX
        window.submitCheckoutLogin = async function() {
            const btn = document.getElementById('btn-submit-checkout-login');
            const alertBox = document.getElementById('checkout-login-alert');
            const loginInput = document.getElementById('checkout-login-id');
            const passInput = document.getElementById('checkout-login-password');
            const rememberInput = document.getElementById('checkout-login-remember');

            // Reset errors
            ['id', 'password'].forEach(f => {
                const el = document.getElementById(`checkout-login-err-${f}`);
                if (el) { el.textContent = ''; el.classList.add('hidden'); }
            });
            if (loginInput) loginInput.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
            if (passInput) passInput.classList.remove('border-red-400', 'ring-1', 'ring-red-400');
            if (alertBox) {
                alertBox.className = 'hidden mb-4 p-3 rounded-lg text-xs leading-relaxed';
                alertBox.innerHTML = '';
            }

            const payload = {
                login: loginInput ? loginInput.value.trim() : '',
                password: passInput ? passInput.value : '',
                remember: rememberInput && rememberInput.checked ? 1 : 0
            };

            const btnText = btn?.querySelector('.btn-text');
            const btnLoader = btn?.querySelector('.btn-loader');
            if (btn) btn.disabled = true;
            if (btnText) btnText.classList.add('hidden');
            if (btnLoader) { btnLoader.classList.remove('hidden'); btnLoader.classList.add('inline-flex'); }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch("{{ route('login') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok) {
                    if (res.status === 422 && data.errors) {
                        let msg = data.errors.login ? data.errors.login[0] : (data.errors.password ? data.errors.password[0] : (data.message || 'Invalid credentials.'));
                        if (alertBox) {
                            alertBox.className = 'mb-4 p-3 rounded-lg text-xs leading-relaxed bg-red-50 text-red-800 border border-red-200';
                            alertBox.textContent = msg;
                        }
                        if (loginInput) loginInput.classList.add('border-red-400', 'ring-1', 'ring-red-400');
                    } else {
                        if (alertBox) {
                            alertBox.className = 'mb-4 p-3 rounded-lg text-xs leading-relaxed bg-red-50 text-red-800 border border-red-200';
                            alertBox.textContent = data.message || 'Sign in failed. Please check your credentials.';
                        }
                    }
                    return;
                }

                if (alertBox) {
                    alertBox.className = 'mb-4 p-3 rounded-lg text-xs leading-relaxed bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium';
                    alertBox.textContent = 'Signed in successfully! Loading your saved details...';
                }

                setTimeout(() => {
                    window.location.reload();
                }, 400);
            } catch (err) {
                console.error('Login error:', err);
                if (alertBox) {
                    alertBox.className = 'mb-4 p-3 rounded-lg text-xs leading-relaxed bg-red-50 text-red-800 border border-red-200';
                    alertBox.textContent = 'An unexpected error occurred. Please try again.';
                }
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.classList.remove('hidden');
                if (btnLoader) { btnLoader.classList.add('hidden'); btnLoader.classList.remove('inline-flex'); }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            const addressRadios = document.querySelectorAll('.shipping-address-radio');
            const newAddressFields = document.getElementById('new-address-fields');

            function updateAddressVisibility() {
                const selectedRadio = document.querySelector('.shipping-address-radio:checked');
                const selectedValue = selectedRadio ? selectedRadio.value : 'new';

                if (newAddressFields) {
                    if (selectedValue === 'new' || !addressRadios.length) {
                        newAddressFields.classList.remove('hidden');
                    } else {
                        newAddressFields.classList.add('hidden');
                    }
                }

                addressRadios.forEach(radio => {
                    const label = radio.closest('.address-option-label');
                    if (!label) return;
                    const isDashed = label.classList.contains('border-dashed');

                    if (radio.checked) {
                        label.classList.add('border-amber-400', 'ring-2', 'ring-amber-400/20', 'bg-amber-50/10');
                        label.classList.remove('border-gray-200', 'border-gray-300');
                    } else {
                        label.classList.remove('border-amber-400', 'ring-2', 'ring-amber-400/20', 'bg-amber-50/10');
                        label.classList.add(isDashed ? 'border-gray-300' : 'border-gray-200');
                    }
                });
            }

            addressRadios.forEach(radio => {
                radio.addEventListener('change', updateAddressVisibility);
            });

            const paymentRadios = document.querySelectorAll('.payment-method-radio');
            const stripeInfoBox = document.getElementById('stripe-info-box');
            const stripeBtnIcon = document.getElementById('stripe-btn-icon');
            const placeOrderBtnText = document.getElementById('place-order-btn-text');

            function updatePaymentMethod() {
                const selectedRadio = document.querySelector('.payment-method-radio:checked');
                const selectedValue = selectedRadio ? selectedRadio.value : '';

                paymentRadios.forEach(radio => {
                    const card = radio.closest('.payment-method-card');
                    if (!card) return;

                    if (radio.checked) {
                        card.classList.add('border-brand-500', 'ring-1', 'ring-brand-500', 'bg-brand-50/10');
                        card.classList.remove('border-gray-200');
                    } else {
                        card.classList.remove('border-brand-500', 'ring-1', 'ring-brand-500', 'bg-brand-50/10');
                        card.classList.add('border-gray-200');
                    }
                });

                if (stripeInfoBox) {
                    if (selectedValue === 'stripe') {
                        stripeInfoBox.classList.remove('hidden');
                    } else {
                        stripeInfoBox.classList.add('hidden');
                    }
                }

                if (stripeBtnIcon && placeOrderBtnText) {
                    if (selectedValue === 'stripe') {
                        stripeBtnIcon.classList.remove('hidden');
                        placeOrderBtnText.textContent = 'Proceed to Stripe Payment';
                    } else {
                        stripeBtnIcon.classList.add('hidden');
                        placeOrderBtnText.textContent = 'Place Order';
                    }
                }
            }

            paymentRadios.forEach(radio => {
                radio.addEventListener('change', updatePaymentMethod);
            });

            updateAddressVisibility();
            updatePaymentMethod();
        });
    </script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <style>
        /* Tom Select copies the select's border/padding classes onto its wrapper; keep a single border on the inner control. */
        .ts-wrapper {
            border: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
        }
        .ts-control {
            border-radius: 0.375rem !important;
            border-color: #e2e8f0 !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            background-color: #ffffff !important;
            box-shadow: none !important;
        }
        .ts-control.focus {
            border-color: #E5E7EB !important;
            box-shadow: 0 0 0 2px #E5E7EB !important;
        }
        .ts-dropdown {
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e2e8f0 !important;
            margin-top: 4px !important;
            z-index: 50 !important;
        }
        .ts-dropdown .dropdown-input-wrap {
            padding: 0.5rem !important;
            border-bottom: 1px solid #f1f5f9;
        }
        .ts-dropdown .dropdown-input-wrap .dropdown-input {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.375rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 0.875rem !important;
            box-shadow: none !important;
        }
        .ts-dropdown .dropdown-input-wrap .dropdown-input:focus {
            border-color: #E5E7EB !important;
            box-shadow: 0 0 0 2px #E5E7EB !important;
        }
        .ts-dropdown .option.active, .ts-dropdown .option:hover {
            background-color: #fffbeb !important;
            color: #d97706 !important;
        }
        dialog::backdrop {
            background-color: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fallbackCountries = [
                {
                    country: 'Bangladesh',
                    cities: ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Comilla', 'Narayanganj', 'Gazipur', 'Bogra', 'Mymensingh', 'Cox\'s Bazar', 'Feni', 'Noakhali', 'Jashore', 'Dinajpur'],
                    states: ['Dhaka Division', 'Chittagong Division', 'Sylhet Division', 'Rajshahi Division', 'Khulna Division', 'Barisal Division', 'Rangpur Division', 'Mymensingh Division']
                },
                {
                    country: 'United States',
                    cities: ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'San Francisco', 'Columbus', 'Indianapolis', 'Seattle', 'Miami'],
                    states: ['California', 'New York', 'Texas', 'Florida', 'Illinois', 'Pennsylvania', 'Ohio', 'Georgia', 'North Carolina', 'Michigan', 'New Jersey', 'Virginia', 'Washington', 'Arizona', 'Massachusetts']
                },
                {
                    country: 'United Kingdom',
                    cities: ['London', 'Birmingham', 'Glasgow', 'Manchester', 'Liverpool', 'Bristol', 'Edinburgh', 'Leeds', 'Sheffield', 'Newcastle', 'Belfast', 'Cardiff', 'Nottingham', 'Leicester'],
                    states: ['England', 'Scotland', 'Wales', 'Northern Ireland']
                },
                {
                    country: 'Canada',
                    cities: ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener'],
                    states: ['Ontario', 'Quebec', 'British Columbia', 'Alberta', 'Manitoba', 'Saskatchewan', 'Nova Scotia', 'New Brunswick']
                },
                {
                    country: 'Australia',
                    cities: ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Canberra', 'Newcastle', 'Hobart'],
                    states: ['New South Wales', 'Victoria', 'Queensland', 'Western Australia', 'South Australia', 'Tasmania', 'Australian Capital Territory']
                },
                {
                    country: 'India',
                    cities: ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Ahmedabad', 'Chennai', 'Kolkata', 'Surat', 'Pune', 'Jaipur', 'Lucknow', 'Kanpur'],
                    states: ['Maharashtra', 'Delhi', 'Karnataka', 'Telangana', 'Gujarat', 'Tamil Nadu', 'West Bengal', 'Rajasthan', 'Uttar Pradesh', 'Kerala']
                },
                {
                    country: 'United Arab Emirates',
                    cities: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Al Ain', 'Ajman', 'Ras Al Khaimah', 'Fujairah'],
                    states: ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain']
                },
                {
                    country: 'Saudi Arabia',
                    cities: ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar', 'Tabuk', 'Abha'],
                    states: ['Riyadh Region', 'Makkah Region', 'Eastern Province', 'Madinah Region', 'Asir Region']
                },
                {
                    country: 'Pakistan',
                    cities: ['Karachi', 'Lahore', 'Faisalabad', 'Rawalpindi', 'Gujranwala', 'Peshawar', 'Multan', 'Islamabad', 'Quetta'],
                    states: ['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Balochistan', 'Islamabad Capital Territory']
                }
            ];

            let countriesData = JSON.parse(JSON.stringify(fallbackCountries));
            let statesData = fallbackCountries.map(c => ({ name: c.country, states: c.states }));

            const initialCountry = @json(old('shipping.country', 'United States'));
            const initialCity = @json(old('shipping.city', ''));
            const initialState = @json(old('shipping.state', ''));

            const countrySelect = document.getElementById('checkout_country_select');
            const citySelect = document.getElementById('checkout_city_select');
            const stateSelect = document.getElementById('checkout_state_select');

            let countryTomSelect = null;
            let cityTomSelect = null;
            let stateTomSelect = null;

            function initTomSelectInstances() {
                if (typeof TomSelect === 'undefined') return;

                if (countrySelect && !countryTomSelect) {
                    countryTomSelect = new TomSelect(countrySelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select country...',
                        allowEmptyOption: true,
                        onChange: function(val) {
                            populateCitiesAndStates(val, '', '');
                        }
                    });
                }

                if (citySelect && !cityTomSelect) {
                    cityTomSelect = new TomSelect(citySelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select city...',
                        allowEmptyOption: true,
                    });
                }

                if (stateSelect && !stateTomSelect) {
                    stateTomSelect = new TomSelect(stateSelect, {
                        create: true,
                        plugins: ['dropdown_input'],
                        placeholder: 'Search & select state/area...',
                        allowEmptyOption: true,
                    });
                }
            }

            function populateCountries(selectedCountryName) {
                if (!countrySelect) return;

                // Sync native select element options
                countrySelect.innerHTML = '';
                const defaultCountryOpt = document.createElement('option');
                defaultCountryOpt.value = '';
                defaultCountryOpt.textContent = 'Search & select country...';
                countrySelect.appendChild(defaultCountryOpt);

                countriesData.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                        opt.selected = true;
                    }
                    countrySelect.appendChild(opt);
                });

                if (countryTomSelect) {
                    countryTomSelect.clearOptions();
                    countryTomSelect.addOption({ value: '', text: 'Search & select country...' });

                    let matchFound = false;
                    countriesData.forEach(item => {
                        countryTomSelect.addOption({ value: item.country, text: item.country });
                        if (selectedCountryName && item.country.toLowerCase() === selectedCountryName.toLowerCase()) {
                            matchFound = true;
                            selectedCountryName = item.country;
                        }
                    });

                    if (selectedCountryName && !matchFound) {
                        countryTomSelect.addOption({ value: selectedCountryName, text: selectedCountryName });
                    }

                    countryTomSelect.refreshOptions(false);
                    countryTomSelect.setValue(selectedCountryName || '', true);
                }
            }

            function populateCitiesAndStates(countryName, selectedCityName, selectedStateName) {
                if (citySelect) {
                    const countryObj = countryName ? countriesData.find(item => item.country.toLowerCase() === countryName.toLowerCase()) : null;
                    const cities = countryObj ? (countryObj.cities || []) : [];

                    // Sync native select options
                    citySelect.innerHTML = '';
                    const defaultCityOpt = document.createElement('option');
                    defaultCityOpt.value = '';
                    defaultCityOpt.textContent = countryName ? 'Search & select city...' : 'Select a country first...';
                    citySelect.appendChild(defaultCityOpt);

                    cities.forEach(cityName => {
                        const opt = document.createElement('option');
                        opt.value = cityName;
                        opt.textContent = cityName;
                        if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                            opt.selected = true;
                        }
                        citySelect.appendChild(opt);
                    });

                    if (cityTomSelect) {
                        cityTomSelect.clearOptions();
                        cityTomSelect.addOption({ value: '', text: countryName ? 'Search & select city...' : 'Select a country first...' });

                        let matchFound = false;
                        cities.forEach(cityName => {
                            cityTomSelect.addOption({ value: cityName, text: cityName });
                            if (selectedCityName && cityName.toLowerCase() === selectedCityName.toLowerCase()) {
                                matchFound = true;
                                selectedCityName = cityName;
                            }
                        });

                        if (selectedCityName && !matchFound) {
                            cityTomSelect.addOption({ value: selectedCityName, text: selectedCityName });
                        }

                        cityTomSelect.refreshOptions(false);
                        cityTomSelect.setValue(selectedCityName || '', true);
                    }
                }

                if (stateSelect) {
                    const stateObj = countryName ? statesData.find(item => (item.name || item.country || '').toLowerCase() === countryName.toLowerCase()) : null;
                    const states = stateObj ? (stateObj.states || []) : [];

                    // Sync native select options
                    stateSelect.innerHTML = '';
                    const defaultStateOpt = document.createElement('option');
                    defaultStateOpt.value = '';
                    defaultStateOpt.textContent = countryName ? 'Search & select state/area...' : 'Select a country first...';
                    stateSelect.appendChild(defaultStateOpt);

                    states.forEach(st => {
                        const sName = typeof st === 'string' ? st : (st.name || st);
                        const opt = document.createElement('option');
                        opt.value = sName;
                        opt.textContent = sName;
                        if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                            opt.selected = true;
                        }
                        stateSelect.appendChild(opt);
                    });

                    if (stateTomSelect) {
                        stateTomSelect.clearOptions();
                        stateTomSelect.addOption({ value: '', text: countryName ? 'Search & select state/area...' : 'Select a country first...' });

                        let matchFound = false;
                        states.forEach(st => {
                            const sName = typeof st === 'string' ? st : (st.name || st);
                            stateTomSelect.addOption({ value: sName, text: sName });
                            if (selectedStateName && sName.toLowerCase() === selectedStateName.toLowerCase()) {
                                matchFound = true;
                                selectedStateName = sName;
                            }
                        });

                        if (selectedStateName && !matchFound) {
                            stateTomSelect.addOption({ value: selectedStateName, text: selectedStateName });
                        }

                        stateTomSelect.refreshOptions(false);
                        stateTomSelect.setValue(selectedStateName || '', true);
                    }
                }
            }

            // Initialize IMMEDIATELY on load
            initTomSelectInstances();
            populateCountries(initialCountry);
            populateCitiesAndStates(initialCountry, initialCity, initialState);

            // Asynchronously fetch full database in background
            async function fetchFullDatabaseInBackground() {
                try {
                    const [resCountries, resStates] = await Promise.all([
                        fetch('https://countriesnow.space/api/v0.1/countries'),
                        fetch('https://countriesnow.space/api/v0.1/countries/states')
                    ]);

                    const jsonCountries = await resCountries.json();
                    const jsonStates = await resStates.json();

                    if (jsonCountries && jsonCountries.data && jsonCountries.data.length > 0) {
                        countriesData = jsonCountries.data;
                    }

                    if (jsonStates && jsonStates.data && jsonStates.data.length > 0) {
                        statesData = jsonStates.data;
                    }

                    const currentSelectedCountry = countryTomSelect ? countryTomSelect.getValue() : initialCountry;
                    const currentSelectedCity = cityTomSelect ? cityTomSelect.getValue() : initialCity;
                    const currentSelectedState = stateTomSelect ? stateTomSelect.getValue() : initialState;

                    populateCountries(currentSelectedCountry);
                    populateCitiesAndStates(currentSelectedCountry, currentSelectedCity, currentSelectedState);
                } catch (err) {
                    // Silently retain fallback data
                }
            }

            fetchFullDatabaseInBackground();

            // Checkout Address Editing Logic
            const savedCheckoutAddresses = @json($addresses->keyBy('id'));

            window.populateEditModalGeographic = function(selectedCountry, selectedState, selectedCity) {
                const countryEl = document.getElementById('edit_modal_country');
                const stateEl = document.getElementById('edit_modal_state');
                const cityEl = document.getElementById('edit_modal_city');
                if (!countryEl || !stateEl || !cityEl) return;

                const cList = (countriesData && countriesData.length > 0) ? countriesData : fallbackCountries;

                countryEl.innerHTML = '<option value="">Select Country</option>';
                let countryFound = false;
                cList.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.country;
                    opt.textContent = item.country;
                    if (selectedCountry && item.country.toLowerCase() === selectedCountry.toLowerCase()) {
                        opt.selected = true;
                        countryFound = true;
                    }
                    countryEl.appendChild(opt);
                });

                if (selectedCountry && !countryFound) {
                    const opt = document.createElement('option');
                    opt.value = selectedCountry;
                    opt.textContent = selectedCountry;
                    opt.selected = true;
                    countryEl.appendChild(opt);
                }

                function syncModalStateAndCity() {
                    const cVal = countryEl.value;
                    const countryObj = cVal ? cList.find(item => item.country.toLowerCase() === cVal.toLowerCase()) : null;

                    // States
                    stateEl.innerHTML = '<option value="">Select State/Area (optional)</option>';
                    const sList = countryObj ? (countryObj.states || []) : [];
                    let stateFound = false;
                    sList.forEach(st => {
                        const sName = typeof st === 'string' ? st : (st.name || st);
                        const opt = document.createElement('option');
                        opt.value = sName;
                        opt.textContent = sName;
                        if (selectedState && sName.toLowerCase() === selectedState.toLowerCase()) {
                            opt.selected = true;
                            stateFound = true;
                        }
                        stateEl.appendChild(opt);
                    });
                    if (selectedState && !stateFound) {
                        const opt = document.createElement('option');
                        opt.value = selectedState;
                        opt.textContent = selectedState;
                        opt.selected = true;
                        stateEl.appendChild(opt);
                    }

                    // Cities
                    cityEl.innerHTML = '<option value="">Select City</option>';
                    const ctList = countryObj ? (countryObj.cities || []) : [];
                    let cityFound = false;
                    ctList.forEach(ct => {
                        const opt = document.createElement('option');
                        opt.value = ct;
                        opt.textContent = ct;
                        if (selectedCity && ct.toLowerCase() === selectedCity.toLowerCase()) {
                            opt.selected = true;
                            cityFound = true;
                        }
                        cityEl.appendChild(opt);
                    });
                    if (selectedCity && !cityFound) {
                        const opt = document.createElement('option');
                        opt.value = selectedCity;
                        opt.textContent = selectedCity;
                        opt.selected = true;
                        cityEl.appendChild(opt);
                    }
                }

                countryEl.onchange = function() {
                    selectedState = '';
                    selectedCity = '';
                    syncModalStateAndCity();
                };

                syncModalStateAndCity();
            };

            window.openEditAddressModal = function(addressId) {
                const addr = savedCheckoutAddresses[addressId];
                if (!addr) return;

                const modal = document.getElementById('checkout-edit-address-modal');
                const form = document.getElementById('checkout-edit-address-form');
                const errBox = document.getElementById('edit-address-error');
                if (errBox) {
                    errBox.classList.add('hidden');
                    errBox.innerHTML = '';
                }

                form.action = `/account/addresses/${addressId}`;
                document.getElementById('edit_modal_address_id').value = addressId;
                document.getElementById('edit_modal_label').value = addr.label || '';
                document.getElementById('edit_modal_name').value = addr.name || '';
                document.getElementById('edit_modal_phone').value = addr.phone || '';
                document.getElementById('edit_modal_line1').value = addr.line1 || '';
                document.getElementById('edit_modal_line2').value = addr.line2 || '';
                document.getElementById('edit_modal_postal_code').value = addr.postal_code || '';
                document.getElementById('edit_modal_is_default').checked = !!addr.is_default;

                populateEditModalGeographic(addr.country, addr.state, addr.city);

                modal.showModal();
            };

            window.handleCheckoutAddressUpdate = async function(e) {
                e.preventDefault();
                const form = e.target;
                const btn = document.getElementById('edit-address-submit-btn');
                const errBox = document.getElementById('edit-address-error');
                const addressId = document.getElementById('edit_modal_address_id').value;

                if (errBox) {
                    errBox.classList.add('hidden');
                    errBox.innerHTML = '';
                }
                btn.disabled = true;
                btn.textContent = 'Saving...';

                const formData = new FormData(form);

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        let msg = data.message || 'Validation failed. Please check the fields.';
                        if (data.errors) {
                            const list = Object.values(data.errors).flat().join('<br>');
                            msg += `<div class="mt-1 font-normal">${list}</div>`;
                        }
                        if (errBox) {
                            errBox.innerHTML = msg;
                            errBox.classList.remove('hidden');
                        }
                        return;
                    }

                    const addr = data.address;
                    if (addr) {
                        savedCheckoutAddresses[addr.id] = addr;

                        const nameEl = document.getElementById(`address-name-${addr.id}`);
                        if (nameEl) nameEl.textContent = addr.name;

                        const phoneEl = document.getElementById(`address-phone-${addr.id}`);
                        if (phoneEl) phoneEl.textContent = addr.phone;

                        const onelineEl = document.getElementById(`address-oneline-${addr.id}`);
                        if (onelineEl) onelineEl.textContent = addr.one_line;

                        let badgeEl = document.getElementById(`address-label-badge-${addr.id}`);
                        if (addr.label) {
                            if (badgeEl) {
                                badgeEl.textContent = addr.label;
                            } else if (nameEl && nameEl.parentElement) {
                                const badge = document.createElement('span');
                                badge.id = `address-label-badge-${addr.id}`;
                                badge.className = 'text-xs font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-md';
                                badge.textContent = addr.label;
                                nameEl.parentElement.prepend(badge);
                            }
                        } else if (badgeEl) {
                            badgeEl.remove();
                        }

                        if (addr.is_default) {
                            document.querySelectorAll('[id^="address-primary-badge-"]').forEach(el => el.remove());
                            if (nameEl && nameEl.parentElement) {
                                const primarySpan = document.createElement('span');
                                primarySpan.id = `address-primary-badge-${addr.id}`;
                                primarySpan.className = 'text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300';
                                primarySpan.textContent = 'PRIMARY';
                                nameEl.parentElement.appendChild(primarySpan);
                            }
                        }
                    }

                    document.getElementById('checkout-edit-address-modal').close();
                    if (window.showToast) {
                        window.showToast('Shipping address updated successfully.', 'success');
                    }
                } catch (err) {
                    console.error('Error updating address:', err);
                    form.submit();
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Save Changes';
                }
            };
        });
    </script>
@endsection
