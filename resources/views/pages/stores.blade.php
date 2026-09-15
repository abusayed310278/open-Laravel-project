@extends('layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :items="['Stores' => null]" class="mb-6" />
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Trusted Sellers</h1>

        @if ($businesses->isEmpty() && $salers->isEmpty())
            <p class="text-gray-400">No stores yet — check back soon.</p>
        @else
            <div class="grid sm:grid-cols-3 md:grid-cols-6 gap-4">
                @foreach ($businesses as $business)
                    <x-vendor-card :name="$business->business_name" :href="route('stores.business', $business->slug)" />
                @endforeach
                @foreach ($salers as $saler)
                    <x-vendor-card :name="$saler->display_name" :href="route('stores.saler', $saler->slug)" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
