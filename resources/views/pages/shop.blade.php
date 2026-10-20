@extends('layouts.app')

@section('title', request('q') ? 'Search: ' . request('q') : 'Shop')

@php
    $hasFilters = request()->hasAny(['category', 'condition', 'brand', 'min_rating', 'min_price', 'max_price', 'q']);
    $fieldClass = 'w-full bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500';
    $labelClass = 'block text-xs font-semibold text-gray-500 mb-1.5';

    $categoryLabel = null;
    foreach ($categories as $cat) {
        if ($cat->slug === request('category')) {
            $categoryLabel = $cat->name;
            break;
        }
        foreach ($cat->children ?? [] as $child) {
            if ($child->slug === request('category')) {
                $categoryLabel = $child->name;
                break 2;
            }
        }
    }
    $brandLabel = request('brand') ? optional($brands->firstWhere('slug', request('brand')))->name : null;
    $conditionLabel = request('condition') ? \App\Enums\ProductCondition::tryFrom(request('condition'))?->label() : null;

    $activeFilters = [];
    if (request('q')) {
        $activeFilters[] = ['label' => '"' . request('q') . '"', 'except' => ['q']];
    }
    if ($categoryLabel) {
        $activeFilters[] = ['label' => $categoryLabel, 'except' => ['category']];
    }
    if ($brandLabel) {
        $activeFilters[] = ['label' => $brandLabel, 'except' => ['brand']];
    }
    if ($conditionLabel) {
        $activeFilters[] = ['label' => $conditionLabel, 'except' => ['condition']];
    }
    if (request('min_rating')) {
        $activeFilters[] = ['label' => request('min_rating') . '+ ★', 'except' => ['min_rating']];
    }
    if (request('min_price') || request('max_price')) {
        if (request('min_price') && request('max_price')) {
            $priceLabel = '$' . request('min_price') . ' – $' . request('max_price');
        } elseif (request('min_price')) {
            $priceLabel = 'Min $' . request('min_price');
        } else {
            $priceLabel = 'Max $' . request('max_price');
        }
        $activeFilters[] = ['label' => $priceLabel, 'except' => ['min_price', 'max_price']];
    }
