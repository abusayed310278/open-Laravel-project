@extends('layouts.auth')

@section('title', 'Verify your email')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Verify your email</h1>
    <p class="text-sm text-gray-500 mb-6">
        Thanks for signing up! Before getting started, click the link we just emailed to
        <strong>{{ auth()->user()->email }}</strong>.
    </p>

    @if (session('status') === 'verification-link-sent')
        <x-alert type="success" class="mb-5">A new verification link has been sent to your email address.</x-alert>
    @endif

    <div class="flex items-center gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-button type="submit" variant="secondary">Resend Verification Email</x-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-button type="submit" variant="ghost">Log Out</x-button>
        </form>
    </div>
@endsection
