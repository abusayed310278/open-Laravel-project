@extends('layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :items="['Stores' => null]" class="mb-6" />
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Trusted Sellers</h1>

        @if ($businesses->isEmpty() && $salers->isEmpty())
            <p class="text-gray-400">No stores yet — check back soon.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                @foreach ($businesses as $business)
                    @php
                        $image = $business->logoUrl() ?? $business->user?->profile?->avatarUrl() ?? ($business->user?->avatar ? \App\Support\MediaUrl::resolve($business->user->avatar) : null);
                    @endphp
                    <x-vendor-card
                        :name="$business->business_name"
                        :image="$image"
                        :href="route('stores.business', $business->slug)"
                    />
                @endforeach
                @foreach ($salers as $saler)
                    @php
                        $image = $saler->logoUrl() ?? $saler->user?->profile?->avatarUrl() ?? ($saler->user?->avatar ? \App\Support\MediaUrl::resolve($saler->user->avatar) : null);
                    @endphp
                    <x-vendor-card
                        :name="$saler->display_name"
                        :image="$image"
                        :href="route('stores.saler', $saler->slug)"
                    />
                @endforeach
            </div>
        @endif
    </div>
@endsection
