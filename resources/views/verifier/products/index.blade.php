@extends('layouts.verifier')

@section('title', 'Seller Product Inventory')

@section('content')
    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Seller Product Inventory</h1>
            <p class="text-xs sm:text-sm text-gray-500">Browse all seller marketplace listings (active and inactive), perform verification, and assign certified grades.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-800 text-xs font-semibold">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Verified listings are locked from seller edits</span>
            </span>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
        <a href="{{ route('verifier.products.index') }}" class="block group">
            <x-stat-card
                label="Total Listings"
                :value="$totalCount"
                hint="All seller products"
                icon='<svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
            />
        </a>

        <a href="{{ route('verifier.products.index', ['status' => 'active']) }}" class="block group">
            <x-stat-card
                label="Active Products"
                :value="$activeCount"
                hint="Live & Published"
                icon='<svg class="w-5 h-5 group-hover:scale-110 transition-transform text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
        </a>

        <a href="{{ route('verifier.products.index', ['status' => 'inactive']) }}" class="block group">
            <x-stat-card
                label="Inactive / Draft"
                :value="$inactiveCount"
                hint="Drafts & Offline"
                icon='<svg class="w-5 h-5 group-hover:scale-110 transition-transform text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>'
            />
        </a>

        <a href="{{ route('verifier.products.index', ['verification_status' => 'pending_queue']) }}" class="block group">
            <x-stat-card
                label="Pending Queue"
                :value="$pendingCount"
                hint="Awaiting check"
                icon='<svg class="w-5 h-5 group-hover:scale-110 transition-transform text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            />
        </a>

        <a href="{{ route('verifier.products.index', ['verification_status' => 'verified']) }}" class="block group">
            <x-stat-card
                label="Verified & Graded"
                :value="$verifiedCount"
                hint="Certified & Locked"
                icon='<svg class="w-5 h-5 group-hover:scale-110 transition-transform text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
            />
        </a>
    </div>

    {{-- Filter Toolbar --}}
    <x-card>
        <form method="GET" action="{{ route('verifier.products.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search product title, SKU, or seller..."
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="w-36">
                <select name="category_id" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$selectedCategory === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Product Active / Inactive Status Filter (Like Admin) --}}
            <div class="w-40">
                <select name="status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Product States</option>
                    <option value="active" {{ $selectedProductStatus === 'active' ? 'selected' : '' }}>Active / Live</option>
                    <option value="inactive" {{ $selectedProductStatus === 'inactive' ? 'selected' : '' }}>Inactive / Offline</option>
                    <option value="draft" {{ $selectedProductStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="pending_approval" {{ $selectedProductStatus === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="approved" {{ $selectedProductStatus === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="suspended" {{ $selectedProductStatus === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            {{-- Verification Status Filter --}}
            <div class="w-44">
                <select name="verification_status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Verification States</option>
                    <option value="pending_queue" {{ in_array($selectedStatus, ['pending', 'pending_queue'], true) ? 'selected' : '' }}>Pending Queue</option>
                    <option value="verified" {{ $selectedStatus === 'verified' ? 'selected' : '' }}>Verified (Locked)</option>
                    <option value="scheduled" {{ $selectedStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="inspecting" {{ $selectedStatus === 'inspecting' ? 'selected' : '' }}>Inspecting</option>
                    <option value="not_requested" {{ $selectedStatus === 'not_requested' ? 'selected' : '' }}>Not Requested</option>
                    <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="w-36">
                <select name="condition" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Conditions</option>
                    <option value="new" {{ $selectedCondition === 'new' ? 'selected' : '' }}>Brand New</option>
                    <option value="used" {{ $selectedCondition === 'used' ? 'selected' : '' }}>Used</option>
                    <option value="refurbished" {{ $selectedCondition === 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                    <option value="openbox" {{ $selectedCondition === 'openbox' ? 'selected' : '' }}>Open Box</option>
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                Filter
            </button>

            @if ($search || $selectedCategory || $selectedProductStatus || $selectedStatus || $selectedCondition)
                <a href="{{ route('verifier.products.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-card>
        <x-table :headers="['Product Details', 'Seller', 'Price', 'Status', 'Verification Status', 'Action']" id="verifier-products-table">
            @forelse ($products as $product)
                @php
                    $imgUrl = $product->primaryImageUrl();
                    $isVerified = $product->isVerified();
                    $isActive = ($product->status?->value === 'published' || $product->approval_status?->value === 'approved');
                @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    {{-- Product Details --}}
                    <td class="px-4 py-3.5 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center text-gray-400 border border-gray-100">
                                @if ($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                    <div class="hidden w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                        </svg>
                                    </div>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('verifier.products.show', $product) }}" class="text-sm font-semibold text-gray-900 hover:text-amber-600 transition-colors block truncate max-w-xs" title="{{ $product->title }}">
                                    {{ $product->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                    @if ($product->sku)
                                        · <span class="font-mono text-gray-500">{{ $product->sku }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </td>

                    {{-- Seller Info --}}
                    <td class="px-4 py-3.5 text-xs text-gray-600">
                        <p class="font-semibold text-gray-900">{{ $product->user->name ?? 'Unknown Seller' }}</p>
                        <p class="text-gray-400">{{ $product->user->email ?? '' }}</p>
                        <span class="inline-block mt-0.5 text-[10px] text-gray-500 font-medium bg-gray-100 px-1.5 py-0.5 rounded">
                            {{ $product->user?->role?->label() ?? 'Seller' }}
                        </span>
                    </td>

                    {{-- Price --}}
                    <td class="px-4 py-3.5 text-xs font-semibold text-gray-900 whitespace-nowrap">
                        ${{ number_format($product->price, 2) }}
                    </td>

                    {{-- Product Active / Inactive Status --}}
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <form method="POST" action="{{ route('verifier.products.toggle-status', $product) }}" class="inline-block">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="cursor-pointer group" title="Click to toggle Active / Inactive state">
                                @if ($isActive)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60 group-hover:bg-red-50 group-hover:text-red-700 group-hover:border-red-200 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 group-hover:bg-red-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200 group-hover:bg-emerald-50 group-hover:text-emerald-700 group-hover:border-emerald-200 transition">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 group-hover:bg-emerald-500"></span>
                                        {{ $product->status?->label() ?? 'Inactive' }}
                                    </span>
                                @endif
                            </button>
                        </form>
                    </td>

                    {{-- Verification Status --}}
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <div class="flex flex-col items-start gap-1">
                            <x-badge :color="$product->verification_status->badgeColor()">
                                {{ $product->verification_status->label() }}
                            </x-badge>
                            @if ($isVerified)
                                @php
                                    $gradeEnum = $product->grade instanceof \App\Enums\ProductGrade 
                                        ? $product->grade 
                                        : \App\Enums\ProductGrade::tryFrom((string)$product->grade);
                                    $gradeVal = $gradeEnum?->value ?? ($product->grade ?? 'A');
                                    $gradeStyle = $gradeEnum?->badgeClass() ?? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60';
                                @endphp
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $gradeStyle }}">
                                        Grade {{ $gradeVal }}
                                    </span>
                                    <span class="inline-flex items-center gap-0.5 text-[10px] text-emerald-700 font-medium" title="Locked from seller editing">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        Locked
                                    </span>
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Action --}}
                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1.5">
                            {{-- Message Seller --}}
                            @if ($product->user)
                                <form method="POST" action="{{ route('chat.start', $product) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="p-1.5 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Message {{ $product->user->name }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                </form>
                            @endif

                            {{-- Live Website Preview Icon --}}
                            <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg border border-gray-200 transition" title="Preview Live Website Listing">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            {{-- Active / Inactive Status Toggle Icon (Like Admin) --}}
                            @if ($product->publication_status?->value === 'published' || $product->status?->value === 'published')
                                <form method="POST" action="{{ route('verifier.products.toggle-status', $product) }}" class="inline-block m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Active / Published (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('verifier.products.toggle-status', $product) }}" class="inline-block m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg border border-gray-200 transition cursor-pointer" title="Inactive / Unpublished (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            {{-- Edit Product (Admin Privileges) --}}
                            <a href="{{ route('verifier.products.edit', [$product, 'redirect_to' => request()->fullUrl()]) }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg border border-gray-200 transition" title="Edit Product Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>

                            {{-- Approve (If Pending Approval) --}}
                            @if ($product->approval_status?->value === 'pending_approval')
                                <form method="POST" action="{{ route('verifier.products.approve', $product) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer" title="Approve Product">
                                        Approve
                                    </button>
                                </form>
                            @endif

                            {{-- Verification Action --}}
                            @if ($isVerified)
                                <button type="button" data-modal-open="verify-modal-{{ $product->id }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition cursor-pointer" title="Re-grade or inspect">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    <span>Re-Grade</span>
                                </button>
                            @else
                                <button type="button" data-modal-open="verify-modal-{{ $product->id }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs font-semibold shadow-2xs transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>Verify & Grade</span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Modal: Verify Product --}}
                <x-modal id="verify-modal-{{ $product->id }}" title="Verify Listing: {{ $product->title }}" maxWidth="max-w-xl">
                    <form method="POST" action="{{ route('verifier.products.verify', $product) }}" class="space-y-4">
                        @csrf
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            @if ($imgUrl)
                                <img src="{{ $imgUrl }}" alt="{{ $product->title }}" class="w-12 h-12 object-cover rounded-lg border border-gray-200">
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $product->title }}</p>
                                <p class="text-[11px] text-gray-500">Seller: <strong class="text-gray-800">{{ $product->user->name ?? 'Unknown' }}</strong> · {{ $product->category?->name ?? 'Uncategorized' }}</p>
                                <p class="text-[11px] text-amber-700 font-medium">⚠️ Once certified, this product is locked from seller modification.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-800 mb-2">Certification Decision</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="border border-gray-200 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:border-emerald-500 transition-colors">
                                    <input type="radio" name="decision" value="pass" checked class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Pass / Certified</p>
                                        <p class="text-[10px] text-gray-500">Meets condition standard</p>
                                    </div>
                                </label>
                                <label class="border border-gray-200 rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:border-red-500 transition-colors">
                                    <input type="radio" name="decision" value="fail" class="text-red-600 focus:ring-red-500">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Fail / Reject</p>
                                        <p class="text-[10px] text-gray-500">Failed physical check</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-800 mb-1">Assigned Grade</label>
                            <select name="grade" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800">
                                <option value="A">Grade A — Like New / Excellent</option>
                                <option value="B">Grade B — Very Good / Minor Scratches</option>
                                <option value="C">Grade C — Acceptable / Visible Wear</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-800 mb-1">Battery Health (%) (Optional)</label>
                            <input type="number" name="battery_health" min="0" max="100" placeholder="e.g. 92" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-800 mb-1">Verification Inspection Notes</label>
                            <textarea name="notes" rows="3" placeholder="Enter physical check observations..." class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                            <button type="button" data-modal-close="verify-modal-{{ $product->id }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-xl">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-xl shadow-xs">Submit Certification</button>
                        </div>
                    </form>
                </x-modal>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                        No seller products found matching your search and filter criteria.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </x-card>
@endsection
