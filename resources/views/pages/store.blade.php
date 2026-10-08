@extends('layouts.app')

@section('title', $storeName)

@section('content')
@php
    $adminSellerBanner = setting('seller_banner');
    $resolvedCover = (trim((string) $coverImage) !== '' ? \App\Support\MediaUrl::resolve($coverImage) : null)
        ?: (trim((string) $adminSellerBanner) !== '' ? \App\Support\MediaUrl::resolve($adminSellerBanner) : null)
        ?: asset('images/default-cover.svg');
    $resolvedLogo = \App\Support\MediaUrl::resolve($logo);
@endphp

    <div class="h-44 sm:h-64 relative overflow-hidden bg-gray-900">
        <img src="{{ $resolvedCover }}" alt="{{ $storeName }} Cover" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pb-16 sm:pb-24">
        <div class="flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6 -mt-12 sm:-mt-16 pb-6 relative z-10">
            <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl bg-white p-1.5 shadow-xl ring-1 ring-black/10 flex-shrink-0 relative overflow-hidden">
                @if ($resolvedLogo)
                    <img src="{{ $resolvedLogo }}" 
                         alt="{{ $storeName }}" 
                         class="w-full h-full object-cover rounded-xl"
                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                    >
                    <div class="hidden w-full h-full rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-black shadow-inner">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                @else
                    <div class="w-full h-full rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-black shadow-inner">
                        {{ strtoupper(substr($storeName, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="flex-1 pb-1 sm:translate-y-3">
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl sm:text-3xl font-bold text-brand-600 sm:[text-shadow:0_0_6px_rgb(255_255_255/0.9)]">{{ $storeName }}</h1>
                    @if ($isVerified)
                        <x-verified-badge />
                    @endif
                </div>
                @if ($location)
                    <p class="text-sm text-gray-500 mt-3 mb-1.5 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $location }}
                    </p>
                @endif

                {{-- eBay-style quick trust feedback metrics (Click to toggle Seller Feedback) --}}
                <button type="button" id="toggle-feedback-btn" class="flex items-center gap-2 mt-2 flex-wrap text-xs group cursor-pointer text-left bg-gray-50/70 hover:bg-gray-100/80 px-2.5 py-1.5 rounded-xl border border-gray-200/60 transition-colors" title="Click to view seller feedback ratings">
                    <div class="flex items-center gap-1">
                        <x-star-rating :rating="round($feedback['averageRating'])" />
                        <span class="font-bold text-gray-900">{{ number_format($feedback['averageRating'], 1) }}</span>
                    </div>
                    <span class="text-gray-300">·</span>
                    <span class="inline-flex items-center font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                        {{ $feedback['positivePercent'] }}% Positive feedback
                    </span>
                    <span class="text-gray-300">·</span>
                    <span class="text-brand-600 group-hover:text-brand-700 font-semibold group-hover:underline inline-flex items-center gap-1">
                        <span>{{ $feedback['totalCount'] }} {{ Str::plural('rating', $feedback['totalCount']) }}</span>
                        <svg id="feedback-toggle-chevron" class="w-3.5 h-3.5 text-brand-500 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </span>
                </button>
            </div>

            @auth
                @if (auth()->id() === $profile->user_id)
                    <span class="inline-flex items-center px-4 py-2 border border-gray-200 text-sm font-medium rounded-lg text-gray-400 bg-gray-50 cursor-not-allowed">
                        Your Store
                    </span>
                @elseif (auth()->user()->isCustomer())
                    <form method="POST" action="{{ route('chat.start-seller', $profile->user) }}">
                        @csrf
                        <x-button type="submit" variant="secondary" class="shadow-2xs hover:shadow-xs transition-all">
                            <svg class="w-4 h-4 mr-1.5 -ml-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            Message Seller
                        </x-button>
                    </form>
                @else
                    <button type="button" data-modal-open="role-restriction-modal" class="inline-flex items-center justify-center font-medium rounded-md transition-colors border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 px-4 py-2 text-sm shadow-2xs cursor-pointer">
                        <svg class="w-4 h-4 mr-1.5 -ml-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Message Seller
                    </button>
                @endif
            @else
                <a href="{{ route('chat.start-seller', $profile->user) }}" class="inline-flex items-center justify-center font-medium rounded-md transition-colors border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 px-4 py-2 text-sm shadow-2xs">
                    <svg class="w-4 h-4 mr-1.5 -ml-0.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Message Seller
                </a>
            @endauth
        </div>

        @if ($bio)
            <p class="text-gray-500 leading-relaxed max-w-2xl pb-6">{{ $bio }}</p>
        @endif

        {{-- eBay-Style Seller Feedback Section (Initially hidden, revealed on rating click) --}}
        <div id="seller-feedback" class="hidden mt-4 pt-8 border-t border-gray-200 scroll-mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Seller Feedback</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                            {{ $feedback['totalCount'] }}
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 mt-1">
                        Verified ratings and feedback from buyers who purchased from {{ $storeName }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="hidden sm:inline">100% Verified Buyer Transactions</span>
                    </div>
                    <button type="button" id="close-feedback-btn" class="text-xs font-semibold text-gray-500 hover:text-gray-800 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors inline-flex items-center gap-1 cursor-pointer" title="Hide Seller Feedback">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        <span>Hide</span>
                    </button>
                </div>
            </div>

            {{-- eBay Feedback Overview Card --}}
            <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-8 shadow-xs mb-8">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                    {{-- 1. Positive Feedback Big Metric --}}
                    <div class="md:col-span-4 flex flex-col justify-between pr-0 md:pr-4">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Seller Rating</span>
                            <div class="flex items-baseline gap-2 mt-2">
                                <span class="text-4xl sm:text-5xl font-black text-gray-900 tracking-tight">{{ $feedback['positivePercent'] }}%</span>
                                <span class="text-sm font-semibold text-emerald-600">Positive</span>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <x-star-rating :rating="round($feedback['averageRating'])" />
                                <span class="text-sm font-bold text-gray-900">{{ number_format($feedback['averageRating'], 1) }}</span>
                                <span class="text-xs text-gray-400">/ 5.0</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Based on {{ $feedback['totalCount'] }} verified buyer {{ Str::plural('rating', $feedback['totalCount']) }}
                            </p>
                        </div>
                        <div class="mt-6 pt-4 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ $feedback['positiveCount'] }} positive in past 12 months</span>
                        </div>
                    </div>

                    {{-- 2. Star Distribution Bars --}}
                    <div class="md:col-span-4 pt-6 md:pt-0 px-0 md:px-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 block">Rating Breakdown</span>
                        <div class="space-y-2">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                @php
                                    $starCount = $feedback['starCounts'][$star] ?? 0;
                                    $starPct = $feedback['totalCount'] > 0 ? round(($starCount / $feedback['totalCount']) * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="w-14 text-gray-600 font-medium shrink-0">{{ $star }} {{ Str::plural('star', $star) }}</span>
                                    <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300 {{ $star >= 4 ? 'bg-emerald-500' : ($star === 3 ? 'bg-amber-400' : 'bg-rose-400') }}" style="width: {{ $starPct }}%;"></div>
                                    </div>
                                    <span class="w-8 text-right text-gray-400 font-medium shrink-0">{{ $starCount }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 3. Detailed Seller Ratings (DSRs) --}}
                    <div class="md:col-span-4 pt-6 md:pt-0 pl-0 md:pl-6">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3 block">Detailed Seller Ratings</span>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Item as described
                                </span>
                                <span class="font-bold text-gray-900 flex items-center gap-1">
                                    {{ $feedback['dsr']['item_described'] }}
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" /></svg>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    Communication
                                </span>
                                <span class="font-bold text-gray-900 flex items-center gap-1">
                                    {{ $feedback['dsr']['communication'] }}
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" /></svg>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Shipping speed
                                </span>
                                <span class="font-bold text-gray-900 flex items-center gap-1">
                                    {{ $feedback['dsr']['shipping_speed'] }}
                                    <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" /></svg>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Shipping & handling
                                </span>
                                <span class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100">
                                    {{ $feedback['dsr']['shipping_cost'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Interactive Sentiment Filter Tabs --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0" id="feedback-filters">
                    <button type="button" data-filter="all" class="feedback-filter-btn active inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gray-900 text-white transition-colors cursor-pointer">
                        <span>All feedback</span>
                        <span class="opacity-80">({{ $feedback['totalCount'] }})</span>
                    </button>
                    <button type="button" data-filter="positive" class="feedback-filter-btn inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Positive</span>
                        <span class="text-gray-400">({{ $feedback['positiveCount'] }})</span>
                    </button>
                    <button type="button" data-filter="neutral" class="feedback-filter-btn inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                        <span>Neutral</span>
                        <span class="text-gray-400">({{ $feedback['neutralCount'] }})</span>
                    </button>
                    <button type="button" data-filter="negative" class="feedback-filter-btn inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer">
                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                        <span>Negative</span>
                        <span class="text-gray-400">({{ $feedback['negativeCount'] }})</span>
                    </button>
                </div>

                <div class="text-xs text-gray-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Most recent first</span>
                </div>
            </div>

            {{-- Feedback Reviews Feed --}}
            <div id="feedback-list" class="space-y-4">
                @if ($feedback['reviews']->isEmpty())
                    <div class="bg-gray-50 border border-gray-200/60 rounded-2xl p-10 text-center">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <h4 class="text-base font-bold text-gray-900">No seller feedback yet</h4>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1 max-w-md mx-auto">
                            As buyers complete orders and receive their deliveries from {{ $storeName }}, their feedback and ratings will appear here.
                        </p>
                    </div>
                @else
                    @foreach ($feedback['reviews'] as $rev)
                        @php
                            $sentiment = $rev->rating >= 4 ? 'positive' : ($rev->rating === 3 ? 'neutral' : 'negative');
                            $purchasedTitle = $rev->order?->items?->first()?->product_title;
                        @endphp
                        <div class="feedback-item bg-white border border-gray-200/70 rounded-2xl p-5 sm:p-6 shadow-2xs hover:border-gray-300 transition-colors" data-sentiment="{{ $sentiment }}">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5">
                                    {{-- Sentiment Badge eBay style --}}
                                    @if ($sentiment === 'positive')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a2 2 0 00-.8 1.4z" />
                                            </svg>
                                            Positive
                                        </span>
                                    @elseif ($sentiment === 'neutral')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                            <span class="w-2.5 h-0.5 bg-gray-600 rounded-full"></span>
                                            Neutral
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200/70">
                                            <svg class="w-3.5 h-3.5 text-rose-600 rotate-180" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a2 2 0 00-.8 1.4z" />
                                            </svg>
                                            Negative
                                        </span>
                                    @endif

                                    <x-star-rating :rating="$rev->rating" />
                                </div>

                                <div class="text-xs text-gray-400 flex items-center gap-2">
                                    <span>{{ $rev->created_at->format('M j, Y') }}</span>
                                    <span>·</span>
                                    <span>{{ $rev->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            {{-- Reviewer & Verified Tag --}}
                            <div class="flex items-center gap-2.5 mt-3.5">
                                <div class="w-7 h-7 rounded-full bg-brand-50 text-brand-700 border border-brand-200/50 flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr($rev->reviewer?->name ?? 'B', 0, 1)) }}
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-900">{{ $rev->reviewer?->name ?? 'Openbox Buyer' }}</span>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-700 bg-emerald-50/80 px-2 py-0.5 rounded border border-emerald-100">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Verified Purchase
                                    </span>
                                </div>
                            </div>

                            {{-- Feedback Content --}}
                            <div class="mt-3">
                                @if ($rev->title)
                                    <h4 class="text-sm font-bold text-gray-900 mb-1">{{ $rev->title }}</h4>
                                @endif
                                <p class="text-sm text-gray-700 leading-relaxed">&ldquo;{{ $rev->body }}&rdquo;</p>
                            </div>

                            @if ($purchasedTitle)
                                <div class="mt-3.5 pt-3 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                                    <span class="text-gray-400 font-medium">Item:</span>
                                    <span class="font-medium text-gray-700 truncate max-w-md">{{ $purchasedTitle }}</span>
                                </div>
                            @endif

                            {{-- Replies --}}
                            @if ($rev->replies->isNotEmpty())
                                <div class="mt-3.5 space-y-2">
                                    @foreach ($rev->replies as $rep)
                                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-3 text-xs text-gray-600">
                                            <div class="flex items-center gap-1.5 font-bold text-gray-900 mb-1">
                                                <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                                <span>Seller Response:</span>
                                                <span class="text-gray-400 font-normal">· {{ $rep->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="leading-relaxed">{{ $rep->body }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- Policy / Trust Notice --}}
            <div class="mt-8 sm:mt-10 rounded-xl bg-gray-50 border border-gray-200/60 p-4 flex items-center justify-between flex-col sm:flex-row gap-3 text-xs text-gray-500">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Only customers who purchased items from this store can submit feedback upon successful delivery.</span>
                </div>
                <span class="text-gray-400 text-[11px]">Protected by Openbox Buyer Trust</span>
            </div>
        </div>

        {{-- Products Section (Shown after Seller Feedback) --}}
        <div id="store-products" class="mt-12 pt-10 border-t border-gray-200">
            <div class="flex items-center justify-between pb-6 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Products</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                        {{ $products->total() }}
                    </span>
                </div>

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

            <div class="py-8">
                @if ($products->isEmpty())
                    <div class="bg-gray-50 border border-gray-100 rounded-md p-12 text-center text-gray-400 text-sm">
                        No products listed yet.
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
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

    @auth
        @if (!auth()->user()->isCustomer() && auth()->id() !== $profile->user_id)
            {{-- Centered Custom Role Restriction Modal --}}
            <div id="role-restriction-modal" data-modal class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left shadow-2xl transition-all border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                    {{-- Close Button (Top Right) --}}
                    <button type="button" data-modal-close class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer" aria-label="Close">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="flex items-start gap-4">
                        {{-- Icon --}}
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 min-w-0 pr-2">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">
                                Customer Account Required
                            </h3>
                            <p class="mt-1.5 text-xs sm:text-sm text-gray-600 leading-relaxed">
                                Messaging sellers is restricted to <strong class="text-gray-900 font-semibold">Customer</strong> accounts. You are currently logged in as <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">{{ auth()->user()->role->label() }}</span>.
                            </p>
                            <p class="mt-2 text-xs text-gray-500">
                                To inquire about products or negotiate with this seller, please sign in with a Customer account.
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" data-modal-close class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors border border-gray-200 cursor-pointer text-center">
                            Got it
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto inline-block">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-4 py-2.5 text-xs font-bold text-white bg-brand-500 hover:bg-brand-600 rounded-lg shadow-xs transition-colors cursor-pointer text-center">
                                Switch Account
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const modal = document.getElementById('role-restriction-modal');
                    if (!modal) return;

                    document.querySelectorAll('[data-modal-open="role-restriction-modal"]').forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            e.preventDefault();
                            modal.classList.remove('hidden');
                        });
                    });

                    const closeModal = () => modal.classList.add('hidden');

                    modal.querySelectorAll('[data-modal-close]').forEach(btn => {
                        btn.addEventListener('click', closeModal);
                    });

                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) closeModal();
                    });

                    document.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                            closeModal();
                        }
                    });
                });
            </script>
        @endif
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const feedbackSection = document.getElementById('seller-feedback');
            const toggleBtn = document.getElementById('toggle-feedback-btn');
            const chevron = document.getElementById('feedback-toggle-chevron');
            const closeBtn = document.getElementById('close-feedback-btn');

            const showFeedback = (shouldScroll = true) => {
                if (!feedbackSection) return;
                feedbackSection.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
                if (shouldScroll) {
                    feedbackSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };

            const hideFeedback = () => {
                if (!feedbackSection) return;
                feedbackSection.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            };

            const toggleFeedback = () => {
                if (!feedbackSection) return;
                if (feedbackSection.classList.contains('hidden')) {
                    showFeedback(true);
                } else {
                    hideFeedback();
                }
            };

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleFeedback);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', hideFeedback);
            }

            // If page is loaded with #seller-feedback anchor, auto-open
            if (window.location.hash === '#seller-feedback') {
                showFeedback(false);
            }

            // Support any link with href="#seller-feedback"
            document.querySelectorAll('a[href="#seller-feedback"]').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    showFeedback(true);
                });
            });

            // Sentiment filter tabs
            const filterBtns = document.querySelectorAll('.feedback-filter-btn');
            const feedbackItems = document.querySelectorAll('.feedback-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const filter = btn.getAttribute('data-filter');

                    filterBtns.forEach(b => {
                        b.classList.remove('bg-gray-900', 'text-white');
                        b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-200');
                    });

                    btn.classList.add('bg-gray-900', 'text-white');
                    btn.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-200');

                    feedbackItems.forEach(item => {
                        const sentiment = item.getAttribute('data-sentiment');
                        if (filter === 'all' || sentiment === filter) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
@endsection
