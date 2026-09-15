@extends('layouts.auth')

@section('title', 'Log in')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Welcome back</h1>
    <p class="text-sm text-gray-500 mb-6">Log in to your Openbox account.</p>

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-input label="Email or phone number" name="login" type="text" autofocus autocomplete="username" :value="old('login')" />

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:underline">Forgot password?</a>
                @endif
            </div>
            <div class="relative">
                <input
                    id="password" name="password" type="password" autocomplete="current-password"
                    class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11"
                >
                <button type="button" data-password-toggle="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password">
                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
            @error('login')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="remember" value="1" class="w-4 h-4 rounded accent-brand-500">
            Remember Me For 30 Days.
        </label>

        <x-button type="submit" class="w-full">Log In</x-button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:underline">Register</a>
    </p>

    <script>
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
