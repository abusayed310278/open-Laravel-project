@extends('layouts.auth')

@section('title', 'Verify your phone number')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Verify your phone number</h1>
    <p class="text-sm text-gray-500 mb-6">
        We'll text a 6-digit code to <strong>{{ auth()->user()->phone }}</strong>.
    </p>

    @if (session('status') === 'otp-sent')
        <x-alert type="success" class="mb-5">Code sent — it expires in 10 minutes.</x-alert>
    @endif

    <form method="POST" action="{{ route('verification.phone.send') }}" class="mb-4">
        @csrf
        <x-button type="submit" variant="secondary" class="w-full">Send Code</x-button>
    </form>

    <form method="POST" action="{{ route('verification.phone.confirm') }}" class="space-y-4">
        @csrf
        <x-input label="6-digit code" name="code" type="text" inputmode="numeric" maxlength="6" />
        <x-button type="submit" class="w-full">Verify</x-button>
    </form>
@endsection
