@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
        <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-1 text-xs font-semibold">
            <span id="pages-count-badge" class="px-3.5 py-1.5 rounded-lg bg-white text-gray-900 shadow-xs">Static Pages ({{ $pages->total() }})</span>
            <a href="{{ route('admin.blog.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition inline-flex items-center gap-1.5">
                <span>Blog Posts</span>
                <span class="px-1.5 py-0.5 rounded-full bg-brand-50 text-brand-700 text-[10px] font-bold">{{ \App\Models\Post::count() }}</span>
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition">
                Blog Categories
            </a>
        </div>
        <x-button as="a" :href="route('admin.pages.create')">New Page</x-button>
    </div>

    {{-- Live Search & Filter Toolbar --}}
    <div class="mb-5 bg-white p-3.5 sm:p-4 rounded-2xl border border-gray-100 shadow-xs">
        <form method="GET" action="{{ route('admin.pages.index') }}" id="pages-search-form" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            {{-- Live Search Input --}}
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="search"
                    id="pages-search-input"
                    value="{{ request('search') }}"
                    placeholder="Search pages by title, slug, or content (live as you type)..."
                    class="w-full bg-gray-50/70 border border-gray-200 rounded-xl pl-9 pr-10 py-2.5 text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-2xs"
                    autocomplete="off"
                />

                {{-- Loading Spinner --}}
                <div id="pages-search-spinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="animate-spin h-4 w-4 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                {{-- Clear Button --}}
                <button
                    type="button"
                    id="pages-search-clear-btn"
                    onclick="clearPagesSearch()"
                    class="{{ request()->filled('search') ? '' : 'hidden' }} absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5 cursor-pointer"
                    title="Clear search"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Status Filter --}}
            <select
                name="status"
                id="pages-status-select"
                class="bg-gray-50/70 border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 shadow-2xs shrink-0 cursor-pointer"
            >
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            {{-- Reset Button --}}
            <button
                type="button"
                id="pages-reset-btn"
                onclick="resetPagesFilters()"
                class="{{ (request()->filled('search') || request()->filled('status')) ? '' : 'hidden' }} px-3 py-2 text-xs font-semibold text-gray-500 hover:text-gray-800 transition shrink-0 inline-flex items-center gap-1 cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                <span>Reset</span>
            </button>
        </form>
    </div>

    {{-- Results Container (Refreshed Live on Search) --}}
    <div id="pages-results-wrapper">
        {{-- Active Filters Summary --}}
        @if (request()->filled('search') || request()->filled('status'))
            <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                <span class="font-semibold text-gray-400">Active Filters:</span>
                @if (request()->filled('search'))
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 text-xs font-medium border border-brand-200/60">
                        Query: <strong class="font-bold">"{{ request('search') }}"</strong>
                        <button type="button" onclick="clearPagesSearch()" class="hover:text-brand-900 font-bold ml-0.5 cursor-pointer">&times;</button>
                    </span>
                @endif
                @if (request()->filled('status'))
                    @php
                        $activeStatus = collect($statuses)->firstWhere('value', request('status'));
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200">
                        Status: <strong class="font-bold">{{ $activeStatus?->label() ?? request('status') }}</strong>
                        <button type="button" onclick="clearPagesStatus()" class="hover:text-gray-900 font-bold ml-0.5 cursor-pointer">&times;</button>
                    </span>
                @endif
                <span class="text-gray-400 font-normal">({{ $pages->total() }} results found)</span>
            </div>
        @endif

        <x-card>
            <x-table :headers="['Title', 'Slug', 'Status', 'Updated', 'Actions']" id="pages-table">
                @forelse ($pages as $page)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors {{ $page->slug === 'blog' ? 'bg-brand-50/20' : '' }}">
                        <td class="px-4 py-3 text-gray-800 font-medium">
                            <div class="flex items-center gap-2">
                                <span>{{ $page->title }}</span>
                                @if ($page->slug === 'blog')
                                    <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-brand-100 text-brand-800 rounded-md">Blog Section</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $page->slug === 'blog' ? '/blog' : '/pages/' . $page->slug }}</td>
                        <td class="px-4 py-3" id="page-status-badge-{{ $page->id }}">
                            <x-badge :color="$page->status->badgeColor()">{{ $page->status->label() }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $page->updated_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center justify-end gap-1.5">
                                {{-- Active/Inactive Toggle Switch --}}
                                <form method="POST" action="{{ route('admin.pages.toggle-status', $page) }}" class="inline-flex items-center m-0 js-toggle-page-form" data-page-id="{{ $page->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 {{ $page->status === \App\Enums\ContentStatus::Published ? 'bg-emerald-500' : 'bg-gray-300' }} js-toggle-btn"
                                        role="switch"
                                        aria-checked="{{ $page->status === \App\Enums\ContentStatus::Published ? 'true' : 'false' }}"
                                        title="{{ $page->status === \App\Enums\ContentStatus::Published ? 'Active / Published (Click to Deactivate)' : 'Inactive / Draft (Click to Activate)' }}"
                                    >
                                        <span class="sr-only">Toggle page status</span>
                                        <span
                                            aria-hidden="true"
                                            class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $page->status === \App\Enums\ContentStatus::Published ? 'translate-x-4' : 'translate-x-0' }} js-toggle-knob"
                                        ></span>
                                    </button>
                                </form>

                                <a href="{{ $page->slug === 'blog' ? route('blog.index') : route('pages.show', $page->slug) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition inline-flex items-center" title="View Live Page">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('admin.pages.edit', $page) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition inline-flex items-center" title="Edit Page">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline-block m-0" onsubmit="return confirm('Delete this page permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer inline-flex items-center" title="Delete Page">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <p class="font-semibold text-gray-700 text-sm">No pages found</p>
                                @if (request()->filled('search') || request()->filled('status'))
                                    <p class="text-xs text-gray-400 max-w-sm">No static pages match your search criteria. Try modifying your search keywords or resetting filters.</p>
                                    <button type="button" onclick="resetPagesFilters()" class="mt-2 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition cursor-pointer">Clear Filters</button>
                                @else
                                    <p class="text-xs text-gray-400">Get started by creating your first static CMS page.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div id="pages-pagination-wrapper">
                <x-pagination :paginator="$pages" />
            </div>
        </x-card>
    </div>

    {{-- Live Search & AJAX Script --}}
    <script>
        let liveSearchTimeout = null;
        let activeAbortController = null;

        function runLiveSearch(debounceMs = 250) {
            clearTimeout(liveSearchTimeout);

            const searchInput = document.getElementById('pages-search-input');
            const statusSelect = document.getElementById('pages-status-select');
            const spinner = document.getElementById('pages-search-spinner');
            const clearBtn = document.getElementById('pages-search-clear-btn');
            const resetBtn = document.getElementById('pages-reset-btn');
            const resultsWrapper = document.getElementById('pages-results-wrapper');

            const query = (searchInput ? searchInput.value : '').trim();
            const status = statusSelect ? statusSelect.value : '';

            // Toggle clear & reset buttons visibility immediately
            if (clearBtn) clearBtn.classList.toggle('hidden', query.length === 0);
            if (resetBtn) resetBtn.classList.toggle('hidden', query.length === 0 && !status);

            liveSearchTimeout = setTimeout(() => {
                if (spinner) spinner.classList.remove('hidden');
                if (clearBtn && query.length > 0) clearBtn.classList.add('hidden');

                if (activeAbortController) {
                    activeAbortController.abort();
                }
                activeAbortController = new AbortController();

                const params = new URLSearchParams();
                if (query) params.set('search', query);
                if (status) params.set('status', status);

                const baseUrl = '{{ route('admin.pages.index') }}';
                const fetchUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

                fetch(fetchUrl, {
                    signal: activeAbortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // 1. Swap the results wrapper
                    const newResults = doc.getElementById('pages-results-wrapper');
                    if (newResults && resultsWrapper) {
                        resultsWrapper.innerHTML = newResults.innerHTML;
                    }

                    // 2. Update the static pages count badge in top header
                    const newCountBadge = doc.getElementById('pages-count-badge');
                    const currentCountBadge = document.getElementById('pages-count-badge');
                    if (newCountBadge && currentCountBadge) {
                        currentCountBadge.innerHTML = newCountBadge.innerHTML;
                    }

                    // 3. Re-attach AJAX toggle handlers on new rows
                    initPageToggleForms();

                    // 4. Update the browser URL without reload
                    window.history.replaceState({}, '', fetchUrl);
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Pages live search error:', err);
                    }
                })
                .finally(() => {
                    if (spinner) spinner.classList.add('hidden');
                    if (clearBtn && searchInput && searchInput.value.trim().length > 0) {
                        clearBtn.classList.remove('hidden');
                    }
                });
            }, debounceMs);
        }

        function clearPagesSearch() {
            const searchInput = document.getElementById('pages-search-input');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                runLiveSearch(0);
            }
        }

        function clearPagesStatus() {
            const statusSelect = document.getElementById('pages-status-select');
            if (statusSelect) {
                statusSelect.value = '';
                runLiveSearch(0);
            }
        }

        function resetPagesFilters() {
            const searchInput = document.getElementById('pages-search-input');
            const statusSelect = document.getElementById('pages-status-select');
            if (searchInput) searchInput.value = '';
            if (statusSelect) statusSelect.value = '';
            runLiveSearch(0);
        }

        function initPageToggleForms() {
            document.querySelectorAll('.js-toggle-page-form').forEach(function(form) {
                if (form.hasAttribute('data-ajax-initialized')) return;
                form.setAttribute('data-ajax-initialized', 'true');

                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = form.querySelector('.js-toggle-btn');
                    const knob = form.querySelector('.js-toggle-knob');
                    const pageId = form.getAttribute('data-page-id');
                    const statusBadgeCell = document.getElementById('page-status-badge-' + pageId);

                    btn.disabled = true;
                    btn.classList.add('opacity-70');

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-70');
                        if (data && data.success) {
                            if (data.isPublished) {
                                btn.classList.remove('bg-gray-300');
                                btn.classList.add('bg-emerald-500');
                                knob.classList.remove('translate-x-0');
                                knob.classList.add('translate-x-4');
                                btn.setAttribute('aria-checked', 'true');
                                btn.setAttribute('title', 'Active / Published (Click to Deactivate)');
                                if (statusBadgeCell) {
                                    statusBadgeCell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">' + data.label + '</span>';
                                }
                            } else {
                                btn.classList.remove('bg-emerald-500');
                                btn.classList.add('bg-gray-300');
                                knob.classList.remove('translate-x-4');
                                knob.classList.add('translate-x-0');
                                btn.setAttribute('aria-checked', 'false');
                                btn.setAttribute('title', 'Inactive / Draft (Click to Activate)');
                                if (statusBadgeCell) {
                                    statusBadgeCell.innerHTML = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">' + data.label + '</span>';
                                }
                            }
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-70');
                        form.submit();
                    });
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('pages-search-input');
            const statusSelect = document.getElementById('pages-status-select');
            const form = document.getElementById('pages-search-form');
            const resultsWrapper = document.getElementById('pages-results-wrapper');

            // Automatic live search as user types
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    runLiveSearch(200);
                });
            }

            // Automatic search when status changes
            if (statusSelect) {
                statusSelect.addEventListener('change', function() {
                    runLiveSearch(0);
                });
            }

            // Prevent form submit from reloading page; run immediate search
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    runLiveSearch(0);
                });
            }

            // Intercept pagination clicks inside results wrapper for seamless AJAX paging
            if (resultsWrapper) {
                resultsWrapper.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.href && link.closest('#pages-pagination-wrapper')) {
                        e.preventDefault();
                        const targetUrl = new URL(link.href);
                        const pageVal = targetUrl.searchParams.get('page');

                        const params = new URLSearchParams(window.location.search);
                        if (pageVal) params.set('page', pageVal);

                        const baseUrl = '{{ route('admin.pages.index') }}';
                        const fetchUrl = baseUrl + (params.toString() ? '?' + params.toString() : '');

                        const spinner = document.getElementById('pages-search-spinner');
                        if (spinner) spinner.classList.remove('hidden');

                        fetch(fetchUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const newResults = doc.getElementById('pages-results-wrapper');
                            if (newResults && resultsWrapper) {
                                resultsWrapper.innerHTML = newResults.innerHTML;
                            }
                            initPageToggleForms();
                            window.history.replaceState({}, '', fetchUrl);
                            window.scrollTo({ top: resultsWrapper.offsetTop - 80, behavior: 'smooth' });
                        })
                        .finally(() => {
                            if (spinner) spinner.classList.add('hidden');
                        });
                    }
                });
            }

            // Initialize toggle forms on initial load
            initPageToggleForms();
        });
    </script>
@endsection
