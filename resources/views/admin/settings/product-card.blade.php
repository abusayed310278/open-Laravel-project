@extends('layouts.admin')

@section('title', 'Settings — Product Card')

@section('content')
    @include('admin.settings._tabs')

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @if (isset($errors) && $errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @php
        $groups = [
            'category' => ['label' => 'Category', 'hint' => 'The small uppercase tag above the title.', 'colors' => [['category_color', 'Text color', $categoryColor]], 'size' => ['category_size', 9, 20, $categorySize]],
            'title' => ['label' => 'Title', 'hint' => 'Product name and its hover state.', 'colors' => [['title_color', 'Text color', $titleColor], ['title_hover_color', 'Hover color', $titleHoverColor]], 'size' => ['title_size', 11, 26, $titleSize]],
            'price' => ['label' => 'Price', 'hint' => 'The selling price.', 'colors' => [['price_color', 'Text color', $priceColor]], 'size' => ['price_size', 12, 28, $priceSize]],
            'old_price' => ['label' => 'Old price', 'hint' => 'The crossed-out compare-at price.', 'colors' => [['old_price_color', 'Text color', $oldPriceColor]], 'size' => ['old_price_size', 10, 22, $oldPriceSize]],
            'rating' => ['label' => 'Rating', 'hint' => 'Star icon, score and review count.', 'colors' => [['star_color', 'Star', $starColor], ['rating_color', 'Score', $ratingColor], ['rating_count_color', 'Count', $ratingCountColor]], 'size' => ['rating_size', 10, 20, $ratingSize]],
            'vendor' => ['label' => 'Vendor', 'hint' => 'The seller / store name.', 'colors' => [['vendor_color', 'Text color', $vendorColor]], 'size' => ['vendor_size', 10, 20, $vendorSize]],
            'button' => ['label' => 'Add button', 'hint' => 'Add-to-cart button, normal and hover.', 'colors' => [['btn_bg', 'Background', $btnBg], ['btn_text', 'Text & icon', $btnText], ['btn_hover_bg', 'Hover background', $btnHoverBg], ['btn_hover_text', 'Hover text & icon', $btnHoverText]], 'size' => ['btn_size', 10, 22, $btnSize]],
        ];
        $presetChips = [
            'default' => ['Default', ['#ea580c', '#090d16', '#656c19']],
            'olive-gold' => ['Olive & Gold', ['#ca8a04', '#15803d', '#656c19']],
            'emerald-modern' => ['Modern Emerald', ['#059669', '#0f172a', '#059669']],
            'dark-indigo' => ['Indigo Tech', ['#6366f1', '#4338ca', '#4f46e5']],
        ];
    @endphp

    <div class="@container">
        <div class="grid grid-cols-1 @4xl:grid-cols-[minmax(320px,400px)_minmax(0,1fr)] gap-6 items-start">

            {{-- ============ Stage: live preview ============ --}}
            <div class="@4xl:sticky @4xl:top-6 rounded-3xl border border-gray-200/90 bg-white shadow-xs overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-4">
                    <div class="flex items-center gap-2">
                        <span class="relative flex w-2.5 h-2.5">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                            <span class="relative inline-flex w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </span>
                        <h2 class="text-sm font-bold text-gray-900">Live preview</h2>
                    </div>
                    <span class="text-[11px] text-gray-400">Click any part to edit it</span>
                </div>

                <div class="m-4 rounded-2xl border border-gray-100 py-8 px-4 flex justify-center bg-gray-50" style="background-image: radial-gradient(circle, #d4d4d8 1px, transparent 1px); background-size: 16px 16px;">
                    <div
                        id="live-preview-card"
                        class="w-full max-w-[280px] bg-white border border-gray-100 rounded-2xl overflow-hidden flex flex-col text-left shadow-xl shadow-gray-900/5"
                    >
                        <div class="relative w-full h-52 shrink-0 overflow-hidden bg-white p-3 pt-6 flex items-center justify-center">
                            <span class="absolute top-2 left-2 z-10 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full leading-none">27.8%</span>
                            <img
                                src="https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&auto=format&fit=crop&q=80"
                                alt="Infinix Hot 70"
                                class="max-h-full max-w-full object-contain mix-blend-multiply"
                            />
                        </div>

                        <div class="p-3 sm:p-4 pt-2.5 flex flex-col grow">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span
                                    id="preview-category"
                                    data-group="category"
                                    class="pc-target uppercase tracking-wide transition-all cursor-pointer"
                                    style="color: {{ $categoryColor }}; font-size: {{ $categorySize }}px; font-weight: {{ in_array($categoryFormat, ['bold', 'bold_italic']) ? '700' : '400' }}; font-style: {{ in_array($categoryFormat, ['italic', 'bold_italic']) ? 'italic' : 'normal' }};"
                                >PHONES</span>
                                <span class="text-emerald-600 bg-white border border-emerald-200 text-[10px] font-semibold px-2 py-0.5 rounded-full leading-none">New</span>
                            </div>

                            <a
                                href="javascript:void(0)"
                                id="preview-title"
                                data-group="title"
                                class="pc-target font-bold leading-snug mb-1 transition-colors truncate block"
                                style="color: {{ $titleColor }}; font-size: {{ $titleSize }}px;"
                                data-normal-color="{{ $titleColor }}"
                                data-hover-color="{{ $titleHoverColor }}"
                                onmouseover="this.style.color = this.dataset.hoverColor"
                                onmouseout="this.style.color = this.dataset.normalColor"
                            >Infinix Hot 70</a>

                            <div class="flex items-baseline gap-2 mb-1.5">
                                <span
                                    id="preview-price"
                                    data-group="price"
                                    class="pc-target font-bold font-mono transition-all cursor-pointer"
                                    style="color: {{ $priceColor }}; font-size: {{ $priceSize }}px;"
                                >$649</span>
                                <span
                                    id="preview-old-price"
                                    data-group="old_price"
                                    class="pc-target line-through font-mono transition-all cursor-pointer"
                                    style="color: {{ $oldPriceColor }}; font-size: {{ $oldPriceSize }}px;"
                                >$899</span>
                            </div>

                            <div id="preview-rating-row" data-group="rating" class="pc-target flex items-center gap-1.5 mb-1 cursor-pointer w-fit">
                                <svg
                                    id="preview-star"
                                    class="fill-current transition-all"
                                    style="color: {{ $starColor }}; width: {{ $ratingSize * 1.15 }}px; height: {{ $ratingSize * 1.15 }}px;"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z" />
                                </svg>
                                <span id="preview-rating" class="font-semibold transition-all" style="color: {{ $ratingColor }}; font-size: {{ $ratingSize }}px;">4.9</span>
                                <span id="preview-rating-count" class="transition-all" style="color: {{ $ratingCountColor }}; font-size: {{ $ratingSize }}px;">(23)</span>
                                <span class="inline-flex text-blue-500 shrink-0 ml-0.5" title="Verified Badge">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                </span>
                            </div>

                            <div class="mt-auto pt-2 flex items-center justify-between gap-2">
                                <span
                                    id="preview-vendor"
                                    data-group="vendor"
                                    class="pc-target truncate transition-all cursor-pointer"
                                    style="color: {{ $vendorColor }}; font-size: {{ $vendorSize }}px;"
                                >NextGen Devices Co.</span>
                                <button
                                    type="button"
                                    id="preview-btn"
                                    data-group="button"
                                    class="pc-target shrink-0 inline-flex items-center gap-1 font-semibold px-3 py-1.5 rounded-lg transition-all active:scale-95 cursor-pointer shadow-2xs"
                                    style="background-color: {{ $btnBg }}; color: {{ $btnText }}; font-size: {{ $btnSize }}px;"
                                    data-normal-bg="{{ $btnBg }}"
                                    data-normal-text="{{ $btnText }}"
                                    data-hover-bg="{{ $btnHoverBg }}"
                                    data-hover-text="{{ $btnHoverText }}"
                                    onmouseover="this.style.backgroundColor = this.dataset.hoverBg; this.style.color = this.dataset.hoverText;"
                                    onmouseout="this.style.backgroundColor = this.dataset.normalBg; this.style.color = this.dataset.normalText;"
                                >
                                    <svg class="transition-all" style="width: {{ $btnSize * 1.15 }}px; height: {{ $btnSize * 1.15 }}px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Presets --}}
                <div class="px-5 pb-5">
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-2">Start from a preset</span>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($presetChips as $presetKey => [$presetLabel, $dots])
                            <button
                                type="button"
                                onclick="applyPreset('{{ $presetKey }}')"
                                class="flex items-center gap-2.5 px-3 py-2 rounded-xl border border-gray-200 bg-white hover:border-brand-500 hover:bg-brand-50/40 text-left transition-colors cursor-pointer"
                            >
                                <span class="flex -space-x-1.5 shrink-0">
                                    @foreach ($dots as $dot)
                                        <span class="w-4 h-4 rounded-full ring-2 ring-white" style="background-color: {{ $dot }};"></span>
                                    @endforeach
                                </span>
                                <span class="text-xs font-semibold text-gray-700 truncate">{{ $presetLabel }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============ Inspector ============ --}}
            <form id="productCardSettingsForm" method="POST" action="{{ route('admin.settings.product-card.update') }}" class="min-w-0 rounded-3xl border border-gray-200/90 bg-white shadow-xs overflow-hidden">
                @csrf

                <div class="px-5 pt-5 pb-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Customize card</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Choose an element, then adjust its colors and size.</p>

                    <div class="flex gap-1.5 mt-4 overflow-x-auto pb-1 -mx-1 px-1" role="tablist">
                        @foreach ($groups as $groupKey => $group)
                            <button
                                type="button"
                                role="tab"
                                data-group="{{ $groupKey }}"
                                onclick="selectGroup('{{ $groupKey }}')"
                                class="pc-tab shrink-0 px-3.5 py-1.5 rounded-full text-xs font-semibold border transition-colors cursor-pointer"
                            >{{ $group['label'] }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="p-5 min-h-[300px]">
                    @foreach ($groups as $groupKey => $group)
                        @php [$sizeKey, $sizeMin, $sizeMax, $sizeValue] = $group['size']; @endphp
                        <div class="pc-panel hidden space-y-6" data-group="{{ $groupKey }}" role="tabpanel">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900">{{ $group['label'] }}</h3>
                                <p class="text-xs text-gray-500">{{ $group['hint'] }}</p>
                            </div>

                            <div class="grid grid-cols-1 @xl:grid-cols-2 gap-3">
                                @foreach ($group['colors'] as [$key, $label, $value])
                                    <div class="p-3 rounded-2xl border border-gray-200 bg-gray-50/60">
                                        <span class="block text-xs font-semibold text-gray-700 mb-2">{{ $label }}</span>
                                        <div class="flex items-stretch gap-2">
                                            <input
                                                type="color"
                                                id="input_{{ $key }}"
                                                name="product_card_{{ $key }}"
                                                value="{{ $value }}"
                                                title="Pick a color"
                                                class="w-11 h-11 shrink-0 p-1 rounded-xl border border-gray-300 bg-white cursor-pointer"
                                                oninput="syncColor('{{ $key }}', this.value)"
                                            />
                                            <div class="flex items-center min-w-0 flex-1 h-11 bg-white border border-gray-300 rounded-xl focus-within:border-brand-500 focus-within:ring-2 focus-within:ring-brand-500/20">
                                                <span class="pl-3 text-sm font-mono font-semibold text-gray-400 select-none">#</span>
                                                <input
                                                    type="text"
                                                    id="text_{{ $key }}"
                                                    value="{{ ltrim($value, '#') }}"
                                                    maxlength="7"
                                                    spellcheck="false"
                                                    placeholder="000000"
                                                    class="min-w-0 flex-1 h-full pl-0.5 pr-3 text-sm font-mono font-semibold text-gray-900 uppercase bg-transparent border-0 focus:outline-none focus:ring-0"
                                                    oninput="syncColorText('{{ $key }}', this.value)"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50/60">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-[11px] font-semibold text-gray-500">Font size</span>
                                    <div class="flex items-center gap-1.5">
                                        <input
                                            type="number"
                                            id="number_{{ $sizeKey }}"
                                            name="product_card_{{ $sizeKey }}"
                                            min="{{ $sizeMin }}"
                                            max="{{ $sizeMax }}"
                                            value="{{ $sizeValue }}"
                                            class="w-14 text-sm font-mono font-semibold px-2 py-1 border border-gray-200 rounded-lg text-center bg-white focus:outline-none focus:border-brand-500"
                                            oninput="syncSize('{{ $sizeKey }}', this.value)"
                                        />
                                        <span id="label_{{ $sizeKey }}" class="text-xs font-mono text-gray-400">{{ $sizeValue }}px</span>
                                    </div>
                                </div>
                                <input
                                    type="range"
                                    id="range_{{ $sizeKey }}"
                                    min="{{ $sizeMin }}"
                                    max="{{ $sizeMax }}"
                                    value="{{ $sizeValue }}"
                                    class="w-full accent-brand-500"
                                    oninput="syncSize('{{ $sizeKey }}', this.value)"
                                />
                                <div class="flex justify-between text-[10px] font-mono text-gray-400 mt-1">
                                    <span>{{ $sizeMin }}px</span>
                                    <span>{{ $sizeMax }}px</span>
                                </div>
                            </div>

                            @if ($groupKey === 'category')
                                <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50/60">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <span class="block text-xs font-semibold text-gray-700">Text Style / Typography</span>
                                            <span class="text-[11px] text-gray-500">Choose whether category text is bold, italic, or normal.</span>
                                        </div>
                                        <span id="current_format_badge" class="text-[10px] font-bold text-brand-700 bg-brand-50 border border-brand-200 px-2 py-0.5 rounded-md uppercase">
                                            {{ str_replace('_', ' ', $categoryFormat) }}
                                        </span>
                                    </div>

                                    <input type="hidden" name="product_card_category_format" id="input_category_format" value="{{ $categoryFormat }}">

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-3">
                                        {{-- Normal --}}
                                        <button
                                            type="button"
                                            data-format="normal"
                                            onclick="setCategoryFormat('normal')"
                                            class="category-format-btn flex flex-col items-center justify-center p-3 rounded-xl border transition-all cursor-pointer {{ $categoryFormat === 'normal' ? 'border-brand-500 bg-white ring-2 ring-brand-500/20 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                        >
                                            <span class="text-base font-normal text-gray-800">Abc</span>
                                            <span class="text-xs font-medium text-gray-700 mt-1">Normal</span>
                                            <span class="text-[10px] text-gray-400">Regular 400</span>
                                        </button>

                                        {{-- Bold --}}
                                        <button
                                            type="button"
                                            data-format="bold"
                                            onclick="setCategoryFormat('bold')"
                                            class="category-format-btn flex flex-col items-center justify-center p-3 rounded-xl border transition-all cursor-pointer {{ $categoryFormat === 'bold' ? 'border-brand-500 bg-white ring-2 ring-brand-500/20 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                        >
                                            <span class="text-base font-bold text-gray-900">Abc</span>
                                            <span class="text-xs font-bold text-gray-900 mt-1">Bold</span>
                                            <span class="text-[10px] text-gray-400">Weight 700</span>
                                        </button>

                                        {{-- Italic --}}
                                        <button
                                            type="button"
                                            data-format="italic"
                                            onclick="setCategoryFormat('italic')"
                                            class="category-format-btn flex flex-col items-center justify-center p-3 rounded-xl border transition-all cursor-pointer {{ $categoryFormat === 'italic' ? 'border-brand-500 bg-white ring-2 ring-brand-500/20 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                        >
                                            <span class="text-base italic font-normal text-gray-800">Abc</span>
                                            <span class="text-xs italic font-medium text-gray-700 mt-1">Italic</span>
                                            <span class="text-[10px] text-gray-400">Slanted Style</span>
                                        </button>

                                        {{-- Bold Italic --}}
                                        <button
                                            type="button"
                                            data-format="bold_italic"
                                            onclick="setCategoryFormat('bold_italic')"
                                            class="category-format-btn flex flex-col items-center justify-center p-3 rounded-xl border transition-all cursor-pointer {{ $categoryFormat === 'bold_italic' ? 'border-brand-500 bg-white ring-2 ring-brand-500/20 shadow-xs' : 'border-gray-200 bg-white hover:border-gray-300' }}"
                                        >
                                            <span class="text-base italic font-bold text-gray-900">Abc</span>
                                            <span class="text-xs italic font-bold text-gray-900 mt-1">Bold Italic</span>
                                            <span class="text-[10px] text-gray-400">700 & Slanted</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-gray-50/80 border-t border-gray-100">
                    <button
                        type="submit"
                        formaction="{{ route('admin.settings.product-card.reset') }}"
                        onclick="return confirm('Reset all product card styling to defaults?');"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 border border-gray-200 bg-white transition-colors cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset to defaults</span>
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold bg-brand-600 hover:bg-brand-700 text-white shadow-sm transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .pc-tab { background: #fff; border-color: #e5e7eb; color: #4b5563; }
        .pc-tab:hover { border-color: var(--color-brand-500); color: var(--color-brand-700); }
        .pc-tab.is-active { background: var(--color-brand-600); border-color: var(--color-brand-600); color: #fff; }
        .pc-target { border-radius: 6px; transition: box-shadow .15s; }
        .pc-target:hover { box-shadow: 0 0 0 2px rgb(0 0 0 / .08); }
        .pc-target.is-active { box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--color-brand-500); }
    </style>

    <script>
        function selectGroup(group) {
            document.querySelectorAll('.pc-panel').forEach(p => p.classList.toggle('hidden', p.dataset.group !== group));
            document.querySelectorAll('.pc-tab').forEach(t => t.classList.toggle('is-active', t.dataset.group === group));
            document.querySelectorAll('.pc-target').forEach(t => t.classList.toggle('is-active', t.dataset.group === group));
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.pc-target').forEach(el => {
                el.addEventListener('click', e => {
                    e.preventDefault();
                    selectGroup(el.dataset.group);
                });
            });
            selectGroup('category');
        });
    </script>

    <script>
        // Sync color picker -> text input & preview
        function syncColor(key, hex) {
            const textInput = document.getElementById('text_' + key);
            if (textInput) textInput.value = hex.replace('#', '');
            updateLivePreview();
        }

        // Sync text input -> color picker & preview
        function syncColorText(key, text) {
            const hex = text.replace(/[^0-9A-Fa-f]/g, '').slice(0, 6);
            if (hex.length === 6) {
                const picker = document.getElementById('input_' + key);
                if (picker) picker.value = '#' + hex;
                updateLivePreview();
            }
        }

        // Sync range & number inputs & preview
        function syncSize(key, val) {
            const range = document.getElementById('range_' + key);
            const number = document.getElementById('number_' + key);
            const label = document.getElementById('label_' + key);
            if (range) range.value = val;
            if (number) number.value = val;
            if (label) label.textContent = val + 'px';
            updateLivePreview();
        }

        function setCategoryFormat(format) {
            const hiddenInput = document.getElementById('input_category_format');
            if (hiddenInput) {
                hiddenInput.value = format;
            }

            const badge = document.getElementById('current_format_badge');
            if (badge) {
                badge.textContent = format.replace('_', ' ');
            }

            document.querySelectorAll('.category-format-btn').forEach(btn => {
                const isActive = btn.dataset.format === format;
                btn.classList.toggle('border-brand-500', isActive);
                btn.classList.toggle('ring-2', isActive);
                btn.classList.toggle('ring-brand-500/20', isActive);
                btn.classList.toggle('shadow-xs', isActive);
                btn.classList.toggle('border-gray-200', !isActive);
            });

            updateLivePreview();
        }

        // Live preview updates
        function updateLivePreview() {
            // Category
            const catColor = document.getElementById('input_category_color')?.value || '#ea580c';
            const catSize = document.getElementById('range_category_size')?.value || '11';
            const catFormat = document.getElementById('input_category_format')?.value || 'bold';
            const prevCat = document.getElementById('preview-category');
            if (prevCat) {
                prevCat.style.color = catColor;
                prevCat.style.fontSize = catSize + 'px';
                prevCat.style.fontWeight = (catFormat === 'bold' || catFormat === 'bold_italic') ? '700' : '400';
                prevCat.style.fontStyle = (catFormat === 'italic' || catFormat === 'bold_italic') ? 'italic' : 'normal';
            }

            // Title
            const titleColor = document.getElementById('input_title_color')?.value || '#090d16';
            const titleHover = document.getElementById('input_title_hover_color')?.value || '#ea580c';
            const titleSize = document.getElementById('range_title_size')?.value || '14';
            const prevTitle = document.getElementById('preview-title');
            if (prevTitle) {
                prevTitle.dataset.normalColor = titleColor;
                prevTitle.dataset.hoverColor = titleHover;
                prevTitle.style.color = titleColor;
                prevTitle.style.fontSize = titleSize + 'px';
            }

            // Price
            const priceColor = document.getElementById('input_price_color')?.value || '#090d16';
            const priceSize = document.getElementById('range_price_size')?.value || '16';
            const prevPrice = document.getElementById('preview-price');
            if (prevPrice) {
                prevPrice.style.color = priceColor;
                prevPrice.style.fontSize = priceSize + 'px';
            }

            // Old Price
            const oldPriceColor = document.getElementById('input_old_price_color')?.value || '#94a3b8';
            const oldPriceSize = document.getElementById('range_old_price_size')?.value || '12';
            const prevOldPrice = document.getElementById('preview-old-price');
            if (prevOldPrice) {
                prevOldPrice.style.color = oldPriceColor;
                prevOldPrice.style.fontSize = oldPriceSize + 'px';
            }

            // Star & Rating
            const starColor = document.getElementById('input_star_color')?.value || '#fbbf24';
            const ratingColor = document.getElementById('input_rating_color')?.value || '#1e293b';
            const ratingCountColor = document.getElementById('input_rating_count_color')?.value || '#94a3b8';
            const ratingSize = parseFloat(document.getElementById('range_rating_size')?.value || '12');

            const prevStar = document.getElementById('preview-star');
            if (prevStar) {
                prevStar.style.color = starColor;
                prevStar.style.width = (ratingSize * 1.15) + 'px';
                prevStar.style.height = (ratingSize * 1.15) + 'px';
            }
            const prevRating = document.getElementById('preview-rating');
            if (prevRating) {
                prevRating.style.color = ratingColor;
                prevRating.style.fontSize = ratingSize + 'px';
            }
            const prevRatingCount = document.getElementById('preview-rating-count');
            if (prevRatingCount) {
                prevRatingCount.style.color = ratingCountColor;
                prevRatingCount.style.fontSize = ratingSize + 'px';
            }

            // Vendor
            const vendorColor = document.getElementById('input_vendor_color')?.value || '#64748b';
            const vendorSize = document.getElementById('range_vendor_size')?.value || '12';
            const prevVendor = document.getElementById('preview-vendor');
            if (prevVendor) {
                prevVendor.style.color = vendorColor;
                prevVendor.style.fontSize = vendorSize + 'px';
            }

            // Add button
            const btnBg = document.getElementById('input_btn_bg')?.value || '#656c19';
            const btnText = document.getElementById('input_btn_text')?.value || '#ffffff';
            const btnHoverBg = document.getElementById('input_btn_hover_bg')?.value || '#fff200';
            const btnHoverText = document.getElementById('input_btn_hover_text')?.value || '#000000';
            const btnSize = parseFloat(document.getElementById('range_btn_size')?.value || '12');

            const prevBtn = document.getElementById('preview-btn');
            if (prevBtn) {
                prevBtn.dataset.normalBg = btnBg;
                prevBtn.dataset.normalText = btnText;
                prevBtn.dataset.hoverBg = btnHoverBg;
                prevBtn.dataset.hoverText = btnHoverText;
                prevBtn.style.backgroundColor = btnBg;
                prevBtn.style.color = btnText;
                prevBtn.style.fontSize = btnSize + 'px';

                const btnSvg = prevBtn.querySelector('svg');
                if (btnSvg) {
                    btnSvg.style.width = (btnSize * 1.15) + 'px';
                    btnSvg.style.height = (btnSize * 1.15) + 'px';
                }
            }
        }

        // Preset data
        const presets = {
            'default': {
                category_color: '#ea580c', category_size: 11, category_format: 'bold',
                title_color: '#090d16', title_hover_color: '#ea580c', title_size: 14,
                price_color: '#090d16', price_size: 16,
                old_price_color: '#94a3b8', old_price_size: 12,
                star_color: '#fbbf24', rating_color: '#1e293b', rating_count_color: '#94a3b8', rating_size: 12,
                vendor_color: '#64748b', vendor_size: 12,
                btn_bg: '#656c19', btn_text: '#ffffff', btn_hover_bg: '#fff200', btn_hover_text: '#000000', btn_size: 12
            },
            'olive-gold': {
                category_color: '#ca8a04', category_size: 11, category_format: 'bold',
                title_color: '#1c1917', title_hover_color: '#ca8a04', title_size: 15,
                price_color: '#15803d', price_size: 17,
                old_price_color: '#a8a29e', old_price_size: 12,
                star_color: '#eab308', rating_color: '#292524', rating_count_color: '#78716c', rating_size: 12,
                vendor_color: '#78716c', vendor_size: 12,
                btn_bg: '#656c19', btn_text: '#ffffff', btn_hover_bg: '#ca8a04', btn_hover_text: '#ffffff', btn_size: 13
            },
            'emerald-modern': {
                category_color: '#059669', category_size: 11, category_format: 'bold',
                title_color: '#0f172a', title_hover_color: '#059669', title_size: 14,
                price_color: '#059669', price_size: 17,
                old_price_color: '#94a3b8', old_price_size: 12,
                star_color: '#f59e0b', rating_color: '#334155', rating_count_color: '#94a3b8', rating_size: 12,
                vendor_color: '#64748b', vendor_size: 12,
                btn_bg: '#059669', btn_text: '#ffffff', btn_hover_bg: '#047857', btn_hover_text: '#ffffff', btn_size: 12
            },
            'dark-indigo': {
                category_color: '#6366f1', category_size: 11, category_format: 'bold',
                title_color: '#1e1b4b', title_hover_color: '#4f46e5', title_size: 14,
                price_color: '#4338ca', price_size: 16,
                old_price_color: '#94a3b8', old_price_size: 12,
                star_color: '#fbbf24', rating_color: '#1e293b', rating_count_color: '#94a3b8', rating_size: 12,
                vendor_color: '#475569', vendor_size: 12,
                btn_bg: '#4f46e5', btn_text: '#ffffff', btn_hover_bg: '#4338ca', btn_hover_text: '#ffffff', btn_size: 12
            }
        };

        function applyPreset(name) {
            const p = presets[name];
            if (!p) return;

            if (p.category_format) {
                setCategoryFormat(p.category_format);
            }

            Object.keys(p).forEach(k => {
                if (k.endsWith('_color')) {
                    const picker = document.getElementById('input_' + k);
                    const txt = document.getElementById('text_' + k);
                    if (picker) picker.value = p[k];
                    if (txt) txt.value = p[k].replace('#', '');
                } else if (k.endsWith('_size')) {
                    const range = document.getElementById('range_' + k);
                    const num = document.getElementById('number_' + k);
                    const lbl = document.getElementById('label_' + k);
                    if (range) range.value = p[k];
                    if (num) num.value = p[k];
                    if (lbl) lbl.textContent = p[k] + 'px';
                } else if (k.startsWith('btn_')) {
                    const picker = document.getElementById('input_' + k);
                    const txt = document.getElementById('text_' + k);
                    if (picker) picker.value = p[k];
                    if (txt) txt.value = p[k].replace('#', '');
                }
            });

            updateLivePreview();
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', updateLivePreview);
    </script>
@endsection
