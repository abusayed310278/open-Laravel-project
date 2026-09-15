@extends('layouts.auth')

@section('title', 'Confirm password')

@section('content')
    <h1 class="text-xl font-bold text-gray-900 mb-1">Confirm your password</h1>
    <p class="text-sm text-gray-500 mb-6">This is a secure area. Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf
        <x-input label="Password" name="password" type="password" autofocus autocomplete="current-password" />
        <x-button type="submit" class="w-full">Confirm</x-button>
    </form>
@endsection
