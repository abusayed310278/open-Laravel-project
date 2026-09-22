@extends('layouts.app')

@section('title', $storeName)

@section('content')
@php
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

    $resolvedCover = $getStorageUrl($coverImage) ?? asset('images/default-cover.svg');
    $resolvedLogo = $getStorageUrl($logo);
@endphp

    <div class="h-44 sm:h-64 relative overflow-hidden bg-gray-900">
        <img src="{{ $resolvedCover }}" alt="{{ $storeName }} Cover" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6 -mt-12 sm:-mt-16 pb-6 relative z-10">
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-black/10 flex-shrink-0 relative overflow-hidden">
                @if ($resolvedLogo)
                    <img src="{{ $resolvedLogo }}" 
                         alt="{{ $storeName }}" 
                         class="w-full h-full object-cover rounded-xl"
                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                    >
                    <div class="hidden w-full h-full rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-black shadow-inner">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                @else
                    <div class="w-full h-full rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-black shadow-inner">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="flex-1 pb-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $storeName }}</h1>
                    @if ($isVerified)
                        <x-verified-badge />
                    @endif
                </div>
                @if ($location)
                    <p class="text-sm text-gray-500 mt-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $location }}
                    </p>
                @endif
            </div>

            <x-button variant="secondary">Message Seller</x-button>
        </div>

        @if ($bio)
            <p class="text-gray-500 leading-relaxed max-w-2xl pb-8">{{ $bio }}</p>
        @endif

        <div class="flex items-center justify-between pb-6 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Products</h2>

            <form method="GET">
                @foreach (request()->except('sort', 'page') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <select name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-3 py-2 text-sm bg-white">
                    <option value="" @selected(!request('sort'))>Newest</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                </select>
            </form>
        </div>

        <div class="py-8">
            @if ($products->isEmpty())
                <div class="bg-gray-50 border border-gray-100 rounded-md p-12 text-center text-gray-400 text-sm">
                    No products listed yet.
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($products as $product)
                        <x-product-card
                            :id="$product->id"
                            :product="$product"
                            :title="$product->title"
                            :category="$product->category?->name ?? $product->brand?->name ?? 'Electronics'"
                            :brand="$product->brand?->name"
                            :price="$product->price"
                            :compare-price="$product->compare_price"
                            :image="$product->primaryImageUrl()"
                            :href="route('products.show', $product)"
                        />
                    @endforeach
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$products" />
                </div>
            @endif
        </div>
    </div>
@endsection
