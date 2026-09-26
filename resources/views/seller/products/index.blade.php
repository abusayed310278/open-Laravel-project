@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Products')

@php
    $routePrefix = auth()->user()->isBusiness() ? 'business.' : 'saler.';
    $isBusiness = auth()->user()->isBusiness();
    $headers = $isBusiness
        ? ['Product', 'Seller / Store', 'Category', 'Price', 'Status', 'Approval', 'Actions']
        : ['Product', 'Category', 'Price', 'Status', 'Approval', 'Actions'];
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('product')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <x-slot:title>
            <div class="flex items-center gap-2">
                <span>Products</span>
                @if ($isBusiness)
                    <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Seller Products</span>
                @endif
            </div>
        </x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route($routePrefix.'products.create')" size="sm">Add Product</x-button>
        </x-slot:action>

        <x-table :headers="$headers" id="products-table">
            @forelse ($products as $product)
                @php
                    $imgUrl = $product->primaryImageUrl();
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 flex items-center gap-3">
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
                    </td>

                    @if ($isBusiness)
                        <td class="px-4 py-3 text-sm">
                            <span class="font-medium text-gray-900 block truncate" title="{{ $product->user->name ?? 'Unknown' }}">
                                {{ \Illuminate\Support\Str::words($product->user->name ?? 'Unknown', 4, '...') }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $product->user?->role?->label() ?? 'Seller' }}</span>
                        </td>
                    @endif

                    <td class="px-4 py-3 text-gray-500 text-sm whitespace-nowrap">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-800 font-medium text-sm whitespace-nowrap">${{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-3 whitespace-nowrap"><x-badge :color="$product->status->badgeColor()">{{ $product->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 whitespace-nowrap"><x-badge :color="$product->approval_status->value === 'approved' ? 'green' : ($product->approval_status->value === 'rejected' ? 'red' : 'gray')">{{ $product->approval_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Publish Button (if approved) --}}
                            @if ($product->approval_status->value === 'approved')
                                @if ($product->publication_status?->value === 'published')
                                    <form method="POST" action="{{ route($routePrefix.'products.toggle-publish', $product) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Published (Click to Unpublish)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route($routePrefix.'products.toggle-publish', $product) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Unpublished (Click to Publish)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- Preview Icon --}}
                            <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Preview on website">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            {{-- Edit / Locked Icon --}}
                            @if ($product->isLocked())
                                <span class="p-1.5 text-amber-700 bg-amber-50/80 border border-amber-200/60 rounded-lg inline-flex items-center cursor-not-allowed" title="Certified & Verified — Locked from editing">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                            @else
                                <a href="{{ route($routePrefix.'products.edit', [$product, 'redirect_to' => request()->fullUrl()]) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit product">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            @endif

                            {{-- Delete Icon (Triggers Centered Delete Modal) --}}
                            <button type="button" data-modal-open="delete-product-modal-{{ $product->id }}" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete product">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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

                                    <form method="POST" action="{{ route($routePrefix.'products.destroy', $product) }}" class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
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
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $isBusiness ? 7 : 6 }}" class="px-4 py-10 text-center text-gray-400 text-sm">No products yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$products" />
    </x-card>
@endsection
