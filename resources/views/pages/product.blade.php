@extends('layouts.app')

@section('title', $product->title)

@section('content')
    @if ($isPreview ?? false)
        <div class="bg-amber-500 text-white text-xs font-semibold px-4 py-2.5 text-center sticky top-0 z-50 shadow-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <span>Preview Mode — You are previewing this product ({{ auth()->user()?->isAdmin() ? 'Admin' : 'Seller' }} Preview). It is currently <span class="uppercase tracking-wider font-bold underline">{{ $product->status->label() }}</span> and not visible to buyers.</span>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <x-breadcrumb :items="[$product->category->name => route('categories.show', $product->category), $product->title => null]" class="mb-6" />

        <div class="grid lg:grid-cols-5 gap-10">
            {{-- Gallery --}}
            <div class="lg:col-span-2">
                @php
                    $galleryImages = ($product->images && $product->images->isNotEmpty())
                        ? $product->images->map(fn($img) => $img->url())->values()
                        : collect([$product->primaryImageUrl()]);
                @endphp

                {{-- Main Product Image (Click to Open Lightbox Popup) --}}
                <div id="main-img-wrapper" class="relative group bg-gray-50 rounded-xl aspect-square flex items-center justify-center overflow-hidden cursor-zoom-in" title="Click to view full image">
                    <img id="main-img" src="{{ $product->primaryImageUrl() }}" alt="{{ $product->title }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                    <div class="absolute bottom-3 right-3 bg-black/60 hover:bg-black/80 backdrop-blur-xs text-white text-xs px-2.5 py-1.5 rounded-lg flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity shadow-md pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                        <span>Click to zoom</span>
                    </div>
                </div>

                @if ($galleryImages->count() > 1)
                    <div class="grid grid-cols-4 gap-3 mt-3">
                        @foreach ($galleryImages as $index => $imageUrl)
                            <img src="{{ $imageUrl }}" data-index="{{ $index }}" class="thumb-img aspect-square w-full object-cover rounded-lg cursor-pointer border-2 transition-all duration-150 hover:opacity-100 hover:scale-[1.03] {{ $index === 0 ? 'border-brand-500 shadow-xs opacity-100' : 'border-transparent opacity-80 hover:border-gray-300' }}">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="lg:col-span-3">
                <div class="flex items-center gap-2 mb-3">
                    @if (feature_enabled('product_verification_badge'))
                        @if ($product->isVerified())
                            <x-grade-badge :grade="$product->grade->value" />
                            <x-verified-badge />
                        @else
                            <x-badge color="blue">{{ $product->condition->label() }}</x-badge>
                        @endif
                        @if ($product->isWarehoused())
                            <x-verified-badge warehouse />
                        @endif
                    @endif
                    @if ($product->is_negotiable)
                        <x-badge color="gray">Negotiable</x-badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $product->title }}</h1>

                <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
                    <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                        <span class="text-gray-400">Price:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($product->price, 2) }}</span>
                    </div>
                    @if ($product->compare_price)
                        <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                            <span class="text-gray-400">Regular Price:</span>
                            <span class="font-semibold text-gray-900">${{ number_format($product->compare_price, 2) }}</span>
                        </div>
                    @endif
                    <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                        <span class="text-gray-400">Status:</span>
                        <span class="font-semibold {{ $product->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $product->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>
                    @if ($product->sku)
                        <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                            <span class="text-gray-400">Product Code:</span>
                            <span class="font-semibold text-gray-900">{{ $product->sku }}</span>
                        </div>
                    @endif
                    @if ($product->brand)
                        <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                            <span class="text-gray-400">Brand:</span>
                            <span class="font-semibold text-gray-900">{{ $product->brand->name }}</span>
                        </div>
                    @endif
                </div>

                @if ($product->short_description)
                    <div class="prose prose-sm max-w-none text-gray-600 mb-4 leading-relaxed">
                        {!! $product->short_description !!}
                    </div>
                @endif

                @if ($reviewCount > 0)
                    <div class="flex items-center gap-2 mb-4">
                        <x-star-rating :rating="round($averageRating)" />
                        <span class="text-sm text-gray-500">{{ number_format($averageRating, 1) }} ({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                    </div>
                @endif

                <div class="flex items-baseline gap-3 mb-6">
                    <span class="text-3xl font-bold text-gray-900">${{ number_format($product->price, 2) }}</span>
                    @if ($product->compare_price)
                        <span class="text-base text-gray-400 line-through">${{ number_format($product->compare_price, 2) }}</span>
                        @php
                            $rawSave = (($product->compare_price - $product->price) / $product->compare_price) * 100;
                            $saveStr = number_format($rawSave, 4, '.', '');
                            [$saveWhole, $saveDec] = explode('.', $saveStr);
                            $saveDigit = substr($saveDec, 0, 1);
                            $savePercent = $saveDigit === '0' ? $saveWhole : "{$saveWhole}.{$saveDigit}";
                        @endphp
                        <x-badge color="red">
                            Save {{ $savePercent }}%
                        </x-badge>
                    @endif
                </div>

                {{-- Seller card --}}
                @if (feature_enabled('product_seller_box'))
                    <div class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-4 mb-6 bg-white shadow-2xs">
                        <a href="{{ $sellerUrl ?? '#' }}" class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-amber-50 border border-gray-200 text-amber-700 font-bold flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if (!empty($sellerLogoUrl))
                                    <img src="{{ $sellerLogoUrl }}" alt="{{ $sellerName }}" class="w-full h-full object-cover">
                                @else
                                    <span>{{ Str::upper(Str::substr($sellerName, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-semibold text-gray-900 truncate">{{ $sellerName }}</span>
                                    @if (($isAdminProduct ?? false) || $product->user->isKycApproved() || $product->user->hasActiveSubscription())
                                        <x-verified-badge />
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">
                                    @if ($isAdminProduct ?? false)
                                        Official Platform Store
                                    @else
                                        @if ($sellerReviewCount > 0)
                                            ★ {{ number_format($sellerRating, 1) }}
                                        @endif
                                        @if ($sellerLocation)
                                            · {{ $sellerLocation }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </a>

                        @if (!($isAdminProduct ?? false) && feature_enabled('product_chat_widget'))
                            @auth
                                @if (auth()->id() !== $product->user_id)
                                    <form method="POST" action="{{ route('chat.start', $product) }}">
                                        @csrf
                                        <x-button type="submit" variant="secondary" size="sm">Contact</x-button>
                                    </form>
                                @endif
                            @endauth
                        @endif
                    </div>
                @endif

                {{-- Purchase actions --}}
                @if (auth()->check() && !auth()->user()->isCustomer())
                    <div class="p-3.5 bg-amber-50 border border-amber-200/80 rounded-lg text-xs text-amber-800 font-medium mb-4 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Purchasing items is restricted to Customer accounts. (Logged in as {{ auth()->user()->role->label() }})</span>
                    </div>
                @elseif (feature_enabled('product_buy_box'))
                    <form method="POST" action="{{ route('cart.add', $product) }}">
                        @csrf
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex items-center border border-gray-200 rounded-md">
                                <button type="button" data-qty="decrement" class="px-3 py-2.5 text-gray-500 hover:text-gray-800">&minus;</button>
                                <input type="number" name="quantity" value="1" min="1" class="w-12 text-center border-0 focus:ring-0 text-sm">
                                <button type="button" data-qty="increment" class="px-3 py-2.5 text-gray-500 hover:text-gray-800">+</button>
                            </div>
                            <x-button type="submit" class="flex-1 justify-center">Add to Cart</x-button>
                            <x-button type="submit" name="checkout" value="1" variant="dark" class="flex-1 justify-center">Buy Now</x-button>
                        </div>
                    </form>
                @endif

                @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="mb-6">
                        @csrf
                        <x-button type="submit" variant="secondary" class="w-full justify-center">♡ Add to Wishlist</x-button>
                    </form>
                @endauth

                <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 8h-3V4H3a1 1 0 00-1 1v11a1 1 0 001 1h1m0 0a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0m2-8h4l3 3v5h-2m-9-8v8m0-8H9" /></svg>
                        {{ $product->shipping_type->label() }}{{ $product->shipping_type->value === 'flat_rate' && $product->shipping_flat_rate ? ' ($'.number_format($product->shipping_flat_rate, 2).')' : '' }}
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Buyer Protection
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Secure Payment
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Ships from {{ $sellerLocation ?? 'Openbox' }}
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-y-2 text-sm border-t border-gray-100 pt-4">
                    <dt class="text-gray-400">SKU</dt>
                    <dd class="text-gray-800 font-medium">{{ $product->sku ?? '—' }}</dd>
                    <dt class="text-gray-400">Condition</dt>
                    <dd class="text-gray-800 font-medium">{{ $product->condition->label() }}</dd>
                </dl>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mt-12">
            <div class="border-b border-gray-200 flex items-center gap-8 mb-6">
                @if (feature_enabled('product_specifications'))
                    <button type="button" data-tab="specs" class="tab-btn pb-3 text-sm sm:text-base font-semibold border-b-2 border-brand-500 text-brand-600 transition-colors duration-150 cursor-pointer -mb-px">Specification</button>
                @endif
                <button type="button" data-tab="description" class="tab-btn pb-3 text-sm sm:text-base font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors duration-150 cursor-pointer -mb-px">Description</button>
                @if (feature_enabled('product_reviews'))
                    <button type="button" data-tab="reviews" class="tab-btn pb-3 text-sm sm:text-base font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors duration-150 cursor-pointer -mb-px">Reviews ({{ $reviewCount }})</button>
                @endif
            </div>

            @if (feature_enabled('product_specifications'))
                <div data-tab-content="specs" class="pb-6">
                    <h2 class="text-lg sm:text-xl font-bold text-[#01132d] mb-4">Specification</h2>

                    @php
                        $savedValues = $product->attributeValues->keyBy('attribute_id');
                        $categoryAttributes = $product->category?->attributes ?? collect();

                        if ($categoryAttributes->isNotEmpty()) {
                            $groupedSpecs = $categoryAttributes
                                ->sortBy([
                                    fn ($a, $b) => ($a->group?->sort_order ?? 999) <=> ($b->group?->sort_order ?? 999),
                                    fn ($a, $b) => ($a->pivot?->sort_order ?? $a->sort_order ?? 999) <=> ($b->pivot?->sort_order ?? $b->sort_order ?? 999),
                                ])
                                ->groupBy(fn ($a) => $a->group?->name ?? 'General Specifications');
                        } else {
                            $groupedSpecs = $product->attributeValues
                                ->sortBy([
                                    fn ($a, $b) => ($a->attribute?->group?->sort_order ?? 999) <=> ($b->attribute?->group?->sort_order ?? 999),
                                    fn ($a, $b) => ($a->attribute?->sort_order ?? 999) <=> ($b->attribute?->sort_order ?? 999),
                                ])
                                ->groupBy(fn ($v) => $v->attribute?->group?->name ?? 'General Specifications');
                        }
                    @endphp

                    @if ($groupedSpecs->isEmpty())
                        <div class="text-center py-8 bg-gray-50 rounded-md border border-dashed border-gray-200">
                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="text-gray-500 text-sm font-medium">No specifications listed for this product.</p>
                            <p class="text-gray-400 text-xs mt-1">Specifications can be assigned in the product edit form under Attributes.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($groupedSpecs as $groupName => $items)
                                <div class="bg-white rounded-sm overflow-hidden">
                                    <div class="bg-[#f5f6fc] px-4 py-2.5">
                                        <h3 class="text-[14px] font-bold text-[#3749bb] tracking-tight">{{ $groupName }}</h3>
                                    </div>
                                    <table class="w-full text-left border-collapse text-[13.5px]">
                                        <tbody>
                                            @foreach ($items as $item)
                                                @php
                                                    if ($item instanceof \App\Models\Attribute) {
                                                        $attr = $item;
                                                        $saved = $savedValues->get($attr->id);
                                                        $savedVal = $saved?->displayValue();
                                                        $val = ($savedVal !== null && $savedVal !== '') ? $savedVal : $attr->unit;
                                                    } else {
                                                        $attr = $item->attribute;
                                                        $savedVal = $item->displayValue();
                                                        $val = ($savedVal !== null && $savedVal !== '') ? $savedVal : $attr?->unit;
                                                    }
                                                @endphp
                                                <tr class="hover:bg-[#fafbfe] transition-colors">
                                                    <td class="w-1/3 sm:w-1/4 px-4 py-2.5 text-[#666666] font-normal align-top leading-relaxed">
                                                        {{ $attr?->name ?? 'Specification' }}
                                                    </td>
                                                    <td class="px-4 py-2.5 text-[#111111] font-normal align-top leading-relaxed">
                                                        {{ $val ?: '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div data-tab-content="description" class="py-6 text-gray-700 leading-relaxed hidden">
                @if ($product->description)
                    <div class="prose max-w-none text-sm sm:text-base leading-relaxed">
                        {!! $product->description !!}
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <p class="text-gray-500 text-sm font-medium">No full description provided.</p>
                        <p class="text-gray-400 text-xs mt-1">You can add a full description by editing this product.</p>
                    </div>
                @endif
            </div>

            @if (feature_enabled('product_reviews'))
                <div data-tab-content="reviews" class="py-6 hidden">
                    @if ($reviewCount > 0)
                        <div class="flex items-center gap-3 mb-6">
                            <x-star-rating :rating="round($averageRating)" />
                            <span class="text-sm text-gray-500">{{ number_format($averageRating, 1) }} out of 5 · {{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</span>
                        </div>
                    @endif

                    @forelse ($reviews as $review)
                        <div class="py-4 border-b border-gray-50 last:border-0">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <x-star-rating :rating="$review->rating" />
                                    <span class="text-sm font-medium text-gray-800">{{ $review->reviewer->name }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ $review->created_at->format('M j, Y') }}</span>
                            </div>
                            @if ($review->title)
                                <p class="font-medium text-gray-900 mt-1">{{ $review->title }}</p>
                            @endif
                            <p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">No reviews yet.</p>
                    @endforelse
                </div>
            @endif
        </div>

        {{-- Similar products --}}
        @if (feature_enabled('product_related_items'))
            @if ($related->isNotEmpty())
                <div class="mt-12">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">Similar Products</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($related as $item)
                            <x-product-card
                                :product="$item"
                                :title="$item->title"
                                :price="$item->price"
                                :compare-price="$item->compare_price"
                                :category="$item->category?->name ?? $item->brand?->name ?? 'Electronics'"
                                :brand="$item->brand?->name"
                                :grade="$item->isVerified() ? $item->grade->value : null"
                                :condition="$item->condition->value"
                                :image="$item->primaryImageUrl()"
                                :href="route('products.show', $item)"
                            />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- More from this seller --}}
            @if ($moreFromSeller->isNotEmpty())
                <div class="mt-12">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">More from {{ $sellerName }}</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($moreFromSeller as $item)
                            <x-product-card
                                :product="$item"
                                :title="$item->title"
                                :price="$item->price"
                                :compare-price="$item->compare_price"
                                :category="$item->category?->name ?? $item->brand?->name ?? 'Electronics'"
                                :brand="$item->brand?->name"
                                :grade="$item->isVerified() ? $item->grade->value : null"
                                :condition="$item->condition->value"
                                :image="$item->primaryImageUrl()"
                                :href="route('products.show', $item)"
                            />
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Product Image Lightbox Modal: Centered Square Size with Side Circular Arrows --}}
    <div
        id="imageLightboxModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6 bg-white/75 sm:bg-white/80 backdrop-blur-md opacity-0 pointer-events-none transition-opacity duration-200 select-none"
        role="dialog"
        aria-modal="true"
        aria-label="Image gallery lightbox"
    >
        {{-- Close Button (Top Right) --}}
        <button
            type="button"
            id="lightboxCloseBtn"
            class="fixed top-5 right-5 sm:top-8 sm:right-8 z-50 w-11 h-11 rounded-full bg-white hover:bg-gray-100 text-gray-800 shadow-xl border border-gray-200 flex items-center justify-center transition-all hover:scale-110 active:scale-95 cursor-pointer"
            title="Close (Esc)"
            aria-label="Close lightbox"
        >
            <svg class="w-5 h-5 stroke-[2.2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Center of Page: Square Size Modal Image Container --}}
        <div
            id="lightboxSquareCard"
            class="relative w-[92vw] max-w-[540px] aspect-square max-h-[85vh] bg-white rounded-2xl shadow-2xl border border-gray-200/80 p-4 sm:p-5 flex flex-col items-center justify-between select-none"
        >
            {{-- Left Arrow Icon (Bare chevron at red marked area, NO circle) --}}
            @if ($galleryImages->count() > 1)
                <button
                    type="button"
                    id="lightboxPrevBtn"
                    class="absolute -left-6 sm:-left-12 top-1/2 -translate-y-1/2 z-40 text-gray-700 hover:text-black hover:scale-125 transition-transform p-1 cursor-pointer focus:outline-none select-none"
                    title="Previous image (Left Arrow)"
                    aria-label="Previous image"
                >
                    <svg class="w-8 h-8 sm:w-11 sm:h-11 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                {{-- Right Arrow Icon (Bare chevron at red marked area, NO circle) --}}
                <button
                    type="button"
                    id="lightboxNextBtn"
                    class="absolute -right-6 sm:-right-12 top-1/2 -translate-y-1/2 z-40 text-gray-700 hover:text-black hover:scale-125 transition-transform p-1 cursor-pointer focus:outline-none select-none"
                    title="Next image (Right Arrow)"
                    aria-label="Next image"
                >
                    <svg class="w-8 h-8 sm:w-11 sm:h-11 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @endif

            {{-- Image Counter Badge --}}
            <div class="absolute top-3.5 left-4 z-30">
                <span id="lightboxCounter" class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-900/80 text-white shadow-xs">
                    1 / {{ $galleryImages->count() }}
                </span>
            </div>

            {{-- Image Stage with Smooth Slide Animation (Fills upper area) --}}
            <div id="lightboxStage" class="relative flex-1 w-full min-h-0 flex items-center justify-center overflow-hidden cursor-default p-2">
                <img
                    id="lightboxMainImg"
                    src="{{ $product->primaryImageUrl() }}"
                    alt="{{ $product->title }}"
                    class="w-full h-full object-contain transition-all duration-200 select-none pointer-events-none"
                />
            </div>

            {{-- Bottom Thumbnail Strip (Directly in the bottom of the card) --}}
            @if ($galleryImages->count() > 1)
                <div class="shrink-0 w-fit max-w-[90%] z-30 mt-1 mb-1 flex items-center gap-2 overflow-x-auto px-3 py-1.5 bg-black/60 backdrop-blur-md rounded-xl border border-white/10 shadow-lg no-scrollbar">
                    @foreach ($galleryImages as $index => $imageUrl)
                        <button
                            type="button"
                            data-lightbox-index="{{ $index }}"
                            class="lightbox-thumb-btn w-10 h-10 rounded-lg overflow-hidden border-2 transition-all flex-shrink-0 cursor-pointer {{ $index === 0 ? 'border-brand-500 ring-2 ring-brand-500/40 scale-105 opacity-100' : 'border-transparent opacity-60 hover:opacity-100' }}"
                            title="View image {{ $index + 1 }}"
                        >
                            <img src="{{ $imageUrl }}" alt="Thumbnail {{ $index + 1 }}" class="w-full h-full object-cover pointer-events-none">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-qty]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('input[name="quantity"]');
                const value = parseInt(input.value, 10) || 1;
                input.value = btn.dataset.qty === 'increment' ? value + 1 : Math.max(1, value - 1);
            });
        });

        // Gallery & Lightbox Popup functionality
        const galleryImages = @json($galleryImages);
        let currentImageIndex = 0;

        const mainImgWrapper = document.getElementById('main-img-wrapper');
        const mainImg = document.getElementById('main-img');
        const thumbs = document.querySelectorAll('.thumb-img');

        const lightboxModal = document.getElementById('imageLightboxModal');
        const lightboxMainImg = document.getElementById('lightboxMainImg');
        const lightboxCounter = document.getElementById('lightboxCounter');
        const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');
        const lightboxPrevBtn = document.getElementById('lightboxPrevBtn');
        const lightboxNextBtn = document.getElementById('lightboxNextBtn');
        const lightboxThumbs = document.querySelectorAll('.lightbox-thumb-btn');

        function updateGalleryView(index, animateDirection = null) {
            if (!galleryImages || galleryImages.length === 0) return;
            if (index < 0) index = galleryImages.length - 1;
            if (index >= galleryImages.length) index = 0;
            currentImageIndex = index;

            const targetUrl = galleryImages[currentImageIndex];

            // Update main image on product page
            if (mainImg) {
                mainImg.src = targetUrl;
            }

            // Update thumbnails on product page
            thumbs.forEach((t, i) => {
                if (i === currentImageIndex) {
                    t.classList.remove('border-transparent', 'opacity-80');
                    t.classList.add('border-brand-500', 'shadow-xs', 'opacity-100');
                } else {
                    t.classList.remove('border-brand-500', 'shadow-xs', 'opacity-100');
                    t.classList.add('border-transparent', 'opacity-80');
                }
            });

            // Update lightbox modal if open
            if (lightboxMainImg) {
                if (animateDirection) {
                    const outX = animateDirection === 'next' ? '-24px' : '24px';
                    const inX = animateDirection === 'next' ? '24px' : '-24px';
                    lightboxMainImg.style.opacity = '0';
                    lightboxMainImg.style.transform = `translateX(${outX}) scale(0.96)`;

                    setTimeout(() => {
                        lightboxMainImg.src = targetUrl;
                        lightboxMainImg.style.transform = `translateX(${inX}) scale(0.96)`;
                        requestAnimationFrame(() => {
                            lightboxMainImg.style.opacity = '1';
                            lightboxMainImg.style.transform = 'translateX(0) scale(1)';
                        });
                    }, 120);
                } else {
                    lightboxMainImg.src = targetUrl;
                    lightboxMainImg.style.opacity = '1';
                    lightboxMainImg.style.transform = 'translateX(0) scale(1)';
                }
            }

            if (lightboxCounter) {
                lightboxCounter.textContent = `${currentImageIndex + 1} / ${galleryImages.length}`;
            }

            // Update lightbox bottom filmstrip thumbnails
            lightboxThumbs.forEach((tb, i) => {
                if (i === currentImageIndex) {
                    tb.classList.remove('border-transparent', 'opacity-60');
                    tb.classList.add('border-brand-500', 'ring-2', 'ring-brand-500/40', 'scale-105', 'opacity-100');
                    tb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                } else {
                    tb.classList.remove('border-brand-500', 'ring-2', 'ring-brand-500/40', 'scale-105', 'opacity-100');
                    tb.classList.add('border-transparent', 'opacity-60');
                }
            });
        }

        // Open Lightbox
        function openLightbox(index = currentImageIndex) {
            if (!lightboxModal) return;
            updateGalleryView(index);
            lightboxModal.classList.remove('opacity-0', 'pointer-events-none');
            lightboxModal.classList.add('opacity-100', 'pointer-events-auto');
            document.body.style.overflow = 'hidden';
        }

        // Close Lightbox
        function closeLightbox() {
            if (!lightboxModal) return;
            lightboxModal.classList.remove('opacity-100', 'pointer-events-auto');
            lightboxModal.classList.add('opacity-0', 'pointer-events-none');
            document.body.style.overflow = '';
        }

        // Main Image click -> opens lightbox
        if (mainImgWrapper) {
            mainImgWrapper.addEventListener('click', () => openLightbox(currentImageIndex));
        }

        // Thumbnail hover -> switches main image; click -> opens lightbox
        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('mouseenter', () => updateGalleryView(index));
            thumb.addEventListener('click', () => openLightbox(index));
        });

        // Lightbox buttons
        if (lightboxCloseBtn) lightboxCloseBtn.addEventListener('click', closeLightbox);
        if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            updateGalleryView(currentImageIndex - 1, 'prev');
        });
        if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            updateGalleryView(currentImageIndex + 1, 'next');
        });

        // Click outside square card on backdrop closes lightbox
        if (lightboxModal) {
            lightboxModal.addEventListener('click', (e) => {
                if (e.target === lightboxModal) {
                    closeLightbox();
                }
            });
        }

        // Keyboard ArrowLeft / ArrowRight & Escape navigation
        window.addEventListener('keydown', (e) => {
            if (!lightboxModal || lightboxModal.classList.contains('pointer-events-none')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') updateGalleryView(currentImageIndex - 1, 'prev');
            if (e.key === 'ArrowRight') updateGalleryView(currentImageIndex + 1, 'next');
        });

        // Lightbox filmstrip thumbnails click
        lightboxThumbs.forEach((tb) => {
            tb.addEventListener('click', (e) => {
                e.stopPropagation();
                const idx = parseInt(tb.dataset.lightboxIndex, 10);
                const dir = idx > currentImageIndex ? 'next' : 'prev';
                updateGalleryView(idx, dir);
            });
        });

        // Tabs functionality
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('[data-tab-content]');
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.dataset.tab;
                
                // Reset all buttons
                tabBtns.forEach(b => {
                    b.classList.remove('border-brand-500', 'text-brand-600', 'font-semibold');
                    b.classList.add('border-transparent', 'text-gray-500', 'font-medium');
                });
                // Activate clicked button
                btn.classList.remove('border-transparent', 'text-gray-500', 'font-medium');
                btn.classList.add('border-brand-500', 'text-brand-600', 'font-semibold');

                // Hide all contents
                tabContents.forEach(c => c.classList.add('hidden'));
                // Show target content
                document.querySelector(`[data-tab-content="${target}"]`)?.classList.remove('hidden');
            });
        });
    </script>
@endsection
