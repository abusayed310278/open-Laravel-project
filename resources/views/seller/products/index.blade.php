@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Products')

@php
    $routePrefix = auth()->user()->isBusiness() ? 'business.' : 'saler.';
@endphp

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @error('product')
        <x-alert type="error">{{ $message }}</x-alert>
    @enderror

    <x-card>
        <x-slot:title>Products</x-slot:title>
        <x-slot:action>
            <x-button as="a" :href="route($routePrefix.'products.create')" size="sm">Add Product</x-button>
        </x-slot:action>

        <x-table :headers="['Product', 'Category', 'Price', 'Status', 'Approval', '']" id="products-table">
            @forelse ($products as $product)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-md bg-gray-50 flex-shrink-0 overflow-hidden">
                            @if ($product->images->isNotEmpty())
                                <img src="{{ $product->images->first()->url() }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <span class="font-medium text-gray-900">{{ $product->title }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-3"><x-badge :color="$product->status->badgeColor()">{{ $product->status->label() }}</x-badge></td>
                    <td class="px-4 py-3"><x-badge :color="$product->approval_status->value === 'approved' ? 'green' : ($product->approval_status->value === 'rejected' ? 'red' : 'gray')">{{ $product->approval_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- Toggle Publish Button (if approved) --}}
                            @if ($product->approval_status->value === 'approved')
                                @if ($product->publication_status?->value === 'published')
                                    <form method="POST" action="{{ route($routePrefix.'products.toggle-publish', $product) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Published (Click to Unpublish)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route($routePrefix.'products.toggle-publish', $product) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Unpublished (Click to Publish)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- Preview Icon --}}
                            <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener noreferrer" class="p-1.5 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Preview on website">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            {{-- Edit Icon --}}
                            <a href="{{ route($routePrefix.'products.edit', $product) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Edit product">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route($routePrefix.'products.destroy', $product) }}" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-confirm="Are you sure you want to delete this product?" class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete product">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>

                            @if (in_array($product->status->value, ['draft', 'rejected'], true))
                                <form method="POST" action="{{ route($routePrefix.'products.submit', $product) }}" class="inline-block m-0">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium border border-gray-200 hover:bg-gray-50 rounded-md px-2 py-1 ml-1">
                                        {{ $product->status->value === 'rejected' ? 'Resubmit' : 'Submit' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No products yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$products" />
    </x-card>
@endsection
