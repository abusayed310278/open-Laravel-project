@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Security Settings</h1>

        <x-card>
            <form method="POST" action="{{ route('account.security.password') }}" class="space-y-4 max-w-sm">
                @csrf
                @method('PATCH')
                <x-input label="Current Password" name="current_password" type="password" />
                <x-input label="New Password" name="password" type="password" />
                <x-input label="Confirm Password" name="password_confirmation" type="password" />
                <x-button type="submit" variant="dark">Update Password</x-button>
            </form>
        </x-card>
    </div>
@endsection
