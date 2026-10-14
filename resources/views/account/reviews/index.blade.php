@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">My Reviews</h1>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        @if (isset($errors) && $errors->any())
            <x-alert type="danger">{{ $errors->first() }}</x-alert>
        @endif

        {{-- Write a Review Card with Dropdown --}}
        <x-card title="Write a Review">
            @if (!empty($eligibleReviewables))
                <form method="POST" action="{{ route('account.reviews.store-direct') }}" class="space-y-5" id="write-review-form">
                    @csrf
                    <input type="hidden" name="order_id" id="form_order_id">
                    <input type="hidden" name="reviewable_type" id="form_reviewable_type">
                    <input type="hidden" name="reviewable_id" id="form_reviewable_id">
                    <input type="hidden" name="rating" id="form_rating" value="5">

                    {{-- Product / Item Selection Dropdown --}}
                    <div>
                        <label for="review_target_select" class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Select Item or Product to Review <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="review_target_select"
                            required
                            class="w-full border border-gray-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all cursor-pointer"
                        >
                            <option value="" disabled selected>-- Choose a purchased item from your orders --</option>
                            @php
                                $grouped = collect($eligibleReviewables)->groupBy('order_number');
                            @endphp
                            @foreach ($grouped as $orderNum => $items)
                                <optgroup label="Order #{{ $orderNum }}">
                                    @foreach ($items as $item)
                                        <option
                                            value="{{ $item['order_id'] }}|{{ $item['type']->value }}|{{ $item['id'] }}"
                                            data-title="{{ $item['label'] }}"
                                            data-subtext="{{ $item['subtext'] }}"
                                            data-image="{{ $item['image'] ?? '' }}"
                                            data-type="{{ $item['type']->label() }}"
                                        >
                                            {{ $item['label'] }} ({{ $item['type']->label() }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dynamic Selected Item Preview Banner --}}
                    <div id="selected_item_preview" class="hidden items-center gap-3 p-3 bg-gray-50 dark:bg-slate-800/80 border border-gray-100 dark:border-slate-700 rounded-xl">
                        <div id="preview_image_container" class="w-12 h-12 rounded-lg bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 flex items-center justify-center p-1 shrink-0 overflow-hidden">
                            <img id="preview_image" src="" alt="" class="max-w-full max-h-full object-contain">
                            <svg id="preview_fallback_icon" class="w-6 h-6 text-gray-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span id="preview_type" class="text-[10px] font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">Product</span>
                            <p id="preview_title" class="text-sm font-semibold text-gray-900 dark:text-white truncate"></p>
                            <p id="preview_subtext" class="text-xs text-gray-400"></p>
                        </div>
                    </div>

                    {{-- Interactive Star Rating --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200 mb-1.5">
                            Overall Rating <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-1.5" id="star-rating-container">
                            @for ($s = 1; $s <= 5; $s++)
                                <button
                                    type="button"
                                    data-rating="{{ $s }}"
                                    class="js-star-btn p-1 text-amber-400 hover:scale-110 active:scale-95 transition-transform cursor-pointer"
                                    title="{{ $s }} Star{{ $s > 1 ? 's' : '' }}"
                                >
                                    <svg class="w-6 h-6 js-star-icon fill-current" viewBox="0 0 20 20">
                                        <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
                                    </svg>
                                </button>
                            @endfor
                            <span id="rating-label" class="text-xs font-semibold text-gray-600 dark:text-gray-300 ml-2">5 Stars (Excellent)</span>
                        </div>
                    </div>

                    {{-- Review Title / Headline --}}
                    <div>
                        <x-input
                            label="Headline (optional)"
                            name="title"
                            type="text"
                            placeholder="e.g. Fantastic quality, exceeded my expectations!"
                        />
                    </div>

                    {{-- Review Body --}}
                    <div>
                        <x-textarea
                            label="Your Review"
                            name="body"
                            rows="4"
                            placeholder="What did you like or dislike about this product? How was the performance and condition?"
                            required
                        />
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-center justify-end">
                        <x-button type="submit" size="md" class="px-6 py-2.5 rounded-xl font-semibold shadow-xs">
                            Submit Review
                        </x-button>
                    </div>
                </form>
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You have already reviewed all items from your previous orders, or haven't made a purchase yet.
                    </p>
                    <div class="mt-4">
                        <x-button as="a" :href="route('shop')">Browse Products</x-button>
                    </div>
                </div>
            @endif
        </x-card>

        {{-- Past Reviews Card --}}
        <x-card title="My Submitted Reviews">
            @forelse ($reviews as $review)
                <div class="py-4 border-b border-gray-100 dark:border-slate-800 last:border-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <x-star-rating :rating="$review->rating" />
                            @php($target = $review->reviewable())
                            @if ($target)
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    for <span class="text-gray-900 dark:text-white font-medium">{{ $target->title ?? $target->name ?? 'Item' }}</span>
                                </span>
                            @endif
                        </div>
                        <x-badge :color="$review->status->badgeColor()">{{ $review->status->label() }}</x-badge>
                    </div>
                    @if ($review->title)
                        <p class="font-bold text-gray-900 dark:text-white mt-1">{{ $review->title }}</p>
                    @endif
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">{{ $review->body }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Submitted {{ $review->created_at->format('M j, Y') }} · Order #{{ $review->order?->order_number }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-8">You haven't written any reviews yet.</p>
            @endforelse

            <x-pagination :paginator="$reviews" />
        </x-card>
    </div>

    {{-- Interactive Review Form Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectEl = document.getElementById('review_target_select');
            const orderIdInput = document.getElementById('form_order_id');
            const typeInput = document.getElementById('form_reviewable_type');
            const idInput = document.getElementById('form_reviewable_id');
            const ratingInput = document.getElementById('form_rating');
            const ratingLabel = document.getElementById('rating-label');

            const previewBanner = document.getElementById('selected_item_preview');
            const previewTitle = document.getElementById('preview_title');
            const previewSubtext = document.getElementById('preview_subtext');
            const previewType = document.getElementById('preview_type');
            const previewImage = document.getElementById('preview_image');
            const previewFallbackIcon = document.getElementById('preview_fallback_icon');

            if (selectEl) {
                selectEl.addEventListener('change', () => {
                    const selected = selectEl.options[selectEl.selectedIndex];
                    if (!selected || !selected.value) return;

                    const [orderId, type, id] = selected.value.split('|');
                    orderIdInput.value = orderId;
                    typeInput.value = type;
                    idInput.value = id;

                    // Update Preview
                    const title = selected.dataset.title || '';
                    const subtext = selected.dataset.subtext || '';
                    const typeLabel = selected.dataset.type || '';
                    const imgUrl = selected.dataset.image || '';

                    previewTitle.textContent = title;
                    previewSubtext.textContent = subtext;
                    previewType.textContent = typeLabel;

                    if (imgUrl) {
                        previewImage.src = imgUrl;
                        previewImage.classList.remove('hidden');
                        previewFallbackIcon.classList.add('hidden');
                    } else {
                        previewImage.classList.add('hidden');
                        previewFallbackIcon.classList.remove('hidden');
                    }

                    previewBanner.classList.remove('hidden');
                    previewBanner.classList.add('flex');
                });
            }

            // Star Rating Logic
            const starButtons = document.querySelectorAll('.js-star-btn');
            const ratingLabels = {
                1: '1 Star (Poor)',
                2: '2 Stars (Fair)',
                3: '3 Stars (Good)',
                4: '4 Stars (Very Good)',
                5: '5 Stars (Excellent)'
            };

            const setRating = (val) => {
                ratingInput.value = val;
                if (ratingLabel) {
                    ratingLabel.textContent = ratingLabels[val] || `${val} Stars`;
                }
                starButtons.forEach(btn => {
                    const starVal = parseInt(btn.dataset.rating, 10);
                    const icon = btn.querySelector('.js-star-icon');
                    if (starVal <= val) {
                        icon.classList.remove('text-gray-300', 'dark:text-gray-600');
                        icon.classList.add('text-amber-400');
                    } else {
                        icon.classList.remove('text-amber-400');
                        icon.classList.add('text-gray-300', 'dark:text-gray-600');
                    }
                });
            };

            starButtons.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    setRating(parseInt(btn.dataset.rating, 10));
                });
            });

            // Form validation before submit
            const form = document.getElementById('write-review-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    if (!orderIdInput.value || !typeInput.value || !idInput.value) {
                        e.preventDefault();
                        alert('Please select an item to review from the dropdown.');
                        selectEl.focus();
                    }
                });
            }
        });
    </script>
@endsection
