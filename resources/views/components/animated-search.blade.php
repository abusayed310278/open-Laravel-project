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
        class="search-box relative flex items-center w-full h-10 sm:h-11 bg-[#f8fafc] dark:bg-slate-900/90 hover:bg-gray-100/90 dark:hover:bg-slate-800/90 focus-within:bg-white dark:focus-within:bg-slate-900 border border-gray-200 dark:border-slate-700 hover:border-gray-300 dark:hover:border-slate-600 focus-within:border-gray-900 dark:focus-within:border-brand-500 rounded-lg shadow-2xs transition-all duration-200 pl-3 sm:pl-4 pr-1.5 sm:pr-2 overflow-hidden group"
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

        {{-- Right: Advanced Search Button --}}
        <a
            href="{{ route('search.advanced') }}"
            id="searchButton"
            class="search-button relative inline-flex items-center justify-center px-3 sm:px-4 py-1.5 h-7 sm:h-8 rounded-md bg-gray-900 dark:bg-brand-500 hover:bg-black dark:hover:bg-brand-600 text-white text-xs font-semibold shadow-xs transition-all duration-200 cursor-pointer overflow-hidden flex-shrink-0"
            title="Advanced Search"
        >
            <span id="buttonText" class="button-text transition-opacity duration-150">Advanced</span>
            <div id="spinner" class="spinner absolute inset-0 m-auto w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full opacity-0 pointer-events-none transition-opacity duration-150"></div>
        </a>

        {{-- Bottom: Animated Laser Scanning Line --}}
        <div id="scanningLine" class="scanning-line"></div>
    </div>
</form>

<style>
    /* Search Box Focus Glow */
    .search-box:focus-within {
        box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.12), 0 2px 8px -1px rgba(0, 0, 0, 0.06);
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
        background: linear-gradient(90deg, transparent, #eab308 30%, #3b82f6 70%, transparent);
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

        // Handle Focus & Blur
        searchInput.addEventListener('focus', function() {
            clearTimeout(typingTimeout);
            searchInput.setAttribute('placeholder', 'Search electronics...');
        });

        searchInput.addEventListener('blur', function() {
            if (!searchInput.value.trim()) {
                charIdx = 0;
                isDeleting = false;
                typingTimeout = setTimeout(typeEffect, 1200);
            }
        });

        // Typing indicator and clear button toggle
        let typingTimer = null;
        searchInput.addEventListener('input', function() {
            const hasValue = searchInput.value.length > 0;

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
