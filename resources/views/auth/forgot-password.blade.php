@extends('layouts.auth')

@section('title', 'Reset your password')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Reset your password</h1>
    <p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a link to reset your password.</p>

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-input label="Email" name="email" type="email" autofocus autocomplete="username" :value="old('email')" />
        <x-button type="submit" class="w-full">Send Reset Link</x-button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-6">
        <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:underline">Back to login</a>
    </p>
@endsection
