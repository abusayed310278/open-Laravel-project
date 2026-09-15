@extends('layouts.app')

@section('title', 'Shop')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="['Shop' => null]" class="mb-6" />

        <div class="grid lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1">
                <form method="GET" class="space-y-6">
                    <div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search within results" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-800 mb-2">Category</p>
                        <div class="space-y-1.5">
                            @foreach ($categories as $category)
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="radio" name="category" value="{{ $category->slug }}" @checked(request('category') === $category->slug) class="accent-brand-500">
                                    {{ $category->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-800 mb-2">Condition</p>
                        <div class="space-y-1.5">
                            @foreach (\App\Enums\ProductCondition::cases() as $condition)
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="radio" name="condition" value="{{ $condition->value }}" @checked(request('condition') === $condition->value) class="accent-brand-500">
                                    {{ $condition->label() }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-800 mb-2">Brand</p>
                        <div class="space-y-1.5">
                            @foreach ($brands as $brand)
                                <label class="flex items-center gap-2 text-sm text-gray-600">
                                    <input type="radio" name="brand" value="{{ $brand->slug }}" @checked(request('brand') === $brand->slug) class="accent-brand-500">
                                    {{ $brand->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-gray-800 mb-2">Price range</p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                            <span class="text-gray-300">–</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                        </div>
                    </div>

                    <x-button type="submit" variant="secondary" class="w-full justify-center">Apply Filters</x-button>
                    <a href="{{ route('shop') }}" class="block text-center text-sm text-gray-400 hover:text-gray-600">Clear Filters</a>
                </form>
            </aside>

            <div class="lg:col-span-3">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</p>

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

                @if ($products->isEmpty())
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-12 text-center text-gray-400 text-sm">
                        No products match your filters yet.
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($products as $product)
                            <x-product-card
                                :title="$product->title"
                                :brand="$product->brand?->name"
                                :price="$product->price"
                                :compare-price="$product->compare_price"
                                :condition="$product->condition->value"
                                :key-features="$product->keyFeatures(3)"
                                :seller="$product->user->name"
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
    </div>
@endsection
