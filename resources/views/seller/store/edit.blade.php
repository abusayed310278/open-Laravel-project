@php
    $isBusiness = auth()->user()->isBusiness();
    $routePrefix = $isBusiness ? 'business.' : 'saler.';
    $storeUrl = ($profile && !empty($profile->slug)) 
        ? ($isBusiness ? route('stores.business', $profile->slug) : route('stores.saler', $profile->slug)) 
        : '#';
@endphp

@extends($isBusiness ? 'layouts.business' : 'layouts.saler')

@section('title', $isBusiness ? 'Store Profile' : 'Seller Profile')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>{{ $isBusiness ? 'Store Profile' : 'Seller Profile' }}</x-slot:title>
        <x-slot:action>
            @if ($storeUrl !== '#')
                <a href="{{ $storeUrl }}" target="_blank" class="text-sm text-brand-600 font-medium hover:underline">{{ $isBusiness ? 'View live store →' : 'View live profile →' }}</a>
            @endif
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
                @php
                    $currentPhoto = $profile->logo ?? $profile->profile_photo ?? null;
                    $getStorageUrl = function ($path) {
                        if (empty($path)) return null;
                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            return $path;
                        }
                        $path = ltrim($path, '/');
                        if (str_starts_with($path, 'public/')) {
                            $path = substr($path, 7);
                        }
                        if (str_starts_with($path, 'storage/')) {
                            return asset($path);
                        }
                        return Illuminate\Support\Facades\Storage::disk('public')->url($path);
                    };
                @endphp
                <div>
                    <x-file-upload 
                        :name="$isBusiness ? 'logo' : 'profile_photo'" 
                        :label="$isBusiness ? 'Logo' : 'Profile photo'" 
                        :value="$getStorageUrl($currentPhoto)" 
                    />
                </div>
                <div>
                    <x-file-upload 
                        name="cover_image" 
                        label="Cover image" 
                        hint="Wide banner, 1200×300 recommended" 
                        :value="$getStorageUrl($profile->cover_image ?? null)" 
                    />
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
                    <x-input label="Website" name="website" type="text" placeholder="https://yourstore.com" :value="old('website', $profile->website ?? '')" />
                @endif
            </div>

            @if ($isBusiness)
                @php($social = is_array($profile->social_links) ? $profile->social_links : (json_decode($profile->social_links ?? '', true) ?: []))
                <div class="grid sm:grid-cols-3 gap-5">
                    <x-input label="Instagram" name="social_links[instagram]" type="text" placeholder="https://instagram.com/yourstore or @handle" :value="old('social_links.instagram', $social['instagram'] ?? '')" />
                    <x-input label="Twitter / X" name="social_links[twitter]" type="text" placeholder="https://x.com/yourstore or @handle" :value="old('social_links.twitter', $social['twitter'] ?? '')" />
                    <x-input label="WhatsApp" name="social_links[whatsapp]" type="text" placeholder="+97455001122" :value="old('social_links.whatsapp', $social['whatsapp'] ?? '')" />
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="is_store_active" value="1" @checked(old('is_store_active', $profile->is_store_active)) class="w-4 h-4 rounded accent-brand-500">
                Store is publicly visible
            </label>

            <x-button type="submit">{{ $isBusiness ? 'Save Store Settings' : 'Save Profile Settings' }}</x-button>
        </form>
    </x-card>
@endsection
