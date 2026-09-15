@extends('layouts.auth')

@section('title', 'Set a new password')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Set a new password</h1>
    <p class="text-sm text-gray-500 mb-6">Choose a strong password for your account.</p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">
        @error('email')
            <p class="text-xs text-red-500 -mt-2 mb-2">{{ $message }}</p>
        @enderror

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
            <div class="relative">
                <input
                    id="password" name="password" type="password" autofocus autocomplete="new-password" required
                    data-strength-for="strength-bar"
                    class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11"
                >
                <button type="button" data-password-toggle="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password">
                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            <div id="strength-bar" class="h-1 rounded-md transition-all mt-2 bg-gray-100"></div>
            @error('password')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
            <div class="relative">
                <input
                    id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                    class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent pr-11"
                >
                <button type="button" data-password-toggle="password_confirmation" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none" aria-label="Show password confirmation">
                    <svg class="w-4 h-4 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg class="w-4 h-4 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        <x-button type="submit" class="w-full">Reset Password</x-button>
    </form>

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
