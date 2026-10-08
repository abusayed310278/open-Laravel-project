@extends('layouts.app')

@section('title', 'Advanced Search')

@section('content')
    @php
        $field = 'w-full border border-gray-200 rounded-md px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-400';
        $label = 'block text-sm font-medium text-gray-700 mb-1.5';
        $check = 'flex items-center gap-2 text-sm text-gray-600';
        $navItem = fn (string $key) => 'flex items-center justify-between rounded-md px-3 py-2 text-sm transition-colors '
            . ($mode === $key ? 'bg-brand-50 font-semibold text-brand-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900');
        $navLinks = [
            'Items' => ['items' => 'Find items', 'seller' => 'By seller', 'item_number' => 'By item number'],
            'Stores' => ['store_items' => 'Items in stores', 'stores' => 'Find stores'],
        ];
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="['Advanced Search' => null]" class="mb-6" />

        <div class="grid lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1">
                <h1 class="text-xl font-semibold text-gray-900 mb-6">Advanced search</h1>

                <nav class="space-y-6" aria-label="Advanced search sections">
                    @foreach ($navLinks as $group => $links)
                        <div>
                            <h2 class="px-3 mb-2 text-sm font-semibold text-gray-900">{{ $group }}</h2>
                            @foreach ($links as $key => $text)
                                <a href="{{ route('search.advanced', ['mode' => $key]) }}" class="{{ $navItem($key) }}" @if ($mode === $key) aria-current="page" @endif>
                                    {{ $text }}
                                    @if ($mode === $key)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </nav>
            </aside>

            <div class="lg:col-span-3">
                @if ($mode === 'seller')
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Search by seller</h2>
                    <form method="GET" action="{{ route('shop') }}" class="rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
                        <div>
                            <label for="seller" class="{{ $label }}">Seller or store name</label>
                            <input id="seller" type="text" name="seller" required placeholder="e.g. Tech Hub" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="q" class="{{ $label }}">Keywords (optional)</label>
                            <input id="q" type="text" name="q" class="{{ $field }}">
                        </div>
                        <x-button type="submit">Search</x-button>
                    </form>
                @elseif ($mode === 'item_number')
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Search by item number</h2>
                    <form method="GET" action="{{ route('search.advanced') }}" class="rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
                        <input type="hidden" name="mode" value="item_number">
                        <div>
                            <label for="item_number" class="{{ $label }}">Item number or SKU</label>
                            <input id="item_number" type="text" name="item_number" value="{{ old('item_number', request('item_number')) }}" required class="{{ $field }}">
                            @error('item_number')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <x-button type="submit">Search</x-button>
                    </form>
                @elseif ($mode === 'store_items')
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Items in stores</h2>
                    <form method="GET" action="{{ route('shop') }}" class="rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
                        <input type="hidden" name="stores_only" value="1">
                        <div>
                            <label for="q" class="{{ $label }}">Keywords</label>
                            <input id="q" type="text" name="q" required placeholder="What are you looking for?" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="seller" class="{{ $label }}">Store name (optional)</label>
                            <input id="seller" type="text" name="seller" class="{{ $field }}">
                        </div>
                        <x-button type="submit">Search</x-button>
                    </form>
                @elseif ($mode === 'stores')
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Find stores</h2>
                    <form method="GET" action="{{ route('search.advanced') }}" class="rounded-xl border border-gray-200 p-5 sm:p-6 grid sm:grid-cols-2 gap-4">
                        <input type="hidden" name="mode" value="stores">
                        <div>
                            <label for="store_q" class="{{ $label }}">Store name</label>
                            <input id="store_q" type="text" name="q" value="{{ request('q') }}" class="{{ $field }}">
                        </div>
                        <div>
                            <label for="location" class="{{ $label }}">City or country</label>
                            <input id="location" type="text" name="location" value="{{ request('location') }}" class="{{ $field }}">
                        </div>
                        <div class="sm:col-span-2"><x-button type="submit">Find stores</x-button></div>
                    </form>

                    @if ($stores !== null)
                        <div class="mt-8">
                            <p class="text-sm text-gray-500 mb-4">{{ $stores->count() }} {{ \Illuminate\Support\Str::plural('store', $stores->count()) }} found</p>
                            <div class="grid sm:grid-cols-2 gap-4">
                                @foreach ($stores as $store)
                                    <a href="{{ $store['url'] }}" class="flex items-center gap-4 rounded-xl border border-gray-200 p-4 transition-colors hover:border-brand-500">
                                        @if ($store['logo'])
                                            <img src="{{ $store['logo'] }}" alt="" class="h-12 w-12 rounded-full object-cover">
                                        @else
                                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-500">{{ mb_strtoupper(mb_substr($store['name'], 0, 1)) }}</span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block truncate font-medium text-gray-900">{{ $store['name'] }}</span>
                                            @if ($store['location'])
                                                <span class="block truncate text-sm text-gray-500">{{ $store['location'] }}</span>
                                            @endif
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Find items</h2>

                    <form method="GET" action="{{ route('shop') }}" class="space-y-6">
                        {{-- Keywords --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6 space-y-4">
                            <div>
                                <label for="q" class="{{ $label }}">Enter keywords</label>
                                <input id="q" type="text" name="q" placeholder="e.g. iPhone 13 128GB" class="{{ $field }}">
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="match" class="{{ $label }}">Keyword match</label>
                                    <select id="match" name="match" class="{{ $field }}">
                                        <option value="all">All words, any order</option>
                                        <option value="any">Any words, any order</option>
                                        <option value="exact">Exact words, exact order</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="exclude" class="{{ $label }}">Exclude words</label>
                                    <input id="exclude" type="text" name="exclude" placeholder="e.g. cracked broken" class="{{ $field }}">
                                </div>
                            </div>

                            <label class="{{ $check }}">
                                <input type="checkbox" name="in_description" value="1" class="accent-brand-500">
                                Include title and description in search
                            </label>
                        </section>

                        {{-- Category / Brand --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="{{ $label }}">Category</label>
                                <select id="category" name="category" class="{{ $field }}">
                                    <option value="">All categories</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->slug }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="brand" class="{{ $label }}">Brand</label>
                                <select id="brand" name="brand" class="{{ $field }}">
                                    <option value="">All brands</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->slug }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </section>

                        {{-- Price --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">Price range (Tk)</h3>
                            <div class="flex items-center gap-3">
                                <input type="number" min="0" name="min_price" placeholder="Min" class="{{ $field }}">
                                <span class="text-gray-400">to</span>
                                <input type="number" min="0" name="max_price" placeholder="Max" class="{{ $field }}">
                            </div>
                        </section>

                        {{-- Condition / Grade --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6 grid sm:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Condition</h3>
                                <div class="space-y-2">
                                    @foreach (\App\Enums\ProductCondition::cases() as $condition)
                                        <label class="{{ $check }}">
                                            <input type="checkbox" name="condition[]" value="{{ $condition->value }}" class="accent-brand-500">
                                            {{ $condition->label() }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 mb-3">Grade</h3>
                                <div class="space-y-2">
                                    @foreach (\App\Enums\ProductGrade::cases() as $grade)
                                        <label class="{{ $check }}">
                                            <input type="checkbox" name="grade[]" value="{{ $grade->value }}" class="accent-brand-500">
                                            {{ $grade->label() }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        {{-- Options --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6">
                            <h3 class="text-base font-semibold text-gray-900 mb-3">Show only</h3>
                            <div class="grid sm:grid-cols-3 gap-2">
                                <label class="{{ $check }}"><input type="checkbox" name="free_shipping" value="1" class="accent-brand-500"> Free shipping</label>
                                <label class="{{ $check }}"><input type="checkbox" name="negotiable" value="1" class="accent-brand-500"> Price negotiable</label>
                                <label class="{{ $check }}"><input type="checkbox" name="in_stock" value="1" class="accent-brand-500"> In stock</label>
                            </div>
                        </section>

                        {{-- Results display --}}
                        <section class="rounded-xl border border-gray-200 p-5 sm:p-6 grid sm:grid-cols-2 gap-4">
                            <div>
                                <label for="sort" class="{{ $label }}">Sort by</label>
                                <select id="sort" name="sort" class="{{ $field }}">
                                    <option value="">Newest first</option>
                                    <option value="oldest">Oldest first</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                </select>
                            </div>
                            <div>
                                <label for="per_page" class="{{ $label }}">Results per page</label>
                                <select id="per_page" name="per_page" class="{{ $field }}">
                                    <option value="20">20</option>
                                    <option value="40">40</option>
                                    <option value="60">60</option>
                                </select>
                            </div>
                        </section>

                        <div class="flex items-center gap-3">
                            <x-button type="submit">Search</x-button>
                            <a href="{{ route('search.advanced') }}" class="text-sm text-gray-500 hover:text-gray-800">Reset</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
