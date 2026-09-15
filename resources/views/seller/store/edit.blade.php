@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Store Profile')

@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.' : 'saler.';
    $storeUrl = $isBusiness ? route('stores.business', $profile->slug) : route('stores.saler', $profile->slug);
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Store Profile</x-slot:title>
        <x-slot:action>
            <a href="{{ $storeUrl }}" target="_blank" class="text-sm text-brand-600 font-medium hover:underline">View live store →</a>
        </x-slot:action>

        <form method="POST" action="{{ route($routePrefix.'store.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if ($isBusiness)
                <x-input label="Business name" name="business_name" type="text" :value="old('business_name', $profile->business_name)" />
                <x-textarea label="Description" name="description" rows="4" :value="old('description', $profile->description)" />
            @else
                <x-input label="Display name" name="display_name" type="text" :value="old('display_name', $profile->display_name)" />
                <x-textarea label="Bio" name="bio" rows="4" :value="old('bio', $profile->bio)" />
            @endif

            <div class="grid sm:grid-cols-2 gap-5">
                <div>
                    <x-file-upload :name="$isBusiness ? 'logo' : 'profile_photo'" :label="$isBusiness ? 'Logo' : 'Profile photo'" />
                    @php($currentPhoto = $isBusiness ? $profile->logo : $profile->profile_photo)
                    @if ($currentPhoto)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($currentPhoto) }}" class="w-14 h-14 rounded-md object-cover border border-gray-100 mt-3">
                    @endif
                </div>
                <div>
                    <x-file-upload name="cover_image" label="Cover image" hint="Wide banner, 1200×300 recommended" />
                    @if ($profile->cover_image)
                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($profile->cover_image) }}" class="w-full h-16 rounded-md object-cover border border-gray-100 mt-3">
                    @endif
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                @if ($isBusiness)
                    <x-input label="Address" name="address" type="text" :value="old('address', $profile->address)" />
                @else
                    <x-input label="Location" name="location" type="text" :value="old('location', $profile->location)" />
                @endif
                <x-input label="City" name="city" type="text" :value="old('city', $profile->city)" />
                <x-input label="Country" name="country" type="text" :value="old('country', $profile->country)" />

                @if ($isBusiness)
                    <x-input label="Phone" name="phone" type="tel" :value="old('phone', $profile->phone)" />
                    <x-input label="Website" name="website" type="url" :value="old('website', $profile->website)" />
                @endif
            </div>

            @if ($isBusiness)
                <div class="grid sm:grid-cols-3 gap-5">
                    <x-input label="Instagram" name="social_links[instagram]" type="url" :value="old('social_links.instagram', $profile->social_links['instagram'] ?? null)" />
                    <x-input label="Twitter / X" name="social_links[twitter]" type="url" :value="old('social_links.twitter', $profile->social_links['twitter'] ?? null)" />
                    <x-input label="WhatsApp" name="social_links[whatsapp]" type="text" :value="old('social_links.whatsapp', $profile->social_links['whatsapp'] ?? null)" />
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_store_active" value="1" @checked(old('is_store_active', $profile->is_store_active)) class="w-4 h-4 rounded accent-brand-500">
                Store is publicly visible
            </label>

            <x-button type="submit">Save Store Settings</x-button>
        </form>
    </x-card>
@endsection
