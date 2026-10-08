@extends('layouts.app')

@section('title', request('q') ? 'Search: ' . request('q') : 'Shop')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="request('q') ? ['Shop' => route('shop'), 'Search: ' . request('q') => null] : ['Shop' => null]" class="mb-6" />

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
                @if (request('q'))
                    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 bg-brand-50/50 border border-brand-100 rounded-xl px-5 py-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-brand-100 text-brand-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <div>
                                <h1 class="text-base sm:text-lg font-bold text-gray-900">
                                    Search results for <span class="text-brand-600">"{{ request('q') }}"</span>
                                </h1>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} found</p>
                            </div>
                        </div>
                        <a href="{{ route('shop') }}" class="text-xs font-semibold text-gray-600 hover:text-brand-700 bg-white hover:bg-gray-50 border border-gray-200 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            <span>Clear Search</span>
                        </a>
                    </div>
                @endif

                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500">Showing {{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results</p>

                    <form method="GET">
                        @foreach (explode('&', \Illuminate\Support\Arr::query(request()->except('sort', 'page'))) as $pair)
                            @continue($pair === '')
                            @php([$hiddenKey, $hiddenValue] = array_pad(array_map('urldecode', explode('=', $pair, 2)), 2, ''))
                            <input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">
                        @endforeach
                        <select name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-3 py-2 text-sm bg-white">
                            <option value="" @selected(!request('sort'))>Newest</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                        </select>
                    </form>
                </div>

                @if ($products->isEmpty())
                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-12 text-center text-gray-500 text-sm">
                        @if (request('q'))
                            <p class="text-base font-semibold text-gray-800 mb-1">No products found matching "{{ request('q') }}"</p>
                            <p class="text-gray-400 text-xs mb-4">Try checking your spelling, using more general terms, or clearing filters.</p>
                            <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold text-xs rounded-lg transition-colors">
                                View all products
                            </a>
                        @else
                            No products match your filters yet.
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
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
    </div>
@endsection
