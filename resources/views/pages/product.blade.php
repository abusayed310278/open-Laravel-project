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
                <div class="bg-gray-50 rounded-xl aspect-square flex items-center justify-center overflow-hidden">
                    @if ($product->images->isNotEmpty())
                        <img id="main-img" src="{{ $product->primaryImage()->url() }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                        </svg>
                    @endif
                </div>

                @if ($product->images->count() > 1)
                    <div class="grid grid-cols-4 gap-3 mt-3">
                        @foreach ($product->images as $image)
                            <img src="{{ $image->url() }}" class="thumb-img aspect-square w-full object-cover rounded-lg cursor-pointer border-2 {{ $image->is_primary ? 'border-brand-500' : 'border-transparent' }}">
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="lg:col-span-3">
                <div class="flex items-center gap-2 mb-3">
                    @if ($product->isVerified())
                        <x-grade-badge :grade="$product->grade->value" />
                        <x-verified-badge />
                    @else
                        <x-badge color="blue">{{ $product->condition->label() }}</x-badge>
                    @endif
                    @if ($product->isWarehoused())
                        <x-verified-badge warehouse />
                    @endif
                    @if ($product->is_negotiable)
                        <x-badge color="gray">Negotiable</x-badge>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ $product->title }}</h1>

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
                        <x-badge color="red">
                            Save {{ number_format((1 - $product->price / $product->compare_price) * 100) }}%
                        </x-badge>
                    @endif
                </div>

                {{-- Seller card --}}
                <div class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-4 mb-6">
                    <a href="{{ $sellerUrl ?? '#' }}" class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 font-bold flex items-center justify-center flex-shrink-0">
                            {{ Str::upper(Str::substr($sellerName, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm font-semibold text-gray-900 truncate">{{ $sellerName }}</span>
                                @if ($product->user->status->value === 'active')
                                    <svg class="w-4 h-4 text-brand-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400">
                                @if ($sellerReviewCount > 0)
                                    ★ {{ number_format($sellerRating, 1) }}
                                @endif
                                @if ($sellerLocation)
                                    · {{ $sellerLocation }}
                                @endif
                            </p>
                        </div>
                    </a>

                    @auth
                        @if (auth()->id() !== $product->user_id)
                            <form method="POST" action="{{ route('chat.start', $product) }}">
                                @csrf
                                <x-button type="submit" variant="secondary" size="sm">Contact</x-button>
                            </form>
                        @endif
                    @endauth
                </div>

                {{-- Purchase actions --}}
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
                <button type="button" data-tab="specs" class="tab-btn pb-3 text-sm sm:text-base font-semibold border-b-2 border-brand-500 text-brand-600 transition-colors duration-150 cursor-pointer -mb-px">Specification</button>
                <button type="button" data-tab="description" class="tab-btn pb-3 text-sm sm:text-base font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors duration-150 cursor-pointer -mb-px">Description</button>
                <button type="button" data-tab="reviews" class="tab-btn pb-3 text-sm sm:text-base font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300 transition-colors duration-150 cursor-pointer -mb-px">Reviews ({{ $reviewCount }})</button>
            </div>

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
        </div>

        {{-- Similar products --}}
        @if ($related->isNotEmpty())
            <div class="mt-12">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Similar Products</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($related as $item)
                        <x-product-card
                            :title="$item->title"
                            :price="$item->price"
                            :compare-price="$item->compare_price"
                            :grade="$item->isVerified() ? $item->grade->value : null"
                            :condition="$item->condition->value"
                            :image="$item->images->first()?->url()"
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
                            :title="$item->title"
                            :price="$item->price"
                            :compare-price="$item->compare_price"
                            :grade="$item->isVerified() ? $item->grade->value : null"
                            :condition="$item->condition->value"
                            :image="$item->images->first()?->url()"
                            :href="route('products.show', $item)"
                        />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        document.querySelectorAll('[data-qty]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('input[name="quantity"]');
                const value = parseInt(input.value, 10) || 1;
                input.value = btn.dataset.qty === 'increment' ? value + 1 : Math.max(1, value - 1);
            });
        });

        // Gallery functionality
        const mainImg = document.getElementById('main-img');
        const thumbs = document.querySelectorAll('.thumb-img');
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', () => {
                if (mainImg) mainImg.src = thumb.src;
                thumbs.forEach(t => {
                    t.classList.remove('border-brand-500');
                    t.classList.add('border-transparent');
                });
                thumb.classList.remove('border-transparent');
                thumb.classList.add('border-brand-500');
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
