@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
            <span>Wishlist</span>
            <span class="js-wishlist-page-count text-sm font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">({{ $wishlist->items->count() }})</span>
        </h1>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        <div id="wishlist-empty-state" class="{{ $wishlist->items->isEmpty() ? '' : 'hidden' }}">
            <x-card>
                <div class="text-center py-10">
                    <p class="text-gray-400 mb-4">Nothing saved yet.</p>
                    <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
                </div>
            </x-card>
        </div>

        <div id="wishlist-items-grid" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 {{ $wishlist->items->isEmpty() ? 'hidden' : '' }}">
            @foreach ($wishlist->items as $item)
                <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl overflow-hidden flex flex-col justify-between shadow-2xs hover:shadow-md transition-all duration-300" data-wishlist-item-card="{{ $item->product->id }}">
                    <div class="flex-1 flex flex-col [&_.group]:border-none [&_.group]:rounded-none [&_.group]:shadow-none">
                        <x-product-card
                            :id="$item->product->id"
                            :product="$item->product"
                            :title="$item->product->title"
                            :price="$item->product->price"
                            :compare-price="$item->product->compare_price"
                            :category="$item->product->category?->name ?? $item->product->brand?->name ?? 'Electronics'"
                            :brand="$item->product->brand?->name"
                            :condition="$item->product->condition->value"
                            :key-features="$item->product->keyFeatures(3)"
                            :image="$item->product->primaryImageUrl()"
                            :href="route('products.show', $item->product)"
                        />
                    </div>
                    <div class="p-3.5 sm:p-4 border-t border-gray-100 dark:border-slate-700/60 bg-gray-50/60 dark:bg-slate-800/80 flex items-center gap-2.5">
                        <form method="POST" action="{{ route('wishlist.move-to-cart', $item->product) }}" class="flex-1">
                            @csrf
                            <x-button type="submit" size="sm" class="w-full justify-center rounded-xl py-2.5 text-xs sm:text-sm font-semibold shadow-xs">Move to Cart</x-button>
                        </form>
                        <button type="button" data-wishlist-remove-btn="{{ $item->product->id }}" class="shrink-0 w-[42px] h-[42px] flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-xl transition-all cursor-pointer border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs" title="Remove from wishlist">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