@endphp

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <x-breadcrumb :items="request('q') ? ['Shop' => route('shop'), 'Search: ' . request('q') => null] : ['Shop' => null]" class="mb-6" />

        <div class="grid lg:grid-cols-4 gap-8">
            {{-- Filters --}}
            <aside class="lg:col-span-1">
                <details class="group bg-white rounded-2xl border border-gray-200 lg:open:shadow-none" @if ($hasFilters) open @endif>
                    <summary class="flex items-center justify-between px-5 py-4 cursor-pointer list-none lg:pointer-events-none">
                        <span class="text-base font-bold text-gray-900">Filters</span>
                        @if ($hasFilters)
                            <a href="{{ route('shop', array_filter(['sort' => request('sort')])) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 lg:pointer-events-auto">Reset</a>
                        @endif
                    </summary>

                    <form method="GET" action="{{ route('shop') }}" class="px-5 pb-5 space-y-5 border-t border-gray-100 pt-5">
                        @if (request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        @if (request('q'))
                            <div>
                                <label class="{{ $labelClass }}">Search</label>
                                <input type="text" name="q" value="{{ request('q') }}" class="{{ $fieldClass }}">
                            </div>
                        @endif

                        <div>
                            <label for="filter-category" class="{{ $labelClass }}">Category</label>
                            <select id="filter-category" name="category" onchange="this.form.submit()" class="{{ $fieldClass }}">
                                <option value="">Any category</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                                    @foreach ($cat->children ?? [] as $child)
                                        <option value="{{ $child->slug }}" @selected(request('category') === $child->slug)>&nbsp;&nbsp;– {{ $child->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-brand" class="{{ $labelClass }}">Brand</label>
                            <select id="filter-brand" name="brand" onchange="this.form.submit()" class="{{ $fieldClass }}">
                                <option value="">Any brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filter-condition" class="{{ $labelClass }}">Condition</label>
                            <select id="filter-condition" name="condition" onchange="this.form.submit()" class="{{ $fieldClass }}">
                                <option value="">Any condition</option>
                                @foreach (\App\Enums\ProductCondition::cases() as $condition)
                                    <option value="{{ $condition->value }}" @selected(request('condition') === $condition->value)>{{ $condition->label() }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">Rating</span>
                            <div class="space-y-1.5">
                                @foreach (['' => 'Any rating', '3' => '3★ & up', '4' => '4★ & up', '4.5' => '4.5★ & up'] as $value => $label)
                                    <label class="flex items-center gap-2.5 text-sm text-gray-700 cursor-pointer">
                                        <input type="radio" name="min_rating" value="{{ $value }}" onchange="this.form.submit()" @checked((string) request('min_rating') === (string) $value) class="w-4 h-4 accent-brand-500 cursor-pointer">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <span class="{{ $labelClass }}">Price</span>
                            <div class="flex items-center gap-2">
                                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" min="0" class="{{ $fieldClass }}">
                                <span class="text-gray-300">–</span>
                                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" min="0" class="{{ $fieldClass }}">
                            </div>
                        </div>

                        <button type="submit" class="w-full py-2.5 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors cursor-pointer">
                            Apply filters
                        </button>
                    </form>
                </details>
                <script>
                    if (window.matchMedia('(min-width: 1024px)').matches) {
                        document.currentScript.previousElementSibling.open = true;
                    }
                </script>
            </aside>

            {{-- Results --}}
            <div class="lg:col-span-3">
                @if (request('q'))
                    <h1 class="text-xl font-bold text-gray-900 mb-1">Results for <span class="text-brand-600">"{{ request('q') }}"</span></h1>
                @else
                    <h1 class="text-xl font-bold text-gray-900 mb-1">Shop</h1>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                    <p class="text-sm text-gray-500">
                        Found <span class="font-bold text-gray-900">{{ $products->total() }}</span> {{ \Illuminate\Support\Str::plural('product', $products->total()) }}
                        @if ($products->total() > 0)
                            · showing {{ $products->firstItem() }}–{{ $products->lastItem() }}
                        @endif
                    </p>

                    <form method="GET" class="flex items-center gap-2">
                        @foreach (explode('&', \Illuminate\Support\Arr::query(request()->except('sort', 'page'))) as $pair)
                            @continue($pair === '')
                            @php([$hiddenKey, $hiddenValue] = array_pad(array_map('urldecode', explode('=', $pair, 2)), 2, ''))
                            <input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">
                        @endforeach
                        <label for="sort" class="text-sm text-gray-500">Sort by</label>
                        <select id="sort" name="sort" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                            <option value="" @selected(!request('sort'))>Newest</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Price: High to Low</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                        </select>
                    </form>
                </div>

                @if ($hasFilters)
                    <div class="flex flex-wrap items-center gap-2 mb-6">
                        @foreach ($activeFilters as $filter)
                            <a href="{{ route('shop', request()->except(array_merge($filter['except'], ['page']))) }}" class="inline-flex items-center gap-1.5 pl-3 pr-2 py-1 bg-brand-50 text-brand-700 text-xs font-semibold rounded-full border border-brand-100 hover:bg-brand-100">
                                {{ $filter['label'] }}
                                <span aria-hidden="true" class="text-brand-400">&times;</span>
                            </a>
                        @endforeach
                        <a href="{{ route('shop', array_filter(['sort' => request('sort')])) }}" class="text-xs font-semibold text-gray-400 hover:text-gray-600">
                            Clear all
                        </a>
                    </div>
                @endif

                @if ($products->isEmpty())
                    <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center">
                        <p class="text-base font-semibold text-gray-800">
                            {{ request('q') ? 'No products found for "' . request('q') . '"' : 'No products match your filters' }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1 mb-5">Try different keywords or clear your filters.</p>
                        <a href="{{ route('shop') }}" class="inline-flex px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-lg transition-colors">
                            View all products
                        </a>
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
                                :rating="$product->approved_reviews_count ? (float) $product->approved_reviews_avg_rating : null"
                                :rating-count="$product->approved_reviews_count ?: null"
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
