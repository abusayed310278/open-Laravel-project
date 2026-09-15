@extends('layouts.app')

@section('title', $storeName)

@section('content')
    <div class="bg-gray-100 h-40 sm:h-56 relative">
        @if ($coverImage)
            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($coverImage) }}" alt="" class="w-full h-full object-cover">
        @endif
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-10 sm:-mt-12 pb-6">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-md bg-white border-4 border-white shadow-sm overflow-hidden flex-shrink-0">
                @if ($logo)
                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-brand-50 flex items-center justify-center text-brand-500 text-2xl font-bold">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="flex-1 pb-1">
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-gray-900">{{ $storeName }}</h1>
                    @if ($isVerified)
                        <x-verified-badge />
                    @endif
                </div>
                @if ($location)
                    <p class="text-sm text-gray-500 mt-0.5">📍 {{ $location }}</p>
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
                            :title="$product->title"
                            :price="$product->price"
                            :compare-price="$product->compare_price"
                            :condition="$product->condition->value"
                            :key-features="$product->keyFeatures(3)"
                            :image="$product->images->first()?->url()"
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
