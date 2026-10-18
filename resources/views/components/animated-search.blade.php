@props([
    'class' => '',
    'placeholder' => 'Search electronics...',
    'action' => null,
])

@php
    $actionUrl = $action ?? route('search');
    $currentQuery = request('q', '');
@endphp

<form
    id="searchForm"
    action="{{ $actionUrl }}"
    method="GET"
    class="w-full relative animated-search-form {{ $class }}"
>
    <div
        id="searchBox"
        class="search-box relative flex items-center w-full h-10 sm:h-11 bg-[#f8fafc] dark:bg-slate-900/90 hover:bg-gray-100/90 dark:hover:bg-slate-800/90 focus-within:bg-white dark:focus-within:bg-slate-900 border border-gray-200 dark:border-slate-700 hover:border-gray-300 dark:hover:border-slate-600 focus-within:border-brand-500 dark:focus-within:border-brand-500 rounded-lg shadow-2xs transition-all duration-200 pl-3 sm:pl-4 pr-1.5 sm:pr-2 overflow-hidden group"
    >
        {{-- Left: Animated Icon Wrapper (Search Icon + Success Check Icon) --}}
        <button
            type="submit"
            id="iconWrapper"
            class="icon-wrapper relative flex items-center justify-center w-5 h-5 flex-shrink-0 mr-2 text-gray-400 group-focus-within:text-brand-500 hover:text-gray-900 dark:hover:text-white transition-colors cursor-pointer border-0 bg-transparent p-0"
            title="Search"
            aria-label="Search"
        >
            {{-- Default Search Magnifier Icon --}}
            <svg
                id="searchIcon"
                class="search-icon w-4 h-4 transition-transform duration-200"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            {{-- Success Icon (Animated Checkmark on submission) --}}
            <svg
                id="successIcon"
                class="success-icon w-4 h-4 text-emerald-500 absolute opacity-0 scale-50 transition-all duration-200 pointer-events-none"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </button>

        {{-- Center: Search Input --}}
        <input
            id="searchInput"
            type="text"
            name="q"
            value="{{ $currentQuery }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="search-input w-full h-full bg-transparent border-0 p-0 text-xs sm:text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 dark:placeholder:text-gray-500 focus:outline-none focus:ring-0 leading-normal"
        />

        {{-- Clear Query Button (Visible when input has value) --}}
        <button
            type="button"
            id="clearButton"
            class="clear-button text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 p-1 mr-1 rounded-full transition-all duration-150 {{ $currentQuery ? 'opacity-100 scale-100' : 'opacity-0 scale-75 pointer-events-none' }}"
            title="Clear search"
            aria-label="Clear search"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Right Controls: Search Submit Button + Advanced (Sliders) Button --}}
        <div class="flex items-center gap-1.5 flex-shrink-0">
            {{-- Search Submit Button --}}
            <button
                type="submit"
                id="searchSubmitBtn"
                class="search-submit-btn relative inline-flex items-center justify-center px-3 sm:px-3.5 py-1.5 h-7 sm:h-8 rounded-md text-xs font-semibold shadow-xs transition-all duration-200 cursor-pointer overflow-hidden flex-shrink-0"
                title="Search"
                aria-label="Search"
            >
                <span id="submitButtonText" class="submit-button-text transition-opacity duration-150">Search</span>
                <div id="spinner" class="spinner absolute inset-0 m-auto w-3.5 h-3.5 border-2 border-current border-t-transparent rounded-full opacity-0 pointer-events-none transition-opacity duration-150"></div>
            </button>

            {{-- Advanced Search (Sliders Icon Button) --}}
            <a
                href="{{ route('search.advanced') }}"
                id="searchButton"
                class="advanced-search-btn relative inline-flex items-center justify-center w-7 h-7 sm:w-8 sm:h-8 rounded-md shadow-xs transition-all duration-200 cursor-pointer overflow-hidden flex-shrink-0 group/btn"
                title="Advanced Search"
                aria-label="Advanced Search"
            >
                {{-- Sliders / Adjustments Horizontal Icon --}}
                <svg
                    id="buttonIcon"
                    class="button-icon w-4 h-4 transition-transform duration-200 group-hover/btn:scale-110"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                </svg>
            </a>
        </div>

        {{-- Bottom: Animated Laser Scanning Line --}}
        <div id="scanningLine" class="scanning-line"></div>
    </div>

    {{-- Live Search Suggestions Dropdown (Star Tech Style) --}}
    <div
        id="searchSuggestionsDropdown"
        class="search-suggestions-dropdown absolute left-0 right-0 top-full mt-2 bg-white dark:bg-slate-900 border border-gray-200/90 dark:border-slate-700/80 rounded-xl shadow-2xl z-50 overflow-hidden hidden transition-all duration-200"
    >
        {{-- Header: Filter Tabs (Products / Categories) & Spinner --}}
        <div class="flex items-center gap-2 p-2.5 sm:p-3 border-b border-gray-100 dark:border-slate-800 bg-gray-50/80 dark:bg-slate-900/90">
            <button
                type="button"
                id="tabProductsBtn"
                class="suggest-tab-btn active"
                data-tab="products"
            >
                Products
                <span id="productsCountBadge" class="ml-1 text-[10px] font-normal opacity-90"></span>
            </button>
            <button
                type="button"
                id="tabCategoriesBtn"
                class="suggest-tab-btn"
                data-tab="categories"
            >
                Categories
                <span id="categoriesCountBadge" class="ml-1 text-[10px] font-normal opacity-90"></span>
            </button>
            <div id="suggestLoadingSpinner" class="ml-auto hidden items-center gap-1.5 text-xs text-gray-400">
                <svg class="animate-spin w-3.5 h-3.5 text-[#e03a14]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[11px] hidden sm:inline">Searching...</span>
            </div>
        </div>

        {{-- Content Area --}}
        <div id="suggestContentArea" class="max-h-[360px] sm:max-h-[420px] overflow-y-auto overscroll-contain">
            {{-- Products List Container --}}
            <div id="suggestProductsList" class="divide-y divide-gray-100 dark:divide-slate-800/80"></div>

            {{-- Categories List Container --}}
            <div id="suggestCategoriesList" class="divide-y divide-gray-100 dark:divide-slate-800/80 hidden"></div>

            {{-- Empty State --}}
            <div id="suggestEmptyState" class="p-8 text-center hidden">
                <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <p class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300">No matching results found</p>
                <p class="text-[11px] text-gray-400 mt-0.5">Try searching with a different keyword</p>
            </div>
        </div>

        {{-- Footer: See All Results Link --}}
        <div id="suggestFooter" class="p-2.5 sm:p-3 text-center border-t border-gray-100 dark:border-slate-800 bg-gray-50/60 dark:bg-slate-900/60">
            <a
                href="{{ route('search') }}"
                id="seeAllResultsLink"
                class="suggest-see-all-link inline-block select-none"
            >
                See all results
            </a>
        </div>
    </div>
