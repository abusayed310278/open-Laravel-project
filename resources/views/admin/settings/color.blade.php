@extends('layouts.admin')

@section('title', 'Settings — Brand Color')

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

    <div class="space-y-6">
        {{-- Live UI Component Preview Canvas --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Real-Time Color Sandbox</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Preview how buttons, badges, navigation links, and highlights adapt instantly to your selected brand color.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-400">Primary:</span>
                        <button
                            type="button"
                            onclick="document.getElementById('color-wheel').click()"
                            title="Click to change primary brand color"
                            class="px-2.5 py-1 rounded-full text-xs font-mono font-bold text-gray-800 bg-gray-100 hover:bg-gray-200 border border-gray-300 transition-all hover:scale-105 cursor-pointer shadow-2xs flex items-center gap-1.5 group"
                        >
                            <span id="active-hex-badge">{{ $currentColor }}</span>
                            <span class="text-[10px] text-gray-400 group-hover:text-gray-600">✎</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-400">Hover:</span>
                        <button
                            type="button"
                            onclick="document.getElementById('hover-color-wheel').click()"
                            title="Click to change hover color"
                            id="active-hover-badge"
                            class="px-2.5 py-1 rounded-full text-xs font-mono font-bold border transition-all hover:scale-105 cursor-pointer shadow-2xs flex items-center gap-1.5 group"
                            style="background-color: {{ $currentHoverColor ?? '#fff200' }}; color: {{ $currentHoverTextColor ?? '#000000' }}; border-color: #d1d5db;"
                        >
                            <span>{{ $currentHoverColor ?? '#fff200' }}</span>
                            <span class="text-[10px] opacity-70 group-hover:opacity-100">✎</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Interactive Component Canvas --}}
            <div class="p-6 rounded-xl border border-gray-200 bg-gray-50/60 space-y-6">
                {{-- Button Styles --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Interactive Buttons</span>
                        <span class="text-[10px] text-gray-400">Hover over buttons to test live hover state (#fff200 / black)</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            id="preview-btn-primary"
                            class="px-5 py-2.5 rounded-lg text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                            style="background-color: {{ $currentColor }};"
                        >
                            Primary Action Button
                        </button>

                        <button
                            type="button"
                            id="preview-btn-outline"
                            class="px-5 py-2.5 rounded-lg text-xs font-semibold border-2 transition-all bg-white"
                            style="border-color: {{ $currentColor }}; color: {{ $currentColor }};"
                        >
                            Secondary Outlined
                        </button>

                        <button
                            type="button"
                            id="preview-btn-subtle"
                            class="px-5 py-2.5 rounded-lg text-xs font-semibold transition-all"
                            style="background-color: {{ $currentColor }}1a; color: {{ $currentColor }};"
                        >
                            Subtle Pill Action
                        </button>

                        {{-- Search Bar Action Buttons Preview --}}
                        <div class="flex items-center gap-1.5 p-1 bg-white border border-gray-200 rounded-lg shadow-2xs">
                            <span class="text-xs text-gray-400 px-2 select-none">Search electronics...</span>
                            <button
                                type="button"
                                id="preview-search-submit"
                                class="px-3 py-1.5 rounded-md text-white text-xs font-semibold shadow-xs transition-all cursor-pointer"
                                style="background-color: {{ $currentColor }};"
                            >
                                Search
                            </button>
                            <button
                                type="button"
                                id="preview-search-adv"
                                class="w-7 h-7 flex items-center justify-center rounded-md text-white shadow-xs transition-all cursor-pointer"
                                style="background-color: {{ $currentColor }};"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Badges & Status Indicators --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Badges, Links &amp; Navigation</span>
                    <div class="flex flex-wrap items-center gap-4">
                        <span
                            id="preview-badge-status"
                            class="px-3 py-1 rounded-full text-xs font-bold transition-all"
                            style="background-color: {{ $currentColor }}1f; color: {{ $currentColor }};"
                        >
                            ● Openbox Verified
                        </span>

                        <span
                            id="preview-tab-active"
                            class="text-xs font-bold pb-1 border-b-2 transition-all"
                            style="border-color: {{ $currentColor }}; color: {{ $currentColor }};"
                        >
                            Active Navigation Tab
                        </span>

                        <a
                            href="#"
                            id="preview-link"
                            onclick="return false;"
                            class="text-xs font-semibold underline underline-offset-4 transition-all"
                            style="color: {{ $currentColor }};"
                        >
                            Read Full Marketplace Policy ↗
                        </a>

                        <span
                            id="preview-price"
                            class="text-base font-extrabold font-mono transition-all ml-auto"
                            style="color: {{ $currentColor }};"
                        >
                            $ 29,999
                        </span>
                    </div>
                </div>

                {{-- Dynamic Color Swatch Bar --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Generated Brand Palette Preview</span>
                        <span class="text-[10px] text-gray-400">Click any weight to select as primary color</span>
                    </div>
                    <div id="palette-swatch-bar" class="grid grid-cols-5 sm:grid-cols-10 gap-2 rounded-xl p-2 bg-white border border-gray-200">
                        {{-- Swatches dynamically generated by JS --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Color Selection Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Select Brand Primary Color</h2>
            <p class="text-xs text-gray-500 mb-6">Choose a curated palette preset or enter a custom hex code.</p>

            <form method="POST" action="{{ route('admin.settings.color.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="brand_color_base" id="brand_color_base" value="{{ $currentBaseColor }}">
                <input type="hidden" name="brand_color_weight" id="brand_color_weight" value="{{ $currentWeight }}">

                {{-- Curated Presets --}}
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">Popular Brand Presets</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                        @php
                            $presets = [
                                ['name' => 'Amber Gold', 'hex' => '#f59e0b', 'desc' => 'Openbox Default'],
                                ['name' => 'Emerald', 'hex' => '#10b981', 'desc' => 'Fresh & Organic'],
                                ['name' => 'Royal Indigo', 'hex' => '#4f46e5', 'desc' => 'Tech & Modern'],
                                ['name' => 'Sky Blue', 'hex' => '#0ea5e9', 'desc' => 'Clean & Trusted'],
                                ['name' => 'Crimson Rose', 'hex' => '#f43f5e', 'desc' => 'Bold & Energetic'],
                                ['name' => 'Violet Purple', 'hex' => '#8b5cf6', 'desc' => 'Creative Luxury'],
                                ['name' => 'Midnight Slate', 'hex' => '#0f172a', 'desc' => 'Monochrome Minimal'],
                            ];
                        @endphp

                        @foreach ($presets as $p)
                            <button
                                type="button"
                                onclick="setBaseColor('{{ $p['hex'] }}')"
                                class="border-2 rounded-xl p-3 text-left transition-all hover:scale-105 group bg-white preset-btn"
                                data-hex="{{ $p['hex'] }}"
                                style="border-color: {{ strtolower($currentColor) === strtolower($p['hex']) ? $p['hex'] : '#e5e7eb' }};"
                            >
                                <div class="w-8 h-8 rounded-lg mb-2 shadow-xs" style="background-color: {{ $p['hex'] }};"></div>
                                <div class="text-xs font-bold text-gray-900 group-hover:text-brand-600">{{ $p['name'] }}</div>
                                <div class="text-[10px] font-mono text-gray-400">{{ $p['hex'] }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Custom Hex / Primary Color Input --}}
                <div class="pt-4 border-t border-gray-100">
                    <label for="brand_color_hex" class="block text-sm font-medium text-gray-700 mb-2">Custom Primary Brand Color</label>
                    <div class="flex items-center gap-3 max-w-md">
                        <div class="relative w-12 h-12 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0">
                            <input
                                type="color"
                                id="color-wheel"
                                value="{{ $currentColor }}"
                                oninput="syncBaseFromWheel(this.value)"
                                class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                            >
                        </div>
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                            <input
                                type="text"
                                name="brand_color_primary"
                                id="brand_color_hex"
                                value="{{ ltrim($currentColor, '#') }}"
                                oninput="syncBaseFromInput(this.value)"
                                placeholder="f59e0b"
                                maxlength="7"
                                required
                                class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-3 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent shadow-xs"
                            >
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Enter a 6-character hex color without '#'. All Tailwind brand scales (50 to 900) are auto-calculated and selectable above.</p>
                </div>

                {{-- Brand Hover State Settings --}}
                <div class="pt-6 border-t border-gray-100 space-y-4">
                    <div>
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-semibold text-gray-900">Button Hover State (Background &amp; Text)</label>
                            <span class="text-xs text-brand-600 font-semibold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Live Synchronized</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">When users hover over primary action buttons (Search, Start Selling, Add to Cart), this background and text color will be applied.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Hover Background Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                            <label for="brand_color_hover" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Hover Background Color</label>
                            <div class="flex items-center gap-3">
                                <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                    <input
                                        type="color"
                                        id="hover-color-wheel"
                                        value="{{ $currentHoverColor ?? '#fff200' }}"
                                        oninput="syncHoverFromWheel(this.value)"
                                        class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                    >
                                </div>
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                    <input
                                        type="text"
                                        name="brand_color_hover"
                                        id="brand_color_hover"
                                        value="{{ ltrim($currentHoverColor ?? '#fff200', '#') }}"
                                        oninput="syncHoverFromInput(this.value)"
                                        placeholder="fff200"
                                        maxlength="7"
                                        required
                                        class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2.5 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-xs"
                                    >
                                </div>
                            </div>

                            {{-- Quick Hover Background Presets --}}
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                <button type="button" onclick="setHoverColor('#fff200', '#000000')" class="px-2.5 py-1 rounded text-[11px] font-mono font-medium border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full border border-black/10" style="background-color: #fff200;"></span>
                                    #fff200 (Vibrant Yellow)
                                </button>
                                <button type="button" onclick="setHoverColor('#fef08a', '#000000')" class="px-2.5 py-1 rounded text-[11px] font-mono font-medium border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full border border-black/10" style="background-color: #fef08a;"></span>
                                    #fef08a (Soft Gold)
                                </button>
                                <button type="button" onclick="setHoverColor('#ffffff', '#0f172a')" class="px-2.5 py-1 rounded text-[11px] font-mono font-medium border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full border border-black/10" style="background-color: #ffffff;"></span>
                                    #ffffff (White)
                                </button>
                                <button type="button" onclick="setHoverColor('#0f172a', '#ffffff')" class="px-2.5 py-1 rounded text-[11px] font-mono font-medium border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full border border-black/10" style="background-color: #0f172a;"></span>
                                    #0f172a (Dark)
                                </button>
                            </div>
                        </div>

                        {{-- Hover Text Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200">
                            <label for="brand_color_hover_text" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Hover Text Color</label>
                            <div class="flex items-center gap-3">
                                <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                    <input
                                        type="color"
                                        id="hover-text-color-wheel"
                                        value="{{ $currentHoverTextColor ?? '#000000' }}"
                                        oninput="syncHoverTextFromWheel(this.value)"
                                        class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                    >
                                </div>
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                    <input
                                        type="text"
                                        name="brand_color_hover_text"
                                        id="brand_color_hover_text"
                                        value="{{ ltrim($currentHoverTextColor ?? '#000000', '#') }}"
                                        oninput="syncHoverTextFromInput(this.value)"
                                        placeholder="000000"
                                        maxlength="7"
                                        required
                                        class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2.5 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-xs"
                                    >
                                </div>
                            </div>

                            {{-- Quick Text Presets --}}
                            <div class="flex gap-2 mt-3">
                                <button type="button" onclick="setHoverTextColor('#000000')" class="px-3 py-1 rounded text-[11px] font-semibold border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full bg-black"></span>
                                    Black (#000000)
                                </button>
                                <button type="button" onclick="setHoverTextColor('#ffffff')" class="px-3 py-1 rounded text-[11px] font-semibold border border-gray-200 hover:border-gray-400 bg-white shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                    <span class="w-3 h-3 rounded-full bg-white border border-gray-300"></span>
                                    White (#ffffff)
                                </button>
                            </div>

                            {{-- Hover Text Weight --}}
                            <div class="mt-3 pt-3 border-t border-gray-200/70 flex items-center justify-between gap-2">
                                <label for="brand_color_hover_text_weight" class="text-xs font-semibold uppercase tracking-wider text-gray-500 shrink-0">Font Weight</label>
                                <select
                                    name="brand_color_hover_text_weight"
                                    id="brand_color_hover_text_weight"
                                    onchange="syncHoverTextWeight(this.value)"
                                    class="border border-gray-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                >
                                    <option value="400" @selected(($currentHoverTextWeight ?? '700') == '400')>400 (Regular)</option>
                                    <option value="500" @selected(($currentHoverTextWeight ?? '700') == '500')>500 (Medium)</option>
                                    <option value="600" @selected(($currentHoverTextWeight ?? '700') == '600')>600 (Semi-Bold)</option>
                                    <option value="700" @selected(($currentHoverTextWeight ?? '700') == '700')>700 (Bold - Default)</option>
                                    <option value="800" @selected(($currentHoverTextWeight ?? '700') == '800')>800 (Extra-Bold)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                {{-- Global Website Text Colors Section --}}
                <div class="pt-6 border-t border-gray-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                Global Website Text Colors (Applies Site-Wide)
                            </label>
                            <p class="text-xs text-gray-500 mt-0.5">Control the default body text, headings, and muted descriptions across all public pages of the website.</p>
                        </div>
                        <span class="text-xs text-blue-600 font-semibold bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-200">Site-Wide Typography</span>
                    </div>

                    {{-- Live Text Preview Box --}}
                    <div id="site-text-preview-box" class="p-4 rounded-xl border border-gray-200 bg-gray-50/70 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block">Live Typography Sandbox</span>
                        <h4 id="preview-text-heading" class="text-base transition-all" style="color: {{ $currentHeadingColor ?? '#0f172a' }}; font-weight: {{ $currentHeadingWeight ?? '700' }};">
                            Experience Next-Gen Electronics Marketplace
                        </h4>
                        <p id="preview-text-body" class="text-xs transition-all" style="color: {{ $currentTextColor ?? '#1f2937' }}; font-weight: {{ $currentTextWeight ?? '400' }};">
                            Browse certified open-box tech, brand-new devices, and quality refurbished electronics with transparent warranties and fast local delivery.
                        </p>
                        <p id="preview-text-muted" class="text-[11px] transition-all" style="color: {{ $currentMutedColor ?? '#64748b' }}; font-weight: {{ $currentMutedWeight ?? '400' }};">
                            Verified seller store • 24/7 customer support • Safe secure escrow checkout
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- 1. Main Body Text Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3 flex flex-col justify-between">
                            <div>
                                <label for="site_text_color" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Main Body Text Color</label>
                                <div class="flex items-center gap-3">
                                    <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                        <input
                                            type="color"
                                            id="site-text-color-wheel"
                                            value="{{ $currentTextColor ?? '#1f2937' }}"
                                            oninput="syncSiteTextFromWheel(this.value)"
                                            class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                        >
                                    </div>
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                        <input
                                            type="text"
                                            name="site_text_color"
                                            id="site_text_color"
                                            value="{{ ltrim($currentTextColor ?? '#1f2937', '#') }}"
                                            oninput="syncSiteTextFromInput(this.value)"
                                            placeholder="1f2937"
                                            maxlength="7"
                                            class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2.5 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-xs"
                                        >
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    <button type="button" onclick="setSiteTextColor('#1f2937')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#1f2937 (Default)</button>
                                    <button type="button" onclick="setSiteTextColor('#000000')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#000000 (Black)</button>
                                    <button type="button" onclick="setSiteTextColor('#334155')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#334155 (Slate)</button>
                                </div>
                            </div>

                            {{-- Font Weight Selector --}}
                            <div class="mt-3 pt-3 border-t border-gray-200/70 flex items-center justify-between gap-2">
                                <label for="site_text_weight" class="text-xs font-semibold uppercase tracking-wider text-gray-500 shrink-0">Font Weight</label>
                                <select
                                    name="site_text_weight"
                                    id="site_text_weight"
                                    onchange="syncSiteTextWeight(this.value)"
                                    class="border border-gray-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                >
                                    <option value="300" @selected(($currentTextWeight ?? '400') == '300')>300 (Light)</option>
                                    <option value="400" @selected(($currentTextWeight ?? '400') == '400')>400 (Regular - Default)</option>
                                    <option value="500" @selected(($currentTextWeight ?? '400') == '500')>500 (Medium)</option>
                                    <option value="600" @selected(($currentTextWeight ?? '400') == '600')>600 (Semi-Bold)</option>
                                    <option value="700" @selected(($currentTextWeight ?? '400') == '700')>700 (Bold)</option>
                                </select>
                            </div>
                        </div>

                        {{-- 2. Headings & Titles Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3 flex flex-col justify-between">
                            <div>
                                <label for="site_heading_color" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Headings &amp; Titles Color</label>
                                <div class="flex items-center gap-3">
                                    <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                        <input
                                            type="color"
                                            id="site-heading-color-wheel"
                                            value="{{ $currentHeadingColor ?? '#0f172a' }}"
                                            oninput="syncSiteHeadingFromWheel(this.value)"
                                            class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                        >
                                    </div>
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                        <input
                                            type="text"
                                            name="site_heading_color"
                                            id="site_heading_color"
                                            value="{{ ltrim($currentHeadingColor ?? '#0f172a', '#') }}"
                                            oninput="syncSiteHeadingFromInput(this.value)"
                                            placeholder="0f172a"
                                            maxlength="7"
                                            class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2.5 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-xs"
                                        >
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    <button type="button" onclick="setSiteHeadingColor('#0f172a')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#0f172a (Default)</button>
                                    <button type="button" onclick="setSiteHeadingColor('#000000')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#000000 (Black)</button>
                                    <button type="button" onclick="setSiteHeadingColor('#111827')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#111827 (Deep)</button>
                                </div>
                            </div>

                            {{-- Font Weight Selector --}}
                            <div class="mt-3 pt-3 border-t border-gray-200/70 flex items-center justify-between gap-2">
                                <label for="site_heading_weight" class="text-xs font-semibold uppercase tracking-wider text-gray-500 shrink-0">Font Weight</label>
                                <select
                                    name="site_heading_weight"
                                    id="site_heading_weight"
                                    onchange="syncSiteHeadingWeight(this.value)"
                                    class="border border-gray-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                >
                                    <option value="400" @selected(($currentHeadingWeight ?? '700') == '400')>400 (Regular)</option>
                                    <option value="500" @selected(($currentHeadingWeight ?? '700') == '500')>500 (Medium)</option>
                                    <option value="600" @selected(($currentHeadingWeight ?? '700') == '600')>600 (Semi-Bold)</option>
                                    <option value="700" @selected(($currentHeadingWeight ?? '700') == '700')>700 (Bold - Default)</option>
                                    <option value="800" @selected(($currentHeadingWeight ?? '700') == '800')>800 (Extra-Bold)</option>
                                    <option value="900" @selected(($currentHeadingWeight ?? '700') == '900')>900 (Black)</option>
                                </select>
                            </div>
                        </div>

                        {{-- 3. Muted / Secondary Text Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3 flex flex-col justify-between">
                            <div>
                                <label for="site_muted_color" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Muted / Subtitle Color</label>
                                <div class="flex items-center gap-3">
                                    <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                        <input
                                            type="color"
                                            id="site-muted-color-wheel"
                                            value="{{ $currentMutedColor ?? '#64748b' }}"
                                            oninput="syncSiteMutedFromWheel(this.value)"
                                            class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                        >
                                    </div>
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                        <input
                                            type="text"
                                            name="site_muted_color"
                                            id="site_muted_color"
                                            value="{{ ltrim($currentMutedColor ?? '#64748b', '#') }}"
                                            oninput="syncSiteMutedFromInput(this.value)"
                                            placeholder="64748b"
                                            maxlength="7"
                                            class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2.5 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-xs"
                                        >
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-1.5 pt-2">
                                    <button type="button" onclick="setSiteMutedColor('#64748b')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#64748b (Default)</button>
                                    <button type="button" onclick="setSiteMutedColor('#6b7280')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#6b7280 (Gray)</button>
                                    <button type="button" onclick="setSiteMutedColor('#94a3b8')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#94a3b8 (Light)</button>
                                </div>
                            </div>

                            {{-- Font Weight Selector --}}
                            <div class="mt-3 pt-3 border-t border-gray-200/70 flex items-center justify-between gap-2">
                                <label for="site_muted_weight" class="text-xs font-semibold uppercase tracking-wider text-gray-500 shrink-0">Font Weight</label>
                                <select
                                    name="site_muted_weight"
                                    id="site_muted_weight"
                                    onchange="syncSiteMutedWeight(this.value)"
                                    class="border border-gray-200 rounded-lg px-2.5 py-1 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                >
                                    <option value="300" @selected(($currentMutedWeight ?? '400') == '300')>300 (Light)</option>
                                    <option value="400" @selected(($currentMutedWeight ?? '400') == '400')>400 (Regular - Default)</option>
                                    <option value="500" @selected(($currentMutedWeight ?? '400') == '500')>500 (Medium)</option>
                                    <option value="600" @selected(($currentMutedWeight ?? '400') == '600')>600 (Semi-Bold)</option>
                                    <option value="700" @selected(($currentMutedWeight ?? '400') == '700')>700 (Bold)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Brand, Hover &amp; Text Colors
                    </x-button>

                    <button
                        type="button"
                        onclick="resetToDefaults()"
                        class="text-xs font-medium text-gray-500 hover:text-gray-700 underline"
                    >
                        Reset to Defaults
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentBaseColor = '{{ $currentColor }}';
        let currentSelectedWeight = '500';
        let currentPrimaryHex = '{{ $currentColor }}';
        let currentHoverHex = '{{ $currentHoverColor ?? '#fff200' }}';
        let currentHoverTextHex = '{{ $currentHoverTextColor ?? '#000000' }}';
        let currentHoverTextWeight = '{{ $currentHoverTextWeight ?? '700' }}';
        let activeScale = {};

        function hexToHsl(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) {
                hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            }
            const r = parseInt(hex.substring(0, 2), 16) / 255;
            const g = parseInt(hex.substring(2, 4), 16) / 255;
            const b = parseInt(hex.substring(4, 6), 16) / 255;

            const max = Math.max(r, g, b);
            const min = Math.min(r, g, b);
            const l = (max + min) / 2;

            if (max === min) {
                return [0, 0, l * 100];
            }

            const d = max - min;
            const s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

            let h;
            if (max === r) {
                h = ((g - b) / d + (g < b ? 6 : 0)) % 6;
            } else if (max === g) {
                h = (b - r) / d + 2;
            } else {
                h = (r - g) / d + 4;
            }

            return [h * 60, s * 100, l * 100];
        }

        function hslToHex(h, s, l) {
            s /= 100;
            l /= 100;
            const c = (1 - Math.abs(2 * l - 1)) * s;
            const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
            const m = l - c / 2;

            let r = 0, g = 0, b = 0;
            if (h < 60) { r = c; g = x; b = 0; }
            else if (h < 120) { r = x; g = c; b = 0; }
            else if (h < 180) { r = 0; g = c; b = x; }
            else if (h < 240) { r = 0; g = x; b = c; }
            else if (h < 300) { r = x; g = 0; b = c; }
            else { r = c; g = 0; b = x; }

            const toHex = (n) => {
                const val = Math.round((n + m) * 255);
                return Math.max(0, Math.min(255, val)).toString(16).padStart(2, '0');
            };

            return '#' + toHex(r) + toHex(g) + toHex(b);
        }

        function generateColorScale(hex) {
            hex = '#' + hex.replace('#', '').toLowerCase();
            const [h, s, l] = hexToHsl(hex);
            const l400 = l > 60 ? (l + 74.0) / 2 : Math.max(30.0, (l + 74.0) / 2);

            return {
                '50':  hslToHex(h, s, 97.0),
                '100': hslToHex(h, s, 93.0),
                '200': hslToHex(h, s, 85.0),
                '300': hslToHex(h, s, 74.0),
                '400': hslToHex(h, s, l400),
                '500': hex,
                '600': hslToHex(h, s, Math.max(12.0, l * 0.84)),
                '700': hslToHex(h, s, Math.max(9.0, l * 0.68)),
                '800': hslToHex(h, s, Math.max(6.0, l * 0.52)),
                '900': hslToHex(h, s, Math.max(4.0, l * 0.38)),
            };
        }

        function getContrastText(hex) {
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            if (hex.length !== 6) return '#0f172a';
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            return yiq >= 155 ? '#0f172a' : '#ffffff';
        }

        function updateHoverBadge() {
            const badge = document.getElementById('active-hover-badge');
            if (badge) {
                const textSpan = badge.querySelector('span:first-child');
                if (textSpan) textSpan.textContent = currentHoverHex.toUpperCase();
                badge.style.backgroundColor = currentHoverHex;
                badge.style.color = currentHoverTextHex;
                badge.style.fontWeight = currentHoverTextWeight;
            }
        }

        function updatePreviewComponents(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;

            currentPrimaryHex = hex;
            const contrastColor = getContrastText(hex);

            const badgeText = document.getElementById('active-hex-badge');
            if (badgeText) badgeText.textContent = hex.toUpperCase();

            const primaryBtn = document.getElementById('preview-btn-primary');
            if (primaryBtn) {
                primaryBtn.style.backgroundColor = hex;
                primaryBtn.style.color = contrastColor;
            }

            const outlineBtn = document.getElementById('preview-btn-outline');
            if (outlineBtn) {
                outlineBtn.style.borderColor = hex;
                outlineBtn.style.color = hex;
            }

            const subtleBtn = document.getElementById('preview-btn-subtle');
            if (subtleBtn) {
                subtleBtn.style.backgroundColor = hex + '1a';
                subtleBtn.style.color = hex;
            }

            const badgeStatus = document.getElementById('preview-badge-status');
            if (badgeStatus) {
                badgeStatus.style.backgroundColor = hex + '1f';
                badgeStatus.style.color = hex;
            }

            const tabActive = document.getElementById('preview-tab-active');
            if (tabActive) {
                tabActive.style.borderColor = hex;
                tabActive.style.color = hex;
            }

            const link = document.getElementById('preview-link');
            if (link) link.style.color = hex;

            const price = document.getElementById('preview-price');
            if (price) price.style.color = hex;

            const searchSubmit = document.getElementById('preview-search-submit');
            if (searchSubmit) {
                searchSubmit.style.backgroundColor = hex;
                searchSubmit.style.color = contrastColor;
            }

            const searchAdv = document.getElementById('preview-search-adv');
            if (searchAdv) {
                searchAdv.style.backgroundColor = hex;
                searchAdv.style.color = contrastColor;
            }
        }

        function renderPalette() {
            let html = '';
            for (const [step, shadeHex] of Object.entries(activeScale)) {
                const isSelected = (step === currentSelectedWeight);
                html += `
                    <button
                        type="button"
                        onclick="selectWeight('${step}')"
                        title="Click to select weight ${step} (${shadeHex.toUpperCase()}) as active brand color"
                        class="flex flex-col items-center justify-between p-1.5 rounded-lg border transition-all hover:scale-105 hover:shadow-md group text-center cursor-pointer ${isSelected ? 'border-gray-900 ring-2 ring-gray-900/30 bg-gray-50 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'}"
                    >
                        <div class="w-full h-8 rounded-md mb-1.5 shadow-2xs relative flex items-center justify-center border border-black/5" style="background-color: ${shadeHex};">
                            ${isSelected ? `<span class="w-4 h-4 rounded-full bg-white/95 text-gray-900 flex items-center justify-center text-[9px] font-bold shadow-xs">✓</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[11px] font-bold ${isSelected ? 'text-gray-900 font-extrabold' : 'text-gray-700'} leading-none">${step}</span>
                            ${step === '500' ? '<span class="text-[8px] font-bold px-1 py-0.5 rounded bg-amber-100 text-amber-800 leading-none">Base</span>' : ''}
                        </div>
                        <span class="text-[9px] font-mono text-gray-400 uppercase mt-0.5 leading-none">${shadeHex}</span>
                    </button>
                `;
            }
            const container = document.getElementById('palette-swatch-bar');
            if (container) container.innerHTML = html;
        }

        function selectWeight(step) {
            currentSelectedWeight = String(step);
            const weightInput = document.getElementById('brand_color_weight');
            if (weightInput) weightInput.value = currentSelectedWeight;

            const chosenHex = activeScale[currentSelectedWeight];
            if (!chosenHex) return;

            currentPrimaryHex = chosenHex;

            const wheel = document.getElementById('color-wheel');
            if (wheel) wheel.value = chosenHex.toLowerCase();

            const hexInput = document.getElementById('brand_color_hex');
            if (hexInput) hexInput.value = chosenHex.replace('#', '');

            updatePreviewComponents(chosenHex);
            renderPalette();
        }

        function setBaseColor(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;

            currentBaseColor = hex;
            currentSelectedWeight = '500';
            const baseInput = document.getElementById('brand_color_base');
            if (baseInput) baseInput.value = hex;
            const weightInput = document.getElementById('brand_color_weight');
            if (weightInput) weightInput.value = '500';

            currentPrimaryHex = hex;
            activeScale = generateColorScale(hex);

            const wheel = document.getElementById('color-wheel');
            if (wheel) wheel.value = hex.toLowerCase();

            const hexInput = document.getElementById('brand_color_hex');
            if (hexInput) hexInput.value = hex.replace('#', '');

            updatePreviewComponents(hex);
            renderPalette();

            // Highlight preset buttons if match
            document.querySelectorAll('.preset-btn').forEach(btn => {
                if (btn.dataset.hex.toLowerCase() === hex.toLowerCase()) {
                    btn.style.borderColor = hex;
                    btn.classList.add('shadow-sm');
                } else {
                    btn.style.borderColor = '#e5e7eb';
                    btn.classList.remove('shadow-sm');
                }
            });
        }

        function syncBaseFromWheel(hex) {
            setBaseColor(hex);
        }

        function syncBaseFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                setBaseColor('#' + clean);
            }
        }

        function setHoverColor(hoverHex, textHex) {
            if (!hoverHex.startsWith('#')) hoverHex = '#' + hoverHex;
            currentHoverHex = hoverHex;
            const wheel = document.getElementById('hover-color-wheel');
            if (wheel) wheel.value = hoverHex.toLowerCase();
            const input = document.getElementById('brand_color_hover');
            if (input) input.value = hoverHex.replace('#', '');

            if (textHex) {
                setHoverTextColor(textHex);
            } else {
                updateHoverBadge();
            }
        }

        function setHoverTextColor(textHex) {
            if (!textHex.startsWith('#')) textHex = '#' + textHex;
            currentHoverTextHex = textHex;
            const wheel = document.getElementById('hover-text-color-wheel');
            if (wheel) wheel.value = textHex.toLowerCase();
            const input = document.getElementById('brand_color_hover_text');
            if (input) input.value = textHex.replace('#', '');
            updateHoverBadge();
        }

        function syncHoverFromWheel(hex) {
            currentHoverHex = hex;
            const input = document.getElementById('brand_color_hover');
            if (input) input.value = hex.replace('#', '');
            updateHoverBadge();
        }

        function syncHoverFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                currentHoverHex = '#' + clean;
                const wheel = document.getElementById('hover-color-wheel');
                if (wheel) wheel.value = currentHoverHex.toLowerCase();
                updateHoverBadge();
            }
        }

        function syncHoverTextFromWheel(hex) {
            currentHoverTextHex = hex;
            const input = document.getElementById('brand_color_hover_text');
            if (input) input.value = hex.replace('#', '');
            updateHoverBadge();
        }

        function syncHoverTextFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                currentHoverTextHex = '#' + clean;
                const wheel = document.getElementById('hover-text-color-wheel');
                if (wheel) wheel.value = currentHoverTextHex.toLowerCase();
                updateHoverBadge();
            }
        }

        function syncSiteTextFromWheel(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const input = document.getElementById('site_text_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-body');
            if (preview) preview.style.color = hex;
        }

        function syncSiteTextFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const wheel = document.getElementById('site-text-color-wheel');
                if (wheel) wheel.value = hex.toLowerCase();
                const preview = document.getElementById('preview-text-body');
                if (preview) preview.style.color = hex;
            }
        }

        function setSiteTextColor(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const wheel = document.getElementById('site-text-color-wheel');
            if (wheel) wheel.value = hex.toLowerCase();
            const input = document.getElementById('site_text_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-body');
            if (preview) preview.style.color = hex;
        }

        function syncSiteHeadingFromWheel(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const input = document.getElementById('site_heading_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-heading');
            if (preview) preview.style.color = hex;
        }

        function syncSiteHeadingFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const wheel = document.getElementById('site-heading-color-wheel');
                if (wheel) wheel.value = hex.toLowerCase();
                const preview = document.getElementById('preview-text-heading');
                if (preview) preview.style.color = hex;
            }
        }

        function setSiteHeadingColor(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const wheel = document.getElementById('site-heading-color-wheel');
            if (wheel) wheel.value = hex.toLowerCase();
            const input = document.getElementById('site_heading_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-heading');
            if (preview) preview.style.color = hex;
        }

        function syncSiteMutedFromWheel(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const input = document.getElementById('site_muted_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-muted');
            if (preview) preview.style.color = hex;
        }

        function syncSiteMutedFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const wheel = document.getElementById('site-muted-color-wheel');
                if (wheel) wheel.value = hex.toLowerCase();
                const preview = document.getElementById('preview-text-muted');
                if (preview) preview.style.color = hex;
            }
        }

        function setSiteMutedColor(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            const wheel = document.getElementById('site-muted-color-wheel');
            if (wheel) wheel.value = hex.toLowerCase();
            const input = document.getElementById('site_muted_color');
            if (input) input.value = hex.replace('#', '');
            const preview = document.getElementById('preview-text-muted');
            if (preview) preview.style.color = hex;
        }

        function syncHoverTextWeight(weight) {
            currentHoverTextWeight = weight;
            updateHoverBadge();
        }

        function syncSiteTextWeight(weight) {
            const preview = document.getElementById('preview-text-body');
            if (preview) preview.style.fontWeight = weight;
        }

        function syncSiteHeadingWeight(weight) {
            const preview = document.getElementById('preview-text-heading');
            if (preview) preview.style.fontWeight = weight;
        }

        function syncSiteMutedWeight(weight) {
            const preview = document.getElementById('preview-text-muted');
            if (preview) preview.style.fontWeight = weight;
        }

        function setHoverTextWeight(weight) {
            const select = document.getElementById('brand_color_hover_text_weight');
            if (select) select.value = weight;
            syncHoverTextWeight(weight);
        }

        function setSiteTextWeight(weight) {
            const select = document.getElementById('site_text_weight');
            if (select) select.value = weight;
            syncSiteTextWeight(weight);
        }

        function setSiteHeadingWeight(weight) {
            const select = document.getElementById('site_heading_weight');
            if (select) select.value = weight;
            syncSiteHeadingWeight(weight);
        }

        function setSiteMutedWeight(weight) {
            const select = document.getElementById('site_muted_weight');
            if (select) select.value = weight;
            syncSiteMutedWeight(weight);
        }

        function resetToDefaults() {
            setBaseColor('#f59e0b');
            setHoverColor('#fff200', '#000000');
            setHoverTextWeight('700');
            setSiteTextColor('#1f2937');
            setSiteTextWeight('400');
            setSiteHeadingColor('#0f172a');
            setSiteHeadingWeight('700');
            setSiteMutedColor('#64748b');
            setSiteMutedWeight('400');
        }

        function setupButtonHoverListeners() {
            const hoverTargets = [
                document.getElementById('preview-btn-primary'),
                document.getElementById('preview-search-submit'),
                document.getElementById('preview-search-adv'),
            ];

            hoverTargets.forEach(el => {
                if (!el) return;
                const originalWeight = el.style.fontWeight || '';
                el.addEventListener('mouseenter', () => {
                    el.style.backgroundColor = currentHoverHex;
                    el.style.color = currentHoverTextHex;
                    el.style.fontWeight = currentHoverTextWeight;
                    const svg = el.querySelector('svg');
                    if (svg) svg.style.color = currentHoverTextHex;
                });
                el.addEventListener('mouseleave', () => {
                    el.style.backgroundColor = currentPrimaryHex;
                    const normalColor = getContrastText(currentPrimaryHex);
                    el.style.color = normalColor;
                    el.style.fontWeight = originalWeight;
                    const svg = el.querySelector('svg');
                    if (svg) svg.style.color = normalColor;
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            activeScale = generateColorScale('{{ $currentBaseColor }}');
            currentSelectedWeight = '{{ $currentWeight }}';
            currentPrimaryHex = '{{ $currentColor }}';
            updatePreviewComponents('{{ $currentColor }}');
            renderPalette();
            updateHoverBadge();
            setupButtonHoverListeners();
        });
    </script>
@endsection
