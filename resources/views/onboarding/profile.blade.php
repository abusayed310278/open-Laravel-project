@php($maxWidth = 'max-w-lg')
@extends('layouts.auth')

@section('title', 'Finish setting up your store')

@section('content')
    @php($isBusiness = auth()->user()->role === \App\Enums\UserRole::Business)

    <h1 class="text-xl font-bold text-gray-900 mb-1">Finish setting up your store</h1>
    <p class="text-sm text-gray-500 mb-6">
        A few more details before your {{ $isBusiness ? 'business' : 'saler' }} account is ready. You can always
        update these later from your dashboard.
    </p>

    <form method="POST" action="{{ route('onboarding.profile.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <x-file-upload name="logo" :label="$isBusiness ? 'Store logo' : 'Profile photo'" hint="PNG or JPG up to 2MB" />

        <x-textarea :label="$isBusiness ? 'About your business' : 'About you'" name="description" :value="old('description')" rows="4" />

        <div class="grid sm:grid-cols-2 gap-4">
            <x-input label="City" name="city" type="text" :value="old('city')" />
            <x-input label="Country" name="country" type="text" :value="old('country')" />
        </div>

        @if ($isBusiness)
            <div class="grid sm:grid-cols-2 gap-4">
                <x-input label="Business phone (optional)" name="phone" type="tel" :value="old('phone')" />
                <x-input label="Website (optional)" name="website" type="url" :value="old('website')" />
            </div>
        @endif

        <x-button type="submit" class="w-full">Finish Setup</x-button>
    </form>
@endsection
