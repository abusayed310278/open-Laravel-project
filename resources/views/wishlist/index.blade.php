@extends('layouts.app')

@section('title', 'Your Wishlist')

@section('content')
    @php
        $categories = $wishlist->items->pluck('product.category')->filter()->unique('id')->sortBy('name');
        $hasComparePrice = $wishlist->items->contains(fn ($i) => filled($i->product->compare_price) && $i->product->compare_price > $i->product->price);
        $conditions = $wishlist->items->pluck('product.condition')->filter()->unique()->values();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <span>Your Wishlist</span>
                <span class="js-wishlist-page-count text-base font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full">({{ $wishlist->items->count() }})</span>
            </h1>
        </div>

        @session('status')
            <x-alert type="success" class="mb-6">{{ $value }}</x-alert>
        @endsession

        {{-- FILTER & SORT TOOLBAR --}}
        <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/80 rounded-2xl p-4 sm:p-5 mb-6 shadow-2xs {{ $wishlist->items->isEmpty() ? 'hidden' : '' }}" id="wishlist-filter-toolbar">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                {{-- Search Input --}}
                <div class="lg:col-span-4 relative">
                    <input
                        type="text"
                        id="wishlist-filter-search"
                        placeholder="Search by name, brand..."
                        class="w-full pl-9 pr-8 py-2.5 text-sm bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <button type="button" id="wishlist-search-clear" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer p-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Category Filter --}}
                <div class="lg:col-span-3">
                    <select
                        id="wishlist-filter-category"
                        class="w-full py-2.5 px-3 text-sm bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all cursor-pointer"
                    >
                        <option value="">All Categories ({{ $wishlist->items->count() }})</option>
                        @foreach ($categories as $cat)
                            @php
                                $countForCat = $wishlist->items->where('product.category_id', $cat->id)->count();
                            @endphp
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $countForCat }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Condition Filter --}}
                <div class="lg:col-span-2">
                    <select
                        id="wishlist-filter-condition"
                        class="w-full py-2.5 px-3 text-sm bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all cursor-pointer"
                    >
                        <option value="">All Conditions</option>
                        @foreach ($conditions as $cond)
                            <option value="{{ strtolower($cond->value) }}">{{ $cond->label() }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div class="lg:col-span-3">
                    <select
                        id="wishlist-filter-sort"
                        class="w-full py-2.5 px-3 text-sm bg-gray-50 dark:bg-slate-900/50 border border-gray-200 dark:border-slate-700 rounded-xl text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all cursor-pointer"
                    >
                        <option value="latest">Sort: Recently Added</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                        <option value="title_asc">Name: A to Z</option>
                        <option value="title_desc">Name: Z to A</option>
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills & Status --}}
            <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-slate-700/60 flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex flex-wrap items-center gap-3.5 text-gray-500 dark:text-gray-400">
                    <span id="wishlist-count-indicator" class="font-medium">Showing all {{ $wishlist->items->count() }} items</span>

                    <span class="text-gray-300 dark:text-gray-600">&bull;</span>

                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        <input type="checkbox" id="wishlist-filter-stock" class="w-3.5 h-3.5 rounded accent-brand-500 text-brand-600">
                        <span>In stock only</span>
                    </label>

                    @if ($hasComparePrice)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer select-none text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                            <input type="checkbox" id="wishlist-filter-discount" class="w-3.5 h-3.5 rounded accent-brand-500 text-brand-600">
                            <span>On sale only</span>
                        </label>
                    @endif
                </div>

                <button
                    type="button"
                    id="wishlist-reset-btn"
                    class="hidden text-brand-600 hover:text-brand-700 dark:text-brand-400 font-semibold cursor-pointer hover:underline"
                >
                    Reset Filters
                </button>
            </div>
        </div>

        {{-- No matching filter results --}}
        <div id="wishlist-no-results" class="hidden bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/80 rounded-2xl p-12 text-center my-6 shadow-2xs">
            <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-700 text-gray-400 dark:text-gray-300 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200 mb-1">No items match your filter</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Try clearing some filters or searching for something else.</p>
            <button type="button" id="wishlist-no-results-reset" class="px-4 py-2 bg-brand-500 text-white rounded-xl text-xs font-semibold hover:bg-brand-600 transition-colors cursor-pointer shadow-xs">
                Clear All Filters
            </button>
        </div>

        {{-- Completely Empty State --}}
        <div id="wishlist-empty-state" class="bg-gray-50 dark:bg-slate-800/50 border border-gray-100 dark:border-slate-800 rounded-md p-12 text-center {{ $wishlist->items->isEmpty() ? '' : 'hidden' }}">
            <p class="text-gray-400 dark:text-gray-500 mb-4">Nothing saved yet.</p>
            <x-button as="a" :href="route('shop')">Browse the Marketplace</x-button>
        </div>

        {{-- Wishlist Items Grid --}}
        <div id="wishlist-items-grid" class="grid grid-cols-2 md:grid-cols-4 gap-4 {{ $wishlist->items->isEmpty() ? 'hidden' : '' }}">
            @foreach ($wishlist->items as $item)
                <div
                    class="js-wishlist-card bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-2xl overflow-hidden flex flex-col justify-between shadow-2xs hover:shadow-md transition-all duration-300"
                    data-wishlist-item-card="{{ $item->product->id }}"
                    data-title="{{ strtolower($item->product->title) }}"
                    data-brand="{{ strtolower($item->product->brand?->name ?? '') }}"
                    data-category-id="{{ (string) $item->product->category_id }}"
                    data-category-name="{{ strtolower($item->product->category?->name ?? '') }}"
                    data-condition="{{ strtolower($item->product->condition?->value ?? '') }}"
                    data-price="{{ (float) $item->product->price }}"
                    data-is-discounted="{{ ($item->product->compare_price && $item->product->compare_price > $item->product->price) ? '1' : '0' }}"
                    data-in-stock="{{ ($item->product->quantity > 0) ? '1' : '0' }}"
                    data-created-at="{{ $item->created_at?->timestamp ?? 0 }}"
                >
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
                        <form method="POST" action="{{ route('wishlist.move-to-cart', $item->product) }}" class="flex-1 js-move-to-cart-form">
                            @csrf
                            <x-button type="submit" size="sm" class="w-full justify-center rounded-xl py-2.5 text-xs sm:text-sm font-semibold shadow-xs cursor-pointer">
                                <span class="js-move-label">Move to Cart</span>
                            </x-button>
                        </form>
                        <button type="button" data-wishlist-remove-btn="{{ $item->product->id }}" class="shrink-0 w-[42px] h-[42px] flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-xl transition-all cursor-pointer border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-2xs" title="Remove from wishlist">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            const grid = document.getElementById('wishlist-items-grid');
            if (!grid) return;

            const searchInput = document.getElementById('wishlist-filter-search');
            const searchClear = document.getElementById('wishlist-search-clear');
            const categorySelect = document.getElementById('wishlist-filter-category');
            const conditionSelect = document.getElementById('wishlist-filter-condition');
            const sortSelect = document.getElementById('wishlist-filter-sort');
            const stockCheckbox = document.getElementById('wishlist-filter-stock');
            const discountCheckbox = document.getElementById('wishlist-filter-discount');
            const resetBtn = document.getElementById('wishlist-reset-btn');
            const noResults = document.getElementById('wishlist-no-results');
            const noResultsReset = document.getElementById('wishlist-no-results-reset');
            const countIndicator = document.getElementById('wishlist-count-indicator');
            const emptyState = document.getElementById('wishlist-empty-state');
            const toolbar = document.getElementById('wishlist-filter-toolbar');

            function getCards() {
                return Array.from(grid.querySelectorAll('.js-wishlist-card'));
            }

            let previousSort = 'latest';
            let searchTimeout = null;

            function filterAndSort(shouldSort = false) {
                const cards = getCards();
                const total = cards.length;

                if (total === 0) {
                    if (toolbar) toolbar.classList.add('hidden');
                    if (grid) grid.classList.add('hidden');
                    if (noResults) noResults.classList.add('hidden');
                    if (emptyState) emptyState.classList.remove('hidden');
                    return;
                }

                const query = (searchInput?.value || '').trim().toLowerCase();
                const catId = categorySelect?.value || '';
                const cond = (conditionSelect?.value || '').toLowerCase();
                const sort = sortSelect?.value || 'latest';
                const onlyInStock = stockCheckbox?.checked || false;
                const onlyDiscounted = discountCheckbox?.checked || false;

                if (searchClear) {
                    searchClear.classList.toggle('hidden', query.length === 0);
                }

                const hasActiveFilter = query !== '' || catId !== '' || cond !== '' || onlyInStock || onlyDiscounted || sort !== 'latest';
                if (resetBtn) {
                    resetBtn.classList.toggle('hidden', !hasActiveFilter);
                }

                let visibleCount = 0;

                cards.forEach(card => {
                    const title = card.dataset.title || '';
                    const brand = card.dataset.brand || '';
                    const cardCat = card.dataset.categoryId || '';
                    const cardCond = (card.dataset.condition || '').toLowerCase();
                    const inStock = card.dataset.inStock === '1';
                    const isDiscounted = card.dataset.isDiscounted === '1';

                    const matchesSearch = query === '' || title.includes(query) || brand.includes(query);
                    const matchesCategory = catId === '' || cardCat === catId;
                    const matchesCondition = cond === '' || cardCond === cond;
                    const matchesStock = !onlyInStock || inStock;
                    const matchesDiscount = !onlyDiscounted || isDiscounted;

                    const isVisible = matchesSearch && matchesCategory && matchesCondition && matchesStock && matchesDiscount;

                    card.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount++;
                });

                // Only rearrange DOM nodes if sort value actually changed or explicitly requested
                if (shouldSort || sort !== previousSort) {
                    previousSort = sort;
                    const sortedCards = cards.slice().sort((a, b) => {
                        if (sort === 'price_asc') {
                            return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
                        }
                        if (sort === 'price_desc') {
                            return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
                        }
                        if (sort === 'title_asc') {
                            return (a.dataset.title || '').localeCompare(b.dataset.title || '');
                        }
                        if (sort === 'title_desc') {
                            return (b.dataset.title || '').localeCompare(a.dataset.title || '');
                        }
                        return parseInt(b.dataset.createdAt || 0, 10) - parseInt(a.dataset.createdAt || 0, 10);
                    });

                    sortedCards.forEach(c => grid.appendChild(c));
                }

                if (countIndicator) {
                    countIndicator.textContent = hasActiveFilter
                        ? `Showing ${visibleCount} of ${total} items`
                        : `Showing all ${total} items`;
                }

                if (noResults) {
                    noResults.classList.toggle('hidden', visibleCount > 0);
                }
                grid.classList.toggle('hidden', visibleCount === 0);
            }

            function resetAll() {
                if (searchInput) searchInput.value = '';
                if (categorySelect) categorySelect.value = '';
                if (conditionSelect) conditionSelect.value = '';
                if (sortSelect) sortSelect.value = 'latest';
                if (stockCheckbox) stockCheckbox.checked = false;
                if (discountCheckbox) discountCheckbox.checked = false;
                filterAndSort(true);
                if (searchInput) searchInput.focus();
            }

            searchInput?.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => filterAndSort(false), 120);
            });

            searchClear?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                filterAndSort(false);
            });

            categorySelect?.addEventListener('change', () => filterAndSort(false));
            conditionSelect?.addEventListener('change', () => filterAndSort(false));
            sortSelect?.addEventListener('change', () => filterAndSort(true));
            stockCheckbox?.addEventListener('change', () => filterAndSort(false));
            discountCheckbox?.addEventListener('change', () => filterAndSort(false));
            resetBtn?.addEventListener('click', resetAll);
            noResultsReset?.addEventListener('click', resetAll);

            // Listen for wishlist item removal without creating an observer loop
            document.addEventListener('click', (e) => {
                if (e.target.closest('[data-wishlist-remove-btn]')) {
                    setTimeout(() => filterAndSort(false), 300);
                }
            });

            // Immediate Move to Cart without full page reload
            document.addEventListener('submit', async (e) => {
                const form = e.target.closest('.js-move-to-cart-form');
                if (!form) return;

                e.preventDefault();
                const card = form.closest('.js-wishlist-card');
                const btn = form.querySelector('button[type="submit"]');
                const label = form.querySelector('.js-move-label') || btn;
                const originalText = label ? label.textContent : 'Move to Cart';

                if (btn) btn.disabled = true;
                if (label) label.textContent = 'Moving...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token || ''
                        }
                    });

                    const data = await res.json();

                    if (!res.ok || data.success === false) {
                        if (window.showToast) {
                            window.showToast(data.message || 'Could not move item to cart.', 'error');
                        }
                        if (label) label.textContent = originalText;
                        if (btn) btn.disabled = false;
                        return;
                    }

                    // Update header cart count badge immediately
                    if (window.updateCartBadge && typeof data.cart_count !== 'undefined') {
                        window.updateCartBadge(data.cart_count);
                    }

                    // Show success toast
                    if (window.showToast) {
                        window.showToast(data.message || 'Item moved to cart.', 'success');
                    }

                    // Smoothly animate and remove card from grid
                    if (card) {
                        card.style.transition = 'all 0.3s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            card.remove();
                            filterAndSort(false);
                            const remainingCards = getCards();
                            if (remainingCards.length === 0) {
                                if (emptyState) emptyState.classList.remove('hidden');
                                if (toolbar) toolbar.classList.add('hidden');
                                if (grid) grid.classList.add('hidden');
                            }
                        }, 300);
                    }
                } catch (err) {
                    console.error('Move to cart error:', err);
                    if (window.showToast) {
                        window.showToast('Could not move item to cart. Please try again.', 'error');
                    }
                    if (label) label.textContent = originalText;
                    if (btn) btn.disabled = false;
                }
            });

            // Initial run
            filterAndSort(false);
        })();
    </script>
@endsection
