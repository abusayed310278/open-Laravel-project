@extends('layouts.admin')

@section('title', 'Settings — Promo Cards')

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
        $cardLabels = [1 => 'Card 1', 2 => 'Card 2', 3 => 'Card 3'];
        $autoHints = [1 => 'newest live product in Laptops', 2 => 'newest live product in Smartphones', 3 => 'newest live product with a photo'];
        $defaultPresets = [
            1 => [
                ['name' => 'Default (Brown)', 'start' => '#ad7f67', 'end' => '#e8dedc', 'title' => '#030712', 'text' => '#1f2937', 'btn_bg' => '#fef08a', 'btn_text' => '#000000'],
                ['name' => 'Sunset Amber', 'start' => '#f97316', 'end' => '#fed7aa', 'title' => '#431407', 'text' => '#7c2d12', 'btn_bg' => '#ffffff', 'btn_text' => '#431407'],
                ['name' => 'Rose Gold', 'start' => '#f43f5e', 'end' => '#fecdd3', 'title' => '#4c0519', 'text' => '#881337', 'btn_bg' => '#ffffff', 'btn_text' => '#4c0519'],
                ['name' => 'Dark Onyx', 'start' => '#0f172a', 'end' => '#334155', 'title' => '#ffffff', 'text' => '#cbd5e1', 'btn_bg' => '#fef08a', 'btn_text' => '#000000'],
            ],
            2 => [
                ['name' => 'Default (Light)', 'start' => '#ebeef3', 'end' => '#ccd6e2', 'title' => '#030712', 'text' => '#1f2937', 'btn_bg' => '#fef08a', 'btn_text' => '#000000'],
                ['name' => 'Clean Slate', 'start' => '#f1f5f9', 'end' => '#e2e8f0', 'title' => '#0f172a', 'text' => '#334155', 'btn_bg' => '#0f172a', 'btn_text' => '#ffffff'],
                ['name' => 'Soft Mint', 'start' => '#ecfdf5', 'end' => '#a7f3d0', 'title' => '#064e3b', 'text' => '#065f46', 'btn_bg' => '#064e3b', 'btn_text' => '#ffffff'],
                ['name' => 'Lavender', 'start' => '#f5f3ff', 'end' => '#ddd6fe', 'title' => '#3b0764', 'text' => '#581c87', 'btn_bg' => '#4c1d95', 'btn_text' => '#ffffff'],
            ],
            3 => [
                ['name' => 'Default (Dark)', 'start' => '#6283c4', 'end' => '#c1d3f7', 'title' => '#030712', 'text' => '#1f2937', 'btn_bg' => '#fef08a', 'btn_text' => '#000000'],
                ['name' => 'Midnight Navy', 'start' => '#0a192f', 'end' => '#1e3a8a', 'title' => '#ffffff', 'text' => '#93c5fd', 'btn_bg' => '#38bdf8', 'btn_text' => '#0a192f'],
                ['name' => 'Emerald Forest', 'start' => '#064e3b', 'end' => '#059669', 'title' => '#ffffff', 'text' => '#a7f3d0', 'btn_bg' => '#fef08a', 'btn_text' => '#064e3b'],
                ['name' => 'Royal Purple', 'start' => '#312e81', 'end' => '#7c3aed', 'title' => '#ffffff', 'text' => '#ddd6fe', 'btn_bg' => '#fef08a', 'btn_text' => '#312e81'],
            ],
        ];
    @endphp

    <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Homepage Promo Cards</h2>
                <p class="text-xs text-gray-500 mt-0.5">Customize the three cards under "Why Buy" with text, live product links, gradient backgrounds, text colors, and action buttons.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg border border-brand-200 transition-colors w-fit">
                <span>View Live Homepage</span>
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>

        <form method="POST" action="{{ route('admin.settings.promo.update') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @foreach ($cards as $n => $card)
                    @php
                        $curBgStart = old("cards.$n.bg_color_start", $card['bg_color_start'] ?? ($n === 1 ? '#ad7f67' : ($n === 2 ? '#ebeef3' : '#6283c4')));
                        $curBgEnd = old("cards.$n.bg_color_end", $card['bg_color_end'] ?? ($n === 1 ? '#e8dedc' : ($n === 2 ? '#ccd6e2' : '#c1d3f7')));
                        $curTitleColor = old("cards.$n.title_color", $card['title_color'] ?? '#030712');
                        $curTextColor = old("cards.$n.text_color", $card['text_color'] ?? '#1f2937');
                        $curBtnBg = old("cards.$n.button_bg_color", $card['button_bg_color'] ?? '#fef08a');
                        $curBtnText = old("cards.$n.button_text_color", $card['button_text_color'] ?? '#000000');
                    @endphp

                    <div class="border border-gray-200/80 rounded-xl p-4.5 space-y-4 bg-gray-50/40">
                        {{-- Card Header & Badge --}}
                        <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span id="header-pill-{{ $n }}" class="w-3.5 h-3.5 rounded-full shadow-2xs border border-white" style="background: linear-gradient(135deg, {{ $curBgStart }} 0%, {{ $curBgEnd }} 100%);"></span>
                                <span class="text-xs font-bold text-gray-800 uppercase tracking-wider">{{ $cardLabels[$n] }}</span>
                            </div>
                            <span class="text-[11px] font-medium text-gray-400">Position {{ $n }} of 3</span>
                        </div>

                        {{-- Real-time Interactive Live Preview Box --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Live Preview</span>
                                <span class="text-[10px] text-gray-400">Updates instantly</span>
                            </div>
                            <div
                                id="preview-card-{{ $n }}"
                                class="relative h-32 rounded-xl p-3.5 flex flex-col justify-between overflow-hidden shadow-xs transition-all duration-200"
                                style="background: linear-gradient(135deg, {{ $curBgStart }} 0%, {{ $curBgEnd }} 100%);"
                            >
                                <div class="relative z-10 max-w-[65%]">
                                    <h4 id="preview-title-{{ $n }}" class="text-sm font-extrabold leading-tight line-clamp-2" style="color: {{ $curTitleColor }};">
                                        {!! nl2br(e(old("cards.$n.title", $card['title']))) !!}
                                    </h4>
                                    <p id="preview-text-{{ $n }}" class="text-[11px] mt-1 leading-snug line-clamp-2" style="color: {{ $curTextColor }};">
                                        {!! nl2br(e(old("cards.$n.text", $card['text']))) !!}
                                    </p>
                                </div>
                                <div class="relative z-10 pt-1">
                                    <span
                                        id="preview-btn-{{ $n }}"
                                        class="inline-flex items-center gap-1 text-[10px] font-bold px-2.5 py-1 rounded-md shadow-2xs transition-colors"
                                        style="background-color: {{ $curBtnBg }}; color: {{ $curBtnText }};"
                                    >
                                        <span id="preview-btn-text-{{ $n }}">{{ old("cards.$n.button", $card['button']) }}</span>
                                        <span>→</span>
                                    </span>
                                </div>

                                {{-- Decorative mock device graphic --}}
                                <div class="absolute right-2 -bottom-2 w-20 h-28 opacity-75 pointer-events-none flex items-center justify-center">
                                    <div class="w-16 h-20 rounded-lg bg-black/10 backdrop-blur-xs border border-white/20 flex items-center justify-center shadow-xs">
                                        <svg class="w-8 h-8 text-black/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Content Fields --}}
                        <div class="space-y-3 bg-white p-3 rounded-lg border border-gray-100 shadow-2xs">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Title</label>
                                <textarea
                                    name="cards[{{ $n }}][title]"
                                    id="input-title-{{ $n }}"
                                    rows="2"
                                    maxlength="120"
                                    required
                                    oninput="syncCardPromo({{ $n }})"
                                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                >{{ old("cards.$n.title", $card['title']) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">{{ $n === 2 ? 'Subtitle' : 'Description' }}</label>
                                <textarea
                                    name="cards[{{ $n }}][text]"
                                    id="input-text-{{ $n }}"
                                    rows="2"
                                    maxlength="200"
                                    required
                                    oninput="syncCardPromo({{ $n }})"
                                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                >{{ old("cards.$n.text", $card['text']) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Button label</label>
                                <input
                                    type="text"
                                    name="cards[{{ $n }}][button]"
                                    id="input-button-{{ $n }}"
                                    value="{{ old("cards.$n.button", $card['button']) }}"
                                    maxlength="40"
                                    required
                                    oninput="syncCardPromo({{ $n }})"
                                    class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Target Product</label>
                                <select name="cards[{{ $n }}][product_id]" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white">
                                    <option value="">Auto — {{ $autoHints[$n] }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected((string) old("cards.$n.product_id", $card['product_id']) === (string) $product->id)>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Color Customization Panel --}}
                        <div class="space-y-3 bg-white p-3.5 rounded-lg border border-gray-200/90 shadow-2xs">
                            <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                                <span class="text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                    Color Palette & Styling
                                </span>
                            </div>

                            {{-- 1. Card Gradient Background --}}
                            <div class="space-y-2">
                                <span class="block text-[11px] font-semibold text-gray-600 uppercase tracking-wider">Card Background Gradient</span>

                                <div class="grid grid-cols-2 gap-2">
                                    {{-- Gradient Start --}}
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-500 mb-1">Start Color</label>
                                        <div class="flex items-center gap-1.5">
                                            <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="wheel-bg-start-{{ $n }}"
                                                    value="{{ $curBgStart }}"
                                                    oninput="syncWheelToInput(this, 'input-bg-start-{{ $n }}'); syncCardPromo({{ $n }});"
                                                    class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                                <input
                                                    type="text"
                                                    name="cards[{{ $n }}][bg_color_start]"
                                                    id="input-bg-start-{{ $n }}"
                                                    value="{{ ltrim($curBgStart, '#') }}"
                                                    oninput="syncInputToWheel(this, 'wheel-bg-start-{{ $n }}'); syncCardPromo({{ $n }});"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Gradient End --}}
                                    <div>
                                        <label class="block text-[10px] font-medium text-gray-500 mb-1">End Color</label>
                                        <div class="flex items-center gap-1.5">
                                            <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="wheel-bg-end-{{ $n }}"
                                                    value="{{ $curBgEnd }}"
                                                    oninput="syncWheelToInput(this, 'input-bg-end-{{ $n }}'); syncCardPromo({{ $n }});"
                                                    class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                                <input
                                                    type="text"
                                                    name="cards[{{ $n }}][bg_color_end]"
                                                    id="input-bg-end-{{ $n }}"
                                                    value="{{ ltrim($curBgEnd, '#') }}"
                                                    oninput="syncInputToWheel(this, 'wheel-bg-end-{{ $n }}'); syncCardPromo({{ $n }});"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Quick Preset Buttons --}}
                                <div class="pt-1">
                                    <div class="text-[10px] font-medium text-gray-400 mb-1 flex items-center justify-between">
                                        <span>Color Presets</span>
                                        <span class="text-[9px] text-gray-400">1-click apply</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($defaultPresets[$n] as $preset)
                                            <button
                                                type="button"
                                                onclick="applyPreset({{ $n }}, '{{ $preset['start'] }}', '{{ $preset['end'] }}', '{{ $preset['title'] }}', '{{ $preset['text'] }}', '{{ $preset['btn_bg'] }}', '{{ $preset['btn_text'] }}')"
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 border border-gray-200/60 cursor-pointer transition-colors"
                                                title="{{ $preset['name'] }}"
                                            >
                                                <span class="w-2.5 h-2.5 rounded-full border border-black/10 shrink-0" style="background: linear-gradient(135deg, {{ $preset['start'] }}, {{ $preset['end'] }});"></span>
                                                <span>{{ $preset['name'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100 my-1">

                            {{-- 2. Typography Colors --}}
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Title Color --}}
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Title Color</label>
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                            <input
                                                type="color"
                                                id="wheel-title-color-{{ $n }}"
                                                value="{{ $curTitleColor }}"
                                                oninput="syncWheelToInput(this, 'input-title-color-{{ $n }}'); syncCardPromo({{ $n }});"
                                                class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                            >
                                        </div>
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                            <input
                                                type="text"
                                                name="cards[{{ $n }}][title_color]"
                                                id="input-title-color-{{ $n }}"
                                                value="{{ ltrim($curTitleColor, '#') }}"
                                                oninput="syncInputToWheel(this, 'wheel-title-color-{{ $n }}'); syncCardPromo({{ $n }});"
                                                maxlength="7"
                                                class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                            >
                                        </div>
                                    </div>
                                </div>

                                {{-- Text / Description Color --}}
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Text Color</label>
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                            <input
                                                type="color"
                                                id="wheel-text-color-{{ $n }}"
                                                value="{{ $curTextColor }}"
                                                oninput="syncWheelToInput(this, 'input-text-color-{{ $n }}'); syncCardPromo({{ $n }});"
                                                class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                            >
                                        </div>
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                            <input
                                                type="text"
                                                name="cards[{{ $n }}][text_color]"
                                                id="input-text-color-{{ $n }}"
                                                value="{{ ltrim($curTextColor, '#') }}"
                                                oninput="syncInputToWheel(this, 'wheel-text-color-{{ $n }}'); syncCardPromo({{ $n }});"
                                                maxlength="7"
                                                class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="border-gray-100 my-1">

                            {{-- 3. Button Colors --}}
                            <div class="grid grid-cols-2 gap-2">
                                {{-- Button Background --}}
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Button BG</label>
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                            <input
                                                type="color"
                                                id="wheel-btn-bg-{{ $n }}"
                                                value="{{ $curBtnBg }}"
                                                oninput="syncWheelToInput(this, 'input-btn-bg-{{ $n }}'); syncCardPromo({{ $n }});"
                                                class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                            >
                                        </div>
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                            <input
                                                type="text"
                                                name="cards[{{ $n }}][button_bg_color]"
                                                id="input-btn-bg-{{ $n }}"
                                                value="{{ ltrim($curBtnBg, '#') }}"
                                                oninput="syncInputToWheel(this, 'wheel-btn-bg-{{ $n }}'); syncCardPromo({{ $n }});"
                                                maxlength="7"
                                                class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                            >
                                        </div>
                                    </div>
                                </div>

                                {{-- Button Text Color --}}
                                <div>
                                    <label class="block text-[10px] font-medium text-gray-500 mb-1">Button Text</label>
                                    <div class="flex items-center gap-1.5">
                                        <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                            <input
                                                type="color"
                                                id="wheel-btn-text-{{ $n }}"
                                                value="{{ $curBtnText }}"
                                                oninput="syncWheelToInput(this, 'input-btn-text-{{ $n }}'); syncCardPromo({{ $n }});"
                                                class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                                            >
                                        </div>
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                            <input
                                                type="text"
                                                name="cards[{{ $n }}][button_text_color]"
                                                id="input-btn-text-{{ $n }}"
                                                value="{{ ltrim($curBtnText, '#') }}"
                                                oninput="syncInputToWheel(this, 'wheel-btn-text-{{ $n }}'); syncCardPromo({{ $n }});"
                                                maxlength="7"
                                                class="w-full border border-gray-200 rounded-lg pl-5 pr-1.5 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                <x-button type="submit" class="shadow-sm px-6 py-2.5 font-bold">Save Promo Cards</x-button>
                <span class="text-xs text-gray-400">Settings save to the homepage immediately upon submission.</span>
            </div>
        </form>
    </div>

    {{-- Interactive Live Synchronization Script --}}
    <script>
        function syncWheelToInput(wheelEl, targetInputId) {
            const input = document.getElementById(targetInputId);
            if (input) {
                input.value = wheelEl.value.replace(/^#/, '');
            }
        }

        function syncInputToWheel(inputEl, targetWheelId) {
            const wheel = document.getElementById(targetWheelId);
            let val = inputEl.value.trim().replace(/^#/, '');
            if (wheel && (val.length === 3 || val.length === 6)) {
                if (val.length === 3) {
                    val = val.split('').map(c => c + c).join('');
                }
                wheel.value = '#' + val;
            }
        }

        function syncCardPromo(n) {
            const titleEl = document.getElementById('input-title-' + n);
            const textEl = document.getElementById('input-text-' + n);
            const btnEl = document.getElementById('input-button-' + n);

            const bgStartInput = document.getElementById('input-bg-start-' + n);
            const bgEndInput = document.getElementById('input-bg-end-' + n);
            const titleColorInput = document.getElementById('input-title-color-' + n);
            const textColorInput = document.getElementById('input-text-color-' + n);
            const btnBgInput = document.getElementById('input-btn-bg-' + n);
            const btnTextInput = document.getElementById('input-btn-text-' + n);

            const formatHex = (val, fallback) => {
                if (!val) return fallback;
                val = val.trim().replace(/^#/, '');
                if (val.length === 3) val = val.split('').map(c => c + c).join('');
                return val.length === 6 ? '#' + val : fallback;
            };

            const bgStart = formatHex(bgStartInput ? bgStartInput.value : '', '#ad7f67');
            const bgEnd = formatHex(bgEndInput ? bgEndInput.value : '', '#e8dedc');
            const titleColor = formatHex(titleColorInput ? titleColorInput.value : '', '#030712');
            const textColor = formatHex(textColorInput ? textColorInput.value : '', '#1f2937');
            const btnBg = formatHex(btnBgInput ? btnBgInput.value : '', '#fef08a');
            const btnText = formatHex(btnTextInput ? btnTextInput.value : '', '#000000');

            // 1. Update Preview Card Background
            const previewCard = document.getElementById('preview-card-' + n);
            if (previewCard) {
                previewCard.style.background = `linear-gradient(135deg, ${bgStart} 0%, ${bgEnd} 100%)`;
            }

            // 2. Update Header Pill
            const headerPill = document.getElementById('header-pill-' + n);
            if (headerPill) {
                headerPill.style.background = `linear-gradient(135deg, ${bgStart} 0%, ${bgEnd} 100%)`;
            }

            // 3. Update Title
            const previewTitle = document.getElementById('preview-title-' + n);
            if (previewTitle && titleEl) {
                previewTitle.innerText = titleEl.value;
                previewTitle.style.color = titleColor;
            }

            // 4. Update Description / Text
            const previewText = document.getElementById('preview-text-' + n);
            if (previewText && textEl) {
                previewText.innerText = textEl.value;
                previewText.style.color = textColor;
            }

            // 5. Update Button
            const previewBtnText = document.getElementById('preview-btn-text-' + n);
            const previewBtn = document.getElementById('preview-btn-' + n);
            if (previewBtnText && btnEl) {
                previewBtnText.innerText = btnEl.value;
            }
            if (previewBtn) {
                previewBtn.style.backgroundColor = btnBg;
                previewBtn.style.color = btnText;
            }
        }

        function applyPreset(n, start, end, title, text, btnBg, btnText) {
            const setVal = (inputId, wheelId, val) => {
                const input = document.getElementById(inputId);
                const wheel = document.getElementById(wheelId);
                const cleanHex = val.replace(/^#/, '');
                if (input) input.value = cleanHex;
                if (wheel) wheel.value = '#' + cleanHex;
            };

            setVal('input-bg-start-' + n, 'wheel-bg-start-' + n, start);
            setVal('input-bg-end-' + n, 'wheel-bg-end-' + n, end);
            setVal('input-title-color-' + n, 'wheel-title-color-' + n, title);
            setVal('input-text-color-' + n, 'wheel-text-color-' + n, text);
            setVal('input-btn-bg-' + n, 'wheel-btn-bg-' + n, btnBg);
            setVal('input-btn-text-' + n, 'wheel-btn-text-' + n, btnText);

            syncCardPromo(n);
        }
    </script>
@endsection
