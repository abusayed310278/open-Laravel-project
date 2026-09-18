@extends('layouts.verifier')

@section('title', 'Seller Products & Verification')

@section('content')
    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Seller Product Inventory</h1>
            <p class="text-xs sm:text-sm text-gray-500">Browse all seller marketplace listings, perform verification, and assign certified grades.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200/60 text-amber-800 text-xs font-semibold">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Verified listings are locked from seller edits</span>
            </span>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <x-stat-card
            label="Total Listings"
            :value="$totalCount"
            hint="Across all sellers"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>'
        />

        <x-stat-card
            label="Verified & Graded"
            :value="$verifiedCount"
            hint="Certified & Locked"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />

        <x-stat-card
            label="Pending Queue"
            :value="$pendingCount"
            hint="Awaiting physical check"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />

        <x-stat-card
            label="Not Requested"
            :value="$unverifiedCount"
            hint="Direct seller pre-owned"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        />
    </div>

    {{-- Filter Toolbar --}}
    <x-card>
        <form method="GET" action="{{ route('verifier.products.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[220px]">
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

            <div class="w-40">
                <select name="category_id" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$selectedCategory === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-44">
                <select name="verification_status" class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">All Verification States</option>
                    <option value="verified" {{ $selectedStatus === 'verified' ? 'selected' : '' }}>Verified (Locked)</option>
                    <option value="not_requested" {{ $selectedStatus === 'not_requested' ? 'selected' : '' }}>Not Requested</option>
                    <option value="scheduled" {{ $selectedStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="inspecting" {{ $selectedStatus === 'inspecting' ? 'selected' : '' }}>Inspecting</option>
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

            @if ($search || $selectedCategory || $selectedStatus || $selectedCondition)
                <a href="{{ route('verifier.products.index') }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                    Reset
                </a>
            @endif
        </form>
    </x-card>

    <x-card>
        <x-table :headers="['Product Details', 'Seller', 'Price', 'Condition', 'Verification Status', 'Action']" id="verifier-products-table">
            @forelse ($products as $product)
                @php
                    $imgUrl = $product->primaryImageUrl();
                    $isVerified = $product->isVerified();
                @endphp
                <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                    {{-- Product Details --}}
                    <td class="px-4 py-3.5 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center text-gray-400 border border-gray-100">
                                <img src="{{ $imgUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('verifier.products.show', $product) }}" class="text-sm font-semibold text-gray-900 hover:text-amber-600 transition-colors block truncate max-w-xs" title="{{ $product->title }}">
                                    {{ $product->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $product->category->name }}
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

                    {{-- Condition --}}
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $product->condition->label() }}
                        </span>
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

                            {{-- View / Inspect Full Details --}}
                            <a href="{{ route('verifier.products.show', $product) }}" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="View device details & specs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

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
                                <p class="text-[11px] text-gray-500">Seller: <strong class="text-gray-800">{{ $product->user->name ?? 'Unknown' }}</strong> · {{ $product->category->name }}</p>
                                <p class="text-[11px] text-amber-700 font-medium">⚠️ Once certified, this product is locked from seller modification.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-800 mb-2">Certification Decision</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="border border-gray-200 has-checked:border-emerald-500 has-checked:bg-emerald-50/40 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition-all">
                                    <input type="radio" name="decision" value="pass" checked class="accent-emerald-600" onchange="toggleVerifyDecisionModal(this, '{{ $product->id }}')">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Pass & Certify</p>
                                        <p class="text-[10px] text-gray-500">Assign Grade & Lock</p>
                                    </div>
                                </label>

                                <label class="border border-gray-200 has-checked:border-red-500 has-checked:bg-red-50/40 rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition-all">
                                    <input type="radio" name="decision" value="fail" class="accent-red-600" onchange="toggleVerifyDecisionModal(this, '{{ $product->id }}')">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Fail & Reject</p>
                                        <p class="text-[10px] text-gray-500">Unmet standards</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="modal-pass-fields-{{ $product->id }}" class="space-y-3 p-3.5 bg-emerald-50/30 rounded-xl border border-emerald-100">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cosmetic Grade *</label>
                                    <select name="grade" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                        <option value="A" {{ ($product->grade ?? 'A') === 'A' ? 'selected' : '' }}>Grade A (Like New)</option>
                                        <option value="B" {{ ($product->grade ?? '') === 'B' ? 'selected' : '' }}>Grade B (Good)</option>
                                        <option value="C" {{ ($product->grade ?? '') === 'C' ? 'selected' : '' }}>Grade C (Fair)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Battery Health (%)</label>
                                    <input type="number" name="battery_health" min="0" max="100" placeholder="e.g. 92" class="w-full py-2 px-3 bg-white border border-gray-200 rounded-xl text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>

                        <div id="modal-fail-fields-{{ $product->id }}" class="hidden space-y-1.5 p-3.5 bg-red-50/30 rounded-xl border border-red-100">
                            <label class="block text-xs font-semibold text-red-900">Rejection Reason *</label>
                            <textarea name="reason" rows="2" placeholder="State reason for verification rejection..." class="w-full py-2 px-3 bg-white border border-red-200 rounded-xl text-xs text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1">Internal Notes (Optional)</label>
                            <textarea name="notes" rows="2" placeholder="Add any certificate or inspector comments..." class="w-full py-2 px-3 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">{{ $product->grade_notes }}</textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100">
                            <button type="button" data-modal-close class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-lg shadow-xs transition cursor-pointer">
                                Confirm & Submit
                            </button>
                        </div>
                    </form>
                </x-modal>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        No seller products found matching your filters.
                    </td>
                </tr>
            @endforelse
        </x-table>

        <div class="mt-4">
            <x-pagination :paginator="$products" />
        </div>
    </x-card>

    <script>
        function toggleVerifyDecisionModal(radio, id) {
            const passFields = document.getElementById(`modal-pass-fields-${id}`);
            const failFields = document.getElementById(`modal-fail-fields-${id}`);
            if (passFields && failFields) {
                const isPass = radio.value === 'pass';
                passFields.classList.toggle('hidden', !isPass);
                failFields.classList.toggle('hidden', isPass);
            }
        }
    </script>
@endsection
