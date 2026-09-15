@extends('layouts.app')

@section('title', 'Your Wishlist')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <h1 class="text-2xl font-bold text-gray-900 mb-8">Your Wishlist</h1>

        @session('status')
            <x-alert type="success" class="mb-6">{{ $value }}</x-alert>
        @endsession

        @if ($wishlist->items->isEmpty())
            <div class="bg-gray-50 border border-gray-100 rounded-md p-12 text-center">
                <p class="text-gray-400 mb-4">Nothing saved yet.</p>
                <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($wishlist->items as $item)
                    <div class="bg-white border border-gray-100 rounded-md overflow-hidden">
                        <x-product-card
                            :title="$item->product->title"
                            :price="$item->product->price"
                            :compare-price="$item->product->compare_price"
                            :condition="$item->product->condition->value"
                            :key-features="$item->product->keyFeatures(3)"
                            :image="$item->product->images->first()?->url()"
                            :href="route('products.show', $item->product)"
                        />
                        <div class="p-3 pt-0 flex items-center gap-2">
                            <form method="POST" action="{{ route('wishlist.move-to-cart', $item->product) }}" class="flex-1">
                                @csrf
                                <x-button type="submit" size="sm" class="w-full justify-center">Move to Cart</x-button>
                            </form>
                            <form method="POST" action="{{ route('wishlist.toggle', $item->product) }}">
                                @csrf
                                <button type="submit" class="text-gray-300 hover:text-red-500 p-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