</form>

<style>
    /* Search Box Focus Glow */
    .search-box:focus-within {
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--color-brand-500) 25%, transparent);
    }

    /* Icon Animations */
    .search-box:focus-within .search-icon {
        transform: scale(1.08) rotate(-4deg);
    }

    .search-box.is-typing .search-icon {
        animation: searchIconPulse 0.6s ease infinite alternate;
    }

    @keyframes searchIconPulse {
        0% { transform: scale(1.05); }
        100% { transform: scale(1.18) rotate(3deg); color: #eab308; }
    }

    /* Laser Scanning Line Animation */
    .search-box .scanning-line {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: transparent;
        overflow: hidden;
        pointer-events: none;
    }

    .search-box .scanning-line::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: -40%;
        width: 40%;
        background: linear-gradient(90deg, transparent, var(--color-brand-400) 30%, var(--color-brand-600) 70%, transparent);
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    /* Active Scanning on Focus */
    .search-box:focus-within .scanning-line::after {
        opacity: 0.9;
        animation: laserScan 2.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }

    /* High-Speed Scan When Submitting / Searching */
    .search-box.is-searching .scanning-line::after {
        opacity: 1;
        width: 60%;
        background: linear-gradient(90deg, transparent, #eab308 20%, #38bdf8 50%, #2563eb 80%, transparent);
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.8), 0 0 4px rgba(234, 179, 8, 0.8);
        animation: laserScanFast 0.65s linear infinite;
    }

    @keyframes laserScan {
        0% { left: -40%; }
        100% { left: 100%; }
    }

    @keyframes laserScanFast {
        0% { left: -60%; }
        100% { left: 100%; }
    }

    /* Spinner Animation */
    @keyframes searchSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .search-box.is-searching .spinner {
        opacity: 1;
        animation: searchSpin 0.7s linear infinite;
    }

    .search-box.is-searching .submit-button-text,
    .search-box.is-searching .button-text {
        opacity: 0;
    }

    .search-box.is-success .search-icon {
        opacity: 0;
        transform: scale(0.3) rotate(45deg);
    }

    .search-box.is-success .success-icon {
        opacity: 1;
        transform: scale(1.1) rotate(0deg);
    }
    /* Custom scrollbar for suggestions */
    #suggestContentArea::-webkit-scrollbar {
        width: 6px;
    }
    #suggestContentArea::-webkit-scrollbar-track {
        background: transparent;
    }
    #suggestContentArea::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.4);
        border-radius: 9999px;
    }
    #suggestContentArea::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.7);
    }

    /* Suggestion Tabs Styling */
    .suggest-tab-btn {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        line-height: 1.2;
        font-weight: 600;
        cursor: pointer;
        user-select: none;
        transition: all 0.15s ease;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
        color: #374151;
    }

    .suggest-tab-btn:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    .suggest-tab-btn.active {
        background-color: #e03a14 !important;
        color: #ffffff !important;
        border-color: #e03a14 !important;
        font-weight: 700;
        box-shadow: 0 1px 3px rgba(224, 58, 20, 0.3);
    }

    :is(.dark) .suggest-tab-btn {
        background-color: #1e293b;
        color: #cbd5e1;
        border-color: #334155;
    }

    :is(.dark) .suggest-tab-btn:hover {
        background-color: #334155;
        color: #f8fafc;
    }

    :is(.dark) .suggest-tab-btn.active {
        background-color: #e03a14 !important;
        color: #ffffff !important;
        border-color: #e03a14 !important;
    }

    /* Product Title & Price Colors */
    .suggest-prod-title {
        color: #111827;
        font-weight: 500;
        font-size: 13px;
        line-height: 1.35;
        transition: color 0.15s ease;
    }
    :is(.dark) .suggest-prod-title {
        color: #f1f5f9;
    }
    .group:hover .suggest-prod-title {
        color: #e03a14;
    }

    .suggest-prod-price {
        color: #e03a14 !important;
        font-weight: 700;
        font-size: 13px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .suggest-prod-compare {
        color: #9ca3af;
        text-decoration: line-through;
        font-size: 11px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }
    .suggest-out-of-stock {
        color: #e03a14 !important;
        font-weight: 600;
        font-size: 11px;
    }
    .suggest-see-all-link {
        color: #e03a14 !important;
        font-weight: 600;
        font-size: 13px;
        transition: color 0.15s ease;
    }
    .suggest-see-all-link:hover {
        color: #b91c1c !important;
        text-decoration: underline;
    }

    /* Search Bar Action Buttons (Dynamic Admin Brand Color) */
    .search-submit-btn,
    .advanced-search-btn {
        background-color: var(--color-brand-primary, #f59e0b) !important;
        color: var(--color-brand-contrast, #0f172a) !important;
        border-color: transparent !important;
        transition: background-color 0.15s ease, color 0.15s ease, transform 0.15s ease;
    }
    .search-submit-btn:hover,
    .advanced-search-btn:hover {
        background-color: var(--color-brand-hover, #fff200) !important;
        color: var(--color-brand-hover-text, #000000) !important;
    }
    .search-submit-btn svg,
    .advanced-search-btn svg,
    .search-submit-btn .submit-button-text {
        color: var(--color-brand-contrast, #0f172a) !important;
    }
    .search-submit-btn:hover svg,
    .advanced-search-btn:hover svg,
    .search-submit-btn:hover .submit-button-text {
        color: var(--color-brand-hover-text, #000000) !important;
    }
</style>

<script>
    (function() {
        const form = document.getElementById('searchForm');
        if (!form || form.dataset.initialized) return;
        form.dataset.initialized = 'true';

        const searchBox = document.getElementById('searchBox');
        const searchInput = document.getElementById('searchInput');
        const searchButton = document.getElementById('searchButton');
        const clearButton = document.getElementById('clearButton');
        const scanningLine = document.getElementById('scanningLine');

        // Suggestions Elements
        const dropdown = document.getElementById('searchSuggestionsDropdown');
        const tabProductsBtn = document.getElementById('tabProductsBtn');
        const tabCategoriesBtn = document.getElementById('tabCategoriesBtn');
        const productsList = document.getElementById('suggestProductsList');
        const categoriesList = document.getElementById('suggestCategoriesList');
        const emptyState = document.getElementById('suggestEmptyState');
        const loadingSpinner = document.getElementById('suggestLoadingSpinner');
        const productsBadge = document.getElementById('productsCountBadge');
        const categoriesBadge = document.getElementById('categoriesCountBadge');
        const seeAllLink = document.getElementById('seeAllResultsLink');

        let suggestDebounceTimer = null;
        let currentAbortController = null;
        let activeTab = 'products';
        let cachedData = null;

        // Dynamic typing placeholders when input is untouched
        const placeholders = [
            'Search electronics...',
            'Search laptops & MacBooks...',
            'Search smartphones & iPhones...',
            'Search RTX GPUs & processors...',
            'Search monitors & keyboards...'
        ];
        let placeholderIdx = 0;
        let charIdx = 0;
        let isDeleting = false;
        let typingTimeout = null;

        function typeEffect() {
            if (document.activeElement === searchInput || searchInput.value.trim().length > 0) {
                return;
            }

            const currentText = placeholders[placeholderIdx];
            if (isDeleting) {
                charIdx--;
                searchInput.setAttribute('placeholder', currentText.substring(0, charIdx));
                if (charIdx <= 0) {
                    isDeleting = false;
                    placeholderIdx = (placeholderIdx + 1) % placeholders.length;
                    typingTimeout = setTimeout(typeEffect, 400);
                    return;
                }
                typingTimeout = setTimeout(typeEffect, 30);
            } else {
                charIdx++;
                searchInput.setAttribute('placeholder', currentText.substring(0, charIdx));
                if (charIdx === currentText.length) {
                    isDeleting = true;
                    typingTimeout = setTimeout(typeEffect, 2200);
                    return;
                }
                typingTimeout = setTimeout(typeEffect, 55);
            }
        }

        // Start cycling placeholder only if input is empty
        if (!searchInput.value.trim()) {
            typingTimeout = setTimeout(typeEffect, 1500);
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Handle Focus & Blur
        searchInput.addEventListener('focus', function() {
            clearTimeout(typingTimeout);
            searchInput.setAttribute('placeholder', 'Search electronics...');
            if (searchInput.value.trim().length > 0 && cachedData) {
                dropdown.classList.remove('hidden');
            }
        });

        searchInput.addEventListener('blur', function() {
            if (!searchInput.value.trim()) {
                charIdx = 0;
                isDeleting = false;
                typingTimeout = setTimeout(typeEffect, 1200);
            }
        });

        function switchTab(tab) {
            activeTab = tab;
            if (tab === 'products') {
                tabProductsBtn.classList.add('active');
                tabCategoriesBtn.classList.remove('active');
                productsList.classList.remove('hidden');
                categoriesList.classList.add('hidden');
            } else {
                tabCategoriesBtn.classList.add('active');
                tabProductsBtn.classList.remove('active');
                categoriesList.classList.remove('hidden');
                productsList.classList.add('hidden');
            }
        }

        if (tabProductsBtn) {
            tabProductsBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            tabProductsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                switchTab('products');
            });
        }

        if (tabCategoriesBtn) {
            tabCategoriesBtn.addEventListener('mousedown', function(e) {
                e.preventDefault();
            });
            tabCategoriesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                switchTab('categories');
            });
        }

        function renderSuggestions(data, query) {
            cachedData = data;
            const products = data.products || [];
            const categories = data.categories || [];

            if (productsBadge) productsBadge.textContent = products.length ? `(${products.length})` : '';
            if (categoriesBadge) categoriesBadge.textContent = categories.length ? `(${categories.length})` : '';

            // Render Products List
            if (products.length > 0) {
                productsList.innerHTML = products.map(item => `
                    <a href="${item.url}" class="flex items-center gap-3 p-2.5 sm:p-3 hover:bg-gray-50 dark:hover:bg-slate-800/60 transition-colors group">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 p-1 rounded-lg bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700/60 shrink-0 flex items-center justify-center overflow-hidden">
                            <img src="${item.image}" alt="${escapeHtml(item.title)}" class="max-w-full max-h-full object-contain mix-blend-multiply dark:mix-blend-normal group-hover:scale-105 transition-transform" onerror="this.classList.add('opacity-40')">
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="suggest-prod-title line-clamp-2">
                                ${escapeHtml(item.title)}
                            </div>
                            <div class="flex items-baseline gap-2 mt-1">
                                ${item.in_stock ? `
                                    <span class="suggest-prod-price">
                                        ${item.price}
                                    </span>
                                    ${item.compare_price ? `
                                        <span class="suggest-prod-compare">
                                            ${item.compare_price}
                                        </span>
                                    ` : ''}
                                ` : `
                                    <span class="suggest-out-of-stock">
                                        Out Of Stock
                                    </span>
                                `}
                            </div>
                        </div>
                    </a>
                `).join('');
            } else {
                productsList.innerHTML = `
                    <div class="p-6 text-center text-xs text-gray-400">
                        No products found for "${escapeHtml(query)}"
                    </div>
                `;
            }

            // Render Categories List
            if (categories.length > 0) {
                categoriesList.innerHTML = categories.map(cat => `
                    <a href="${cat.url}" class="flex items-center justify-between p-2.5 sm:p-3 hover:bg-gray-50 dark:hover:bg-slate-800/60 transition-colors group">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-950/40 text-[#e03a14] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-[#e03a14] dark:group-hover:text-rose-400 transition-colors truncate">
                                    ${escapeHtml(cat.name)}
                                </div>
                                ${cat.breadcrumb && cat.breadcrumb !== cat.name ? `
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 truncate">
                                        ${escapeHtml(cat.breadcrumb)}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-medium shrink-0 bg-gray-100 dark:bg-slate-800 px-2 py-0.5 rounded-full">
                            ${cat.products_count} items
                        </span>
                    </a>
                `).join('');
            } else {
                categoriesList.innerHTML = `
                    <div class="p-6 text-center text-xs text-gray-400">
                        No categories found for "${escapeHtml(query)}"
                    </div>
                `;
            }

            // Empty state check
            if (products.length === 0 && categories.length === 0) {
                emptyState.classList.remove('hidden');
                productsList.classList.add('hidden');
                categoriesList.classList.add('hidden');
            } else {
                emptyState.classList.add('hidden');
                switchTab(activeTab);
            }

            // Update footer link
            if (seeAllLink) {
                seeAllLink.href = data.search_url || ("{{ route('search') }}?q=" + encodeURIComponent(query));
            }
            dropdown.classList.remove('hidden');
        }

        function fetchSuggestions(query) {
            if (currentAbortController) {
                currentAbortController.abort();
            }

            const cleanQuery = query.trim();
            if (!cleanQuery) {
                dropdown.classList.add('hidden');
                cachedData = null;
                return;
            }

            if (loadingSpinner) {
                loadingSpinner.classList.remove('hidden');
                loadingSpinner.classList.add('flex');
            }

            currentAbortController = new AbortController();
            const url = "{{ route('search.suggest') }}?q=" + encodeURIComponent(cleanQuery);

            fetch(url, {
                headers: { 'Accept': 'application/json' },
                signal: currentAbortController.signal
            })
            .then(res => res.json())
            .then(data => {
                if (loadingSpinner) {
                    loadingSpinner.classList.add('hidden');
                    loadingSpinner.classList.remove('flex');
                }
                renderSuggestions(data, cleanQuery);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    if (loadingSpinner) {
                        loadingSpinner.classList.add('hidden');
                        loadingSpinner.classList.remove('flex');
                    }
                }
            });
        }

        // Close on clicking outside
        document.addEventListener('click', function(e) {
            if (!form.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.add('hidden');
            }
        });

        // Typing indicator, clear button toggle, and live suggestions
        let typingTimer = null;
        searchInput.addEventListener('input', function() {
            const val = searchInput.value;
            const hasValue = val.length > 0;

            if (clearButton) {
                if (hasValue) {
                    clearButton.classList.remove('opacity-0', 'scale-75', 'pointer-events-none');
                    clearButton.classList.add('opacity-100', 'scale-100');
                } else {
                    clearButton.classList.add('opacity-0', 'scale-75', 'pointer-events-none');
                    clearButton.classList.remove('opacity-100', 'scale-100');
                }
            }

            searchBox.classList.add('is-typing');
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                searchBox.classList.remove('is-typing');
            }, 600);

            // Debounced suggestion fetch
            clearTimeout(suggestDebounceTimer);
            if (val.trim().length > 0) {
                suggestDebounceTimer = setTimeout(function() {
                    fetchSuggestions(val);
                }, 180);
            } else {
                dropdown.classList.add('hidden');
                cachedData = null;
            }
        });

        // Clear button click
        if (clearButton) {
            clearButton.addEventListener('click', function(e) {
                e.preventDefault();
                searchInput.value = '';
                searchInput.focus();
                clearButton.classList.add('opacity-0', 'scale-75', 'pointer-events-none');
                clearButton.classList.remove('opacity-100', 'scale-100');
                searchBox.classList.remove('is-typing');
                dropdown.classList.add('hidden');
                cachedData = null;
            });
        }

        // Advanced button click: navigate to advanced search (passing query if entered)
        if (searchButton) {
            searchButton.addEventListener('click', function(e) {
                const query = searchInput.value.trim();
                if (query) {
                    e.preventDefault();
                    window.location.href = "{{ route('search.advanced') }}?q=" + encodeURIComponent(query);
                }
            });
        }

        // Form Submit Animation Handler
        form.addEventListener('submit', function(e) {
            if (searchBox.classList.contains('is-searching')) {
                return;
            }

            const query = searchInput.value.trim();
            if (!query) {
                searchInput.focus();
                e.preventDefault();
                return;
            }

            dropdown.classList.add('hidden');
            e.preventDefault();
            searchBox.classList.add('is-searching');

            // Success micro-feedback and scan before submitting
            setTimeout(function() {
                searchBox.classList.add('is-success');
            }, 260);

            setTimeout(function() {
                form.submit();
            }, 420);
        });
    })();
</script>
