@extends('layouts.admin')

@section('title', 'Products')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <form id="bulk-delete-form" method="POST" action="{{ route('admin.products.bulk-destroy') }}">
        @csrf
        @method('DELETE')
    </form>

    <x-card>
        <x-slot:title>Products</x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route('admin.products.create')" size="sm">Add Product</x-button>
        </x-slot:action>

        {{-- SEARCH BAR & BULK ACTIONS TOOLBAR --}}
        <div class="mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/70 p-4 rounded-xl border border-gray-100">
            <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-3xl">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Search products by title, SKU, brand, seller..." 
                        class="w-full border border-gray-200 rounded-lg pl-9 pr-4 py-2 text-sm bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    >
                </div>

                <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3.5 py-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-500 shrink-0">
                    <option value="">All approval statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-sm font-medium rounded-lg shadow-2xs transition shrink-0 cursor-pointer">
                    Search
                </button>

                @if (request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-900 transition flex items-center gap-1 shrink-0">
                        Clear Filters
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-gray-100 shrink-0">
                <button 
                    type="submit" 
                    form="bulk-delete-form"
                    id="bulk-delete-btn" 
                    disabled 
                    onclick="return confirm('Are you sure you want to delete the selected product(s)?')" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:cursor-not-allowed rounded-lg shadow-2xs transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    <span>Delete Selected (<span id="selected-count">0</span>)</span>
                </button>
            </div>
        </div>

        <x-table :headers="['<input type=\'checkbox\' id=\'select-all-products\' class=\'rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer\'>', 'Product', 'Seller', 'Category', 'Price', 'Status', 'Approval', 'Actions']" id="products-table" class="min-w-[1200px]">
            @forelse ($products as $product)
                @php
                    $imgUrl = $product->primaryImageUrl();
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 w-10">
                        <input type="checkbox" name="ids[]" value="{{ $product->id }}" form="bulk-delete-form" class="product-checkbox rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-md bg-gray-100 flex-shrink-0 overflow-hidden flex items-center justify-center text-gray-400 border border-gray-100">
                                @if ($imgUrl)
                                    <img
                                        src="{{ $imgUrl }}"
                                        alt="{{ $product->title }}"
                                        class="w-full h-full object-cover"
                                        onerror="this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');"
                                    >
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
                                <span class="font-medium text-gray-900 block truncate" title="{{ $product->title }}">
                                    {{ \Illuminate\Support\Str::words($product->title, 4, '...') }}
                                </span>
                                @if ($product->sku)
                                    <span class="text-xs text-gray-400 font-mono block truncate">SKU: {{ $product->sku }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm">
                        <span class="font-medium text-gray-900 block truncate" title="{{ $product->user->name ?? 'Unknown' }}">
                            {{ \Illuminate\Support\Str::words($product->user->name ?? 'Unknown', 1, '...') }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $product->user?->role?->label() ?? 'Seller' }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-sm whitespace-nowrap">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-800 font-medium text-sm whitespace-nowrap">${{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap"><x-badge :color="$product->status->badgeColor()">{{ $product->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <x-badge :color="$product->approval_status->value === 'approved' ? 'green' : ($product->approval_status->value === 'rejected' ? 'red' : 'gray')">
                            {{ $product->approval_status->label() }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- 1. View Icon --}}
                            <button type="button" data-modal-open="view-product-{{ $product->id }}" class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition cursor-pointer" title="View details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            {{-- 3. Active / Inactive Icon --}}
                            @if ($product->publication_status?->value === 'published')
                                <form method="POST" action="{{ route('admin.products.toggle-publish', $product) }}" class="inline-block m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Active / Published (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.products.toggle-publish', $product) }}" class="inline-block m-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Inactive / Unpublished (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    </button>
                                </form>
                            @endif

                            {{-- 4. Edit Icon --}}
                            <a href="{{ route('admin.products.edit', [$product, 'redirect_to' => request()->fullUrl()]) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit product">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            {{-- 5. Delete Icon (Triggers Centered Delete Modal) --}}
                            <button type="button" data-modal-open="delete-product-modal-{{ $product->id }}" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete product">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            {{-- Centered Delete Product Confirmation Modal --}}
                            <x-modal :id="'delete-product-modal-'.$product->id" title="Delete Product" maxWidth="max-w-md">
                                <div class="text-center py-2 space-y-4">
                                    <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center mx-auto text-red-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 mb-1">Are you sure?</h4>
                                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed">
                                            Do you really want to delete <strong class="text-gray-900 font-semibold">{{ $product->title }}</strong>? This action cannot be undone.
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

                                        <button type="button" data-modal-close class="px-4 py-2 text-xs sm:text-sm font-semibold text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                                            Cancel
                                        </button>
                                        <button type="submit" class="px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-2xs transition cursor-pointer">
                                            Yes, Delete Product
                                        </button>
                                    </form>
                                </div>
                            </x-modal>

                            @if ($product->status->value === 'pending_approval')
                                <form method="POST" action="{{ route('admin.products.approve', $product) }}" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium bg-brand-500 hover:bg-brand-600 text-white rounded-md px-2 py-1 cursor-pointer" title="Approve listing">Approve</button>
                                </form>
                                <button type="button" data-modal-open="reject-{{ $product->id }}" class="text-xs font-medium text-red-500 hover:text-red-700 cursor-pointer" title="Reject listing">Reject</button>

                                <x-modal :id="'reject-'.$product->id" title="Reject listing">
                                    <form method="POST" action="{{ route('admin.products.reject', $product) }}" class="space-y-4">
                                        @csrf
                                        <x-textarea name="reason" label="Reason" rows="3" />
                                        <x-button type="submit" variant="danger">Reject</x-button>
                                    </form>
                                </x-modal>
                            @endif
                        </div>

                        {{-- Product View Details Modal --}}
                        <x-modal :id="'view-product-'.$product->id" title="Product Details: {{ $product->title }}" maxWidth="max-w-2xl">
                            <div class="space-y-4 text-left">
                                <div class="flex items-start gap-4 pb-4 border-b border-gray-100">
                                    <div class="w-20 h-20 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border border-gray-200 flex items-center justify-center">
                                        @if ($imgUrl)
                                            <img src="{{ $imgUrl }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" /></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-base font-semibold text-gray-900 leading-snug">{{ $product->title }}</h4>
                                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500">
                                            @if ($product->sku)
                                                <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">SKU: {{ $product->sku }}</span>
                                            @endif
                                            <span>Category: <strong>{{ $product->category->name ?? 'N/A' }}</strong></span>
                                            @if ($product->brand)
                                                <span>Brand: <strong>{{ $product->brand->name }}</strong></span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <x-badge :color="$product->status->badgeColor()">{{ $product->status->label() }}</x-badge>
                                            <x-badge :color="$product->approval_status->value === 'approved' ? 'green' : ($product->approval_status->value === 'rejected' ? 'red' : 'gray')">
                                                {{ $product->approval_status->label() }}
                                            </x-badge>
                                            @if ($product->publication_status?->value === 'published')
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Published (Active)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Unpublished (Inactive)
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-xs sm:text-sm">
                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span class="text-gray-500 block text-xs">Price</span>
                                        <span class="font-semibold text-gray-900 text-base">${{ number_format($product->price, 2) }}</span>
                                        @if ($product->compare_price)
                                            <span class="text-xs text-gray-400 line-through ml-1">${{ number_format($product->compare_price, 2) }}</span>
                                        @endif
                                    </div>

                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span class="text-gray-500 block text-xs">Stock / Quantity</span>
                                        <span class="font-semibold text-gray-900 text-base">{{ $product->quantity ?? 0 }} units</span>
                                    </div>

                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span class="text-gray-500 block text-xs">Seller</span>
                                        <span class="font-medium text-gray-900 block truncate">{{ $product->user->name ?? 'Unknown' }}</span>
                                        <span class="text-xs text-gray-400">{{ $product->user->email ?? '' }}</span>
                                    </div>

                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span class="text-gray-500 block text-xs">Created At</span>
                                        <span class="font-medium text-gray-900 block">{{ $product->created_at?->format('M d, Y H:i') }}</span>
                                    </div>
                                </div>

                                @if ($product->description)
                                    <div>
                                        <h5 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Description</h5>
                                        <div class="text-xs text-gray-700 max-h-56 overflow-y-auto bg-gray-50 p-3.5 rounded-lg border border-gray-100 leading-relaxed prose prose-sm max-w-none">
                                            {!! $product->description !!}
                                        </div>
                                    </div>
                                @endif

                                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.products.edit', [$product, 'redirect_to' => request()->fullUrl()]) }}" class="inline-flex items-center gap-1.5 text-xs font-medium bg-gray-900 hover:bg-black text-white px-3 py-2 rounded-md transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        Edit Product
                                    </a>
                                    <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-2 rounded-md transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Live Preview
                                    </a>
                                </div>
                            </div>
                        </x-modal>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">No products found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$products" />
    </x-card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all-products');
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
            const selectedCount = document.getElementById('selected-count');

            function updateState() {
                const checked = document.querySelectorAll('.product-checkbox:checked');
                const count = checked.length;
                if (selectedCount) selectedCount.textContent = count;
                if (bulkDeleteBtn) bulkDeleteBtn.disabled = count === 0;
                if (selectAll) {
                    selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
                    selectAll.indeterminate = count > 0 && count < checkboxes.length;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateState();
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateState);
            });

            updateState();
        });
    </script>
@endsection
