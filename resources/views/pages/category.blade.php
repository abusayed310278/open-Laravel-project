@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :items="['Categories' => route('categories.index'), $category->name => null]" class="mb-6" />

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-gray-500 max-w-2xl mb-8">{{ $category->description }}</p>
        @endif

        @if ($category->children->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                @foreach ($category->children as $child)
                    <a href="{{ route('categories.show', $child) }}" class="flex items-center gap-2.5 p-4 rounded-xl bg-white border border-gray-100 hover:border-gray-900 shadow-2xs hover:shadow-md transition-all">
                        <div class="w-8 h-8 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center text-gray-900 text-xs font-bold">
                            {{ strtoupper(substr($child->name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-semibold text-gray-800">{{ $child->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if (isset($products) && $products->isNotEmpty())
            <div class="mb-6 flex items-center justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-900 dark:text-white">{{ $products->firstItem() }}</span> to <span class="font-semibold text-gray-900 dark:text-white">{{ $products->lastItem() }}</span> of <span class="font-semibold text-gray-900 dark:text-white">{{ $products->total() }}</span> products
                </p>
                <a href="{{ route('shop', ['category' => $category->slug]) }}" class="text-xs sm:text-sm font-semibold text-brand-600 dark:text-brand-400 hover:underline">
                    View in Shop with Filters &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($products as $product)
                    <x-product-card
                        :id="$product->id"
                        :product="$product"
                        :title="$product->title"
                        :price="$product->price"
                        :compare-price="$product->compare_price"
                        :category="$product->category?->name ?? 'Electronics'"
                        :brand="$product->brand?->name"
                        :condition="$product->condition?->value"
                        :key-features="$product->keyFeatures(3)"
                        :image="$product->primaryImageUrl()"
                        :href="route('products.show', $product)"
                    />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @else
            <div class="bg-gray-50 dark:bg-slate-800/60 border border-gray-100 dark:border-slate-700/60 rounded-xl p-12 text-center text-gray-400 text-sm">
                No products found in this category yet.
            </div>
        @endif
    </div>
@endsection
