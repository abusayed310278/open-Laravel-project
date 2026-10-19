@extends('layouts.admin')

@section('title', 'Settings — Why Buy')

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
        {{-- Live Sandbox Preview Card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Live "Why Buy" Sandbox Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time preview of how the section heading and the 4 value proposition boxes appear on the homepage.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Synchronized
                    </span>
                </div>
            </div>

            {{-- Mock Homepage Container --}}
            <div class="bg-slate-50/80 p-5 sm:p-6 rounded-xl border border-gray-200/70">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-1 h-4 rounded-full bg-brand-400"></span>
                    <h2
                        id="preview-section-heading"
                        class="transition-all"
                        style="font-size: {{ old('heading_size', $content['heading_size'] ?? 18) }}px; font-weight: {{ old('heading_weight', $content['heading_weight'] ?? '700') }}; color: {{ old('heading_color', $content['heading_color'] ?? '#030712') }};"
                    >
                        {{ old('heading', $content['heading']) }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($content['items'] as $i => $item)
                        @php
                            $curTitle = old("items.$i.title", $item['title']);
                            $curText = old("items.$i.text", $item['text']);
                            $curIcon = old("items.$i.icon", $item['icon']);
                            $curIconBg = old("items.$i.icon_bg", $item['icon_bg']);
                            $curIconColor = old("items.$i.icon_color", $item['icon_color']);
                            $curIconSize = old("items.$i.icon_size", $item['icon_size']);
                            $curTitleSize = old("items.$i.title_size", $item['title_size']);
                            $curTitleWeight = old("items.$i.title_weight", $item['title_weight']);
                            $curTitleColor = old("items.$i.title_color", $item['title_color']);
                            $curTextSize = old("items.$i.text_size", $item['text_size']);
                            $curTextWeight = old("items.$i.text_weight", $item['text_weight']);
                            $curTextColor = old("items.$i.text_color", $item['text_color']);
                            $curCardBg = old("items.$i.card_bg", $item['card_bg'] ?? '#ffffff');
                            $curCustomIcon = $item['custom_icon'];
                        @endphp
                        <div
                            id="preview-card-{{ $i }}"
                            class="border border-gray-100 rounded-xl px-4 py-4 flex items-center gap-3.5 shadow-2xs transition-all"
                            style="background-color: {{ $curCardBg }};"
                        >
                            <div
                                id="preview-circle-{{ $i }}"
                                class="w-12 h-12 shrink-0 rounded-full flex items-center justify-center transition-all shadow-2xs"
                                style="background-color: {{ $curIconBg }}; color: {{ $curIconColor }};"
                            >
                                <span id="preview-icon-wrapper-{{ $i }}">
                                    @if ($curCustomIcon)
                                        <img
                                            src="{{ $curCustomIcon }}"
                                            alt="Icon"
                                            class="object-contain"
                                            style="width: {{ $curIconSize }}px; height: {{ $curIconSize }}px;"
                                        >
                                    @else
                                        <svg
                                            id="preview-svg-{{ $i }}"
                                            style="width: {{ $curIconSize }}px; height: {{ $curIconSize }}px;"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon_svg_d'] }}" />
                                        </svg>
                                    @endif
                                </span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3
                                    id="preview-title-{{ $i }}"
                                    class="leading-snug transition-all"
                                    style="font-size: {{ $curTitleSize }}px; font-weight: {{ $curTitleWeight }}; color: {{ $curTitleColor }};"
                                >
                                    {{ $curTitle }}
                                </h3>
                                <p
                                    id="preview-text-{{ $i }}"
                                    class="mt-0.5 leading-normal transition-all"
                                    style="font-size: {{ $curTextSize }}px; font-weight: {{ $curTextWeight }}; color: {{ $curTextColor }};"
                                >
                                    {{ $curText }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Configuration Form --}}
        <form method="POST" action="{{ route('admin.settings.why-buy.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. Section Heading Settings --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Section Header</h2>
                        <p class="text-xs text-gray-500">Configure title, size, weight, and color of the section title on the homepage.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-700">Section Settings</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="why-heading">Section Title</label>
                        <input
                            id="why-heading"
                            type="text"
                            name="heading"
                            value="{{ old('heading', $content['heading']) }}"
                            maxlength="120"
                            required
                            oninput="syncSectionHeadingText(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="heading_size">Font Size</label>
                        <select
                            name="heading_size"
                            id="heading_size"
                            onchange="syncSectionHeadingSize(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                        >
                            <option value="16" @selected(($content['heading_size'] ?? 18) == 16)>16px (Compact)</option>
                            <option value="18" @selected(($content['heading_size'] ?? 18) == 18)>18px (Default - text-lg)</option>
                            <option value="20" @selected(($content['heading_size'] ?? 18) == 20)>20px (text-xl)</option>
                            <option value="24" @selected(($content['heading_size'] ?? 18) == 24)>24px (text-2xl)</option>
                            <option value="28" @selected(($content['heading_size'] ?? 18) == 28)>28px (Extra Large)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="heading_weight">Font Weight</label>
                        <select
                            name="heading_weight"
                            id="heading_weight"
                            onchange="syncSectionHeadingWeight(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                        >
                            <option value="600" @selected(($content['heading_weight'] ?? '700') == '600')>600 (Semi-Bold)</option>
                            <option value="700" @selected(($content['heading_weight'] ?? '700') == '700')>700 (Bold - Default)</option>
                            <option value="800" @selected(($content['heading_weight'] ?? '700') == '800')>800 (Extra-Bold)</option>
                            <option value="900" @selected(($content['heading_weight'] ?? '700') == '900')>900 (Black)</option>
                        </select>
                    </div>
                </div>

                {{-- Heading Color Row --}}
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Heading Color:</span>
                    <div class="relative w-8 h-8 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                        <input
                            type="color"
                            id="heading-color-wheel"
                            value="{{ $content['heading_color'] ?? '#030712' }}"
                            oninput="syncSectionHeadingColor(this.value)"
                            class="absolute -top-2 -left-2 w-12 h-12 cursor-pointer border-0"
                        >
                    </div>
                    <div class="relative w-28">
                        <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 font-mono text-xs">#</span>
                        <input
                            type="text"
                            name="heading_color"
                            id="heading_color"
                            value="{{ ltrim($content['heading_color'] ?? '#030712', '#') }}"
                            oninput="syncSectionHeadingColorFromInput(this.value)"
                            maxlength="7"
                            class="w-full border border-gray-200 rounded-lg pl-6 pr-2 py-1.5 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="setHeadingColorPreset('#030712')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#030712 (Deep)</button>
                        <button type="button" onclick="setHeadingColorPreset('#0f172a')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#0f172a (Slate)</button>
                        <button type="button" onclick="setHeadingColorPreset('#1f2937')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer">#1f2937 (Default)</button>
                    </div>
                </div>
            </div>

            {{-- 2. Four Feature Boxes --}}
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">Feature Boxes (4 Cards)</h2>
                        <p class="text-xs text-gray-500">Customize icon, circle background color, font sizes and font weights for each value proposition.</p>
                    </div>
                    <span class="text-xs text-brand-700 bg-brand-50 border border-brand-200 px-2.5 py-1 rounded-md font-medium">Box Customization</span>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    @foreach ($content['items'] as $i => $item)
                        @php
                            $boxNumber = $i + 1;
                            $curTitle = old("items.$i.title", $item['title']);
                            $curText = old("items.$i.text", $item['text']);
                            $curIcon = old("items.$i.icon", $item['icon']);
                            $curIconBg = old("items.$i.icon_bg", $item['icon_bg']);
                            $curIconColor = old("items.$i.icon_color", $item['icon_color']);
                            $curIconSize = old("items.$i.icon_size", $item['icon_size']);
                            $curTitleSize = old("items.$i.title_size", $item['title_size']);
                            $curTitleWeight = old("items.$i.title_weight", $item['title_weight']);
                            $curTitleColor = old("items.$i.title_color", $item['title_color']);
                            $curTextSize = old("items.$i.text_size", $item['text_size']);
                            $curTextWeight = old("items.$i.text_weight", $item['text_weight']);
                            $curTextColor = old("items.$i.text_color", $item['text_color']);
                            $curCardBg = old("items.$i.card_bg", $item['card_bg'] ?? '#ffffff');
                            $curCustomIcon = $item['custom_icon'];
                        @endphp

                        <div class="bg-white border border-gray-100 rounded-xl p-5 sm:p-6 shadow-xs space-y-5">
                            {{-- Box Header Badge --}}
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-gray-900 text-white font-bold text-xs flex items-center justify-center">{{ $boxNumber }}</span>
                                    <h3 class="text-sm font-bold text-gray-900">Box {{ $boxNumber }}: {{ $curTitle }}</h3>
                                </div>
                                <span class="text-[11px] font-mono text-gray-400 uppercase tracking-wider">Index #{{ $i }}</span>
                            </div>

                            {{-- Row 1: Copywriting (Title & Subtitle) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Card Title</label>
                                    <input
                                        type="text"
                                        name="items[{{ $i }}][title]"
                                        id="input-title-{{ $i }}"
                                        value="{{ $curTitle }}"
                                        maxlength="120"
                                        required
                                        oninput="syncCardTitle({{ $i }}, this.value)"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-semibold text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                    >
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Description Subtitle</label>
                                    <input
                                        type="text"
                                        name="items[{{ $i }}][text]"
                                        id="input-text-{{ $i }}"
                                        value="{{ $curText }}"
                                        maxlength="255"
                                        required
                                        oninput="syncCardText({{ $i }}, this.value)"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                    >
                                </div>
                            </div>

                            {{-- Row 2: Icon Controls --}}
                            <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/60 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                                        Icon Settings
                                    </span>
                                    <span class="text-[11px] text-gray-400">Select pre-built SVG icon or upload custom icon</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    {{-- Pre-built Icon Selector --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Select SVG Icon</label>
                                        <select
                                            name="items[{{ $i }}][icon]"
                                            id="input-icon-{{ $i }}"
                                            onchange="syncCardIcon({{ $i }}, this.value)"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                        >
                                            @foreach ($icons as $key => $iconData)
                                                <option value="{{ $key }}" @selected($curIcon === $key)>
                                                    {{ $iconData['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Icon Color --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Icon Stroke Color</label>
                                        <div class="flex items-center gap-2">
                                            <div class="relative w-9 h-9 rounded-lg border border-gray-200 overflow-hidden shadow-2xs shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="icon-color-wheel-{{ $i }}"
                                                    value="{{ $curIconColor }}"
                                                    oninput="syncCardIconColor({{ $i }}, this.value)"
                                                    class="absolute -top-2 -left-2 w-14 h-14 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-gray-400 font-mono text-xs">#</span>
                                                <input
                                                    type="text"
                                                    name="items[{{ $i }}][icon_color]"
                                                    id="input-icon-color-{{ $i }}"
                                                    value="{{ ltrim($curIconColor, '#') }}"
                                                    oninput="syncCardIconColorFromInput({{ $i }}, this.value)"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded-lg pl-6 pr-2 py-2 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Icon Size --}}
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Icon Size</label>
                                        <select
                                            name="items[{{ $i }}][icon_size]"
                                            id="input-icon-size-{{ $i }}"
                                            onchange="syncCardIconSize({{ $i }}, this.value)"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                        >
                                            <option value="18" @selected($curIconSize == 18)>18px (Small)</option>
                                            <option value="20" @selected($curIconSize == 20)>20px</option>
                                            <option value="24" @selected($curIconSize == 24)>24px (Default - w-6 h-6)</option>
                                            <option value="28" @selected($curIconSize == 28)>28px (Medium)</option>
                                            <option value="32" @selected($curIconSize == 32)>32px (Large)</option>
                                            <option value="36" @selected($curIconSize == 36)>36px (Extra Large)</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Custom Icon File Upload --}}
                                <div class="pt-2 border-t border-gray-200/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex-1">
                                        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Optional Custom Icon Upload (SVG, PNG, WebP)</label>
                                        <input
                                            type="file"
                                            name="items[{{ $i }}][custom_icon]"
                                            accept="image/png,image/jpeg,image/svg+xml,image/webp"
                                            onchange="previewCustomIconFile({{ $i }}, this)"
                                            class="block w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer"
                                        >
                                    </div>
                                    @if ($curCustomIcon)
                                        <div class="flex items-center gap-3 shrink-0">
                                            <div class="flex items-center gap-1.5 p-1 px-2 rounded bg-white border border-gray-200 text-xs text-gray-700">
                                                <img src="{{ $curCustomIcon }}" alt="Current" class="w-5 h-5 object-contain">
                                                <span>Active Icon</span>
                                            </div>
                                            <label class="inline-flex items-center gap-1.5 text-xs text-red-600 cursor-pointer font-medium">
                                                <input type="checkbox" name="items[{{ $i }}][remove_custom_icon]" value="1" class="rounded text-red-600 focus:ring-red-400">
                                                Remove custom
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Row 3: Background Colors (Icon Circle Background & Card Background) --}}
                            <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/60 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                        Background Colors
                                    </span>
                                    <span class="text-[11px] text-gray-400">Circle badge background &amp; card background</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    {{-- 1. Icon Circle Background Color (Circled in user screenshot) --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                            Icon Circle Background Color <span class="text-red-500 font-bold">*</span>
                                        </label>
                                        <div class="flex items-center gap-2.5">
                                            <div class="relative w-10 h-10 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="icon-bg-wheel-{{ $i }}"
                                                    value="{{ $curIconBg }}"
                                                    oninput="syncCardIconBg({{ $i }}, this.value)"
                                                    class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-mono text-xs">#</span>
                                                <input
                                                    type="text"
                                                    name="items[{{ $i }}][icon_bg]"
                                                    id="input-icon-bg-{{ $i }}"
                                                    value="{{ ltrim($curIconBg, '#') }}"
                                                    oninput="syncCardIconBgFromInput({{ $i }}, this.value)"
                                                    maxlength="7"
                                                    required
                                                    class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                                >
                                            </div>
                                        </div>
                                        {{-- Quick Background Swatches --}}
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#fef3c7')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#fef3c7] hover:brightness-95 cursor-pointer text-gray-800" title="Soft Amber">Amber</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#ffedd5')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#ffedd5] hover:brightness-95 cursor-pointer text-gray-800" title="Soft Peach">Peach</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#dcfce7')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#dcfce7] hover:brightness-95 cursor-pointer text-gray-800 font-semibold" title="Soft Green (Circled in user screenshot)">Green</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#e0e7ff')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#e0e7ff] hover:brightness-95 cursor-pointer text-gray-800" title="Soft Blue">Blue</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#f3e8ff')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#f3e8ff] hover:brightness-95 cursor-pointer text-gray-800" title="Soft Purple">Purple</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#ffe4e6')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-[#ffe4e6] hover:brightness-95 cursor-pointer text-gray-800" title="Soft Rose">Rose</button>
                                            <button type="button" onclick="setCardIconBgPreset({{ $i }}, '#ffffff')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer text-gray-800" title="White">White</button>
                                        </div>
                                    </div>

                                    {{-- 2. Card Background Color --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Card Container Background</label>
                                        <div class="flex items-center gap-2.5">
                                            <div class="relative w-10 h-10 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="card-bg-wheel-{{ $i }}"
                                                    value="{{ $curCardBg }}"
                                                    oninput="syncCardBg({{ $i }}, this.value)"
                                                    class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-mono text-xs">#</span>
                                                <input
                                                    type="text"
                                                    name="items[{{ $i }}][card_bg]"
                                                    id="input-card-bg-{{ $i }}"
                                                    value="{{ ltrim($curCardBg, '#') }}"
                                                    oninput="syncCardBgFromInput({{ $i }}, this.value)"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded-lg pl-7 pr-3 py-2 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                                                >
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                            <button type="button" onclick="setCardBgPreset({{ $i }}, '#ffffff')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer text-gray-800">#ffffff (Default)</button>
                                            <button type="button" onclick="setCardBgPreset({{ $i }}, '#f8fafc')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer text-gray-800">#f8fafc (Slate)</button>
                                            <button type="button" onclick="setCardBgPreset({{ $i }}, '#fefce8')" class="px-2 py-0.5 rounded text-[10px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer text-gray-800">#fefce8 (Cream)</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Row 4: Typography (Font Size & Weight) --}}
                            <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-200/60 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 5v14m0 0h4m-4 0H5m10-7h6m-3 0v7m0 0h3m-3 0h-3"/></svg>
                                        Typography (Font Size &amp; Weight)
                                    </span>
                                    <span class="text-[11px] text-gray-400">Card title and subtitle typography</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    {{-- Title Typography --}}
                                    <div class="p-3 bg-white rounded-lg border border-gray-200/80 space-y-3">
                                        <span class="text-xs font-bold text-gray-800 block">Title Typography</span>
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Font Size</label>
                                                <select
                                                    name="items[{{ $i }}][title_size]"
                                                    id="input-title-size-{{ $i }}"
                                                    onchange="syncCardTitleSize({{ $i }}, this.value)"
                                                    class="w-full border border-gray-200 rounded-md px-2 py-1.5 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                                >
                                                    <option value="12" @selected($curTitleSize == 12)>12px (Small)</option>
                                                    <option value="13" @selected($curTitleSize == 13)>13px</option>
                                                    <option value="14" @selected($curTitleSize == 14)>14px (Default - text-sm)</option>
                                                    <option value="15" @selected($curTitleSize == 15)>15px</option>
                                                    <option value="16" @selected($curTitleSize == 16)>16px (text-base)</option>
                                                    <option value="18" @selected($curTitleSize == 18)>18px (text-lg)</option>
                                                    <option value="20" @selected($curTitleSize == 20)>20px</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Font Weight</label>
                                                <select
                                                    name="items[{{ $i }}][title_weight]"
                                                    id="input-title-weight-{{ $i }}"
                                                    onchange="syncCardTitleWeight({{ $i }}, this.value)"
                                                    class="w-full border border-gray-200 rounded-md px-2 py-1.5 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                                >
                                                    <option value="400" @selected($curTitleWeight == '400')>400 (Regular)</option>
                                                    <option value="500" @selected($curTitleWeight == '500')>500 (Medium)</option>
                                                    <option value="600" @selected($curTitleWeight == '600')>600 (Semi-Bold)</option>
                                                    <option value="700" @selected($curTitleWeight == '700')>700 (Bold - Default)</option>
                                                    <option value="800" @selected($curTitleWeight == '800')>800 (Extra-Bold)</option>
                                                    <option value="900" @selected($curTitleWeight == '900')>900 (Black)</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Title Color --}}
                                        <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
                                            <label class="text-[11px] font-medium text-gray-500 shrink-0">Color:</label>
                                            <div class="relative w-6 h-6 rounded border border-gray-200 overflow-hidden shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="title-color-wheel-{{ $i }}"
                                                    value="{{ $curTitleColor }}"
                                                    oninput="syncCardTitleColor({{ $i }}, this.value)"
                                                    class="absolute -top-1 -left-1 w-10 h-10 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                                <input
                                                    type="text"
                                                    name="items[{{ $i }}][title_color]"
                                                    id="input-title-color-{{ $i }}"
                                                    value="{{ ltrim($curTitleColor, '#') }}"
                                                    oninput="syncCardTitleColorFromInput({{ $i }}, this.value)"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded px-2 pl-5 py-1 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Subtitle Typography --}}
                                    <div class="p-3 bg-white rounded-lg border border-gray-200/80 space-y-3">
                                        <span class="text-xs font-bold text-gray-800 block">Description Typography</span>
                                        <div class="grid grid-cols-2 gap-2.5">
                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Font Size</label>
                                                <select
                                                    name="items[{{ $i }}][text_size]"
                                                    id="input-text-size-{{ $i }}"
                                                    onchange="syncCardTextSize({{ $i }}, this.value)"
                                                    class="w-full border border-gray-200 rounded-md px-2 py-1.5 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                                >
                                                    <option value="10" @selected($curTextSize == 10)>10px</option>
                                                    <option value="11" @selected($curTextSize == 11)>11px</option>
                                                    <option value="12" @selected($curTextSize == 12)>12px (Default - text-xs)</option>
                                                    <option value="13" @selected($curTextSize == 13)>13px</option>
                                                    <option value="14" @selected($curTextSize == 14)>14px (text-sm)</option>
                                                    <option value="15" @selected($curTextSize == 15)>15px</option>
                                                    <option value="16" @selected($curTextSize == 16)>16px</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-[11px] font-medium text-gray-500 mb-1">Font Weight</label>
                                                <select
                                                    name="items[{{ $i }}][text_weight]"
                                                    id="input-text-weight-{{ $i }}"
                                                    onchange="syncCardTextWeight({{ $i }}, this.value)"
                                                    class="w-full border border-gray-200 rounded-md px-2 py-1.5 text-xs font-semibold text-gray-800 bg-white shadow-2xs focus:outline-none focus:ring-2 focus:ring-brand-400 cursor-pointer"
                                                >
                                                    <option value="300" @selected($curTextWeight == '300')>300 (Light)</option>
                                                    <option value="400" @selected($curTextWeight == '400')>400 (Regular - Default)</option>
                                                    <option value="500" @selected($curTextWeight == '500')>500 (Medium)</option>
                                                    <option value="600" @selected($curTextWeight == '600')>600 (Semi-Bold)</option>
                                                    <option value="700" @selected($curTextWeight == '700')>700 (Bold)</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Subtitle Color --}}
                                        <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
                                            <label class="text-[11px] font-medium text-gray-500 shrink-0">Color:</label>
                                            <div class="relative w-6 h-6 rounded border border-gray-200 overflow-hidden shrink-0 bg-white">
                                                <input
                                                    type="color"
                                                    id="text-color-wheel-{{ $i }}"
                                                    value="{{ $curTextColor }}"
                                                    oninput="syncCardTextColor({{ $i }}, this.value)"
                                                    class="absolute -top-1 -left-1 w-10 h-10 cursor-pointer border-0"
                                                >
                                            </div>
                                            <div class="relative flex-1">
                                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-400 font-mono text-[11px]">#</span>
                                                <input
                                                    type="text"
                                                    name="items[{{ $i }}][text_color]"
                                                    id="input-text-color-{{ $i }}"
                                                    value="{{ ltrim($curTextColor, '#') }}"
                                                    oninput="syncCardTextColorFromInput({{ $i }}, this.value)"
                                                    maxlength="7"
                                                    class="w-full border border-gray-200 rounded px-2 pl-5 py-1 text-xs font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <x-button type="submit" class="shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Why Buy Section
                </x-button>

                <button
                    type="button"
                    onclick="resetWhyBuyToDefaults()"
                    class="text-xs font-medium text-gray-500 hover:text-gray-700 underline cursor-pointer"
                >
                    Reset to Defaults
                </button>
            </div>
        </form>
    </div>

    {{-- Interactive Live Synchronization Scripts --}}
    <script>
        const iconDefinitions = @json($icons);

        const defaultData = [
            {
                title: 'Verified Quality',
                text: 'All products tested and verified',
                icon: 'shield',
                icon_bg: '#fef3c7',
                icon_color: '#1f2937',
                icon_size: 24,
                title_size: 14,
                title_weight: '700',
                title_color: '#0f172a',
                text_size: 12,
                text_weight: '400',
                text_color: '#64748b',
                card_bg: '#ffffff'
            },
            {
                title: 'Fast & Reliable Delivery',
                text: 'Across Abu Dhabi & UAE',
                icon: 'truck',
                icon_bg: '#ffedd5',
                icon_color: '#1f2937',
                icon_size: 24,
                title_size: 14,
                title_weight: '700',
                title_color: '#0f172a',
                text_size: 12,
                text_weight: '400',
                text_color: '#64748b',
                card_bg: '#ffffff'
            },
            {
                title: 'A More Sustainable Choice',
                text: 'Give tech a second life',
                icon: 'leaf',
                icon_bg: '#dcfce7',
                icon_color: '#16a34a',
                icon_size: 24,
                title_size: 14,
                title_weight: '700',
                title_color: '#0f172a',
                text_size: 12,
                text_weight: '400',
                text_color: '#64748b',
                card_bg: '#ffffff'
            },
            {
                title: 'Dedicated Support',
                text: "We're here to help",
                icon: 'headset',
                icon_bg: '#ffedd5',
                icon_color: '#1f2937',
                icon_size: 24,
                title_size: 14,
                title_weight: '700',
                title_color: '#0f172a',
                text_size: 12,
                text_weight: '400',
                text_color: '#64748b',
                card_bg: '#ffffff'
            }
        ];

        // Section Heading sync functions
        function syncSectionHeadingText(val) {
            const el = document.getElementById('preview-section-heading');
            if (el) el.textContent = val;
        }

        function syncSectionHeadingSize(val) {
            const el = document.getElementById('preview-section-heading');
            if (el) el.style.fontSize = val + 'px';
        }

        function syncSectionHeadingWeight(val) {
            const el = document.getElementById('preview-section-heading');
            if (el) el.style.fontWeight = val;
        }

        function syncSectionHeadingColor(val) {
            if (!val.startsWith('#')) val = '#' + val;
            const el = document.getElementById('preview-section-heading');
            if (el) el.style.color = val;
            const input = document.getElementById('heading_color');
            if (input) input.value = val.replace('#', '');
        }

        function syncSectionHeadingColorFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const el = document.getElementById('preview-section-heading');
                if (el) el.style.color = hex;
                const wheel = document.getElementById('heading-color-wheel');
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        function setHeadingColorPreset(hex) {
            const wheel = document.getElementById('heading-color-wheel');
            if (wheel) wheel.value = hex.toLowerCase();
            syncSectionHeadingColor(hex);
        }

        // Card sync functions
        function syncCardTitle(index, val) {
            const el = document.getElementById('preview-title-' + index);
            if (el) el.textContent = val;
        }

        function syncCardText(index, val) {
            const el = document.getElementById('preview-text-' + index);
            if (el) el.textContent = val;
        }

        function syncCardIcon(index, iconKey) {
            const svgPath = iconDefinitions[iconKey] ? iconDefinitions[iconKey].d : '';
            if (!svgPath) return;

            const wrapper = document.getElementById('preview-icon-wrapper-' + index);
            const currentSize = document.getElementById('input-icon-size-' + index)?.value || 24;
            if (wrapper) {
                wrapper.innerHTML = `
                    <svg id="preview-svg-${index}" style="width: ${currentSize}px; height: ${currentSize}px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="${svgPath}" />
                    </svg>
                `;
            }
        }

        function syncCardIconColor(index, val) {
            if (!val.startsWith('#')) val = '#' + val;
            const circle = document.getElementById('preview-circle-' + index);
            if (circle) circle.style.color = val;
            const input = document.getElementById('input-icon-color-' + index);
            if (input) input.value = val.replace('#', '');
        }

        function syncCardIconColorFromInput(index, raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const circle = document.getElementById('preview-circle-' + index);
                if (circle) circle.style.color = hex;
                const wheel = document.getElementById('icon-color-wheel-' + index);
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        function syncCardIconSize(index, val) {
            const svg = document.getElementById('preview-svg-' + index);
            if (svg) {
                svg.style.width = val + 'px';
                svg.style.height = val + 'px';
            }
            const img = document.querySelector('#preview-icon-wrapper-' + index + ' img');
            if (img) {
                img.style.width = val + 'px';
                img.style.height = val + 'px';
            }
        }

        function previewCustomIconFile(index, input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentSize = document.getElementById('input-icon-size-' + index)?.value || 24;
                    const wrapper = document.getElementById('preview-icon-wrapper-' + index);
                    if (wrapper) {
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" alt="Preview" class="object-contain" style="width: ${currentSize}px; height: ${currentSize}px;">
                        `;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Icon Circle Background sync
        function syncCardIconBg(index, val) {
            if (!val.startsWith('#')) val = '#' + val;
            const circle = document.getElementById('preview-circle-' + index);
            if (circle) circle.style.backgroundColor = val;
            const input = document.getElementById('input-icon-bg-' + index);
            if (input) input.value = val.replace('#', '');
        }

        function syncCardIconBgFromInput(index, raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const circle = document.getElementById('preview-circle-' + index);
                if (circle) circle.style.backgroundColor = hex;
                const wheel = document.getElementById('icon-bg-wheel-' + index);
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        function setCardIconBgPreset(index, hex) {
            const wheel = document.getElementById('icon-bg-wheel-' + index);
            if (wheel) wheel.value = hex.toLowerCase();
            syncCardIconBg(index, hex);
        }

        // Card Container Background sync
        function syncCardBg(index, val) {
            if (!val.startsWith('#')) val = '#' + val;
            const card = document.getElementById('preview-card-' + index);
            if (card) card.style.backgroundColor = val;
            const input = document.getElementById('input-card-bg-' + index);
            if (input) input.value = val.replace('#', '');
        }

        function syncCardBgFromInput(index, raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const card = document.getElementById('preview-card-' + index);
                if (card) card.style.backgroundColor = hex;
                const wheel = document.getElementById('card-bg-wheel-' + index);
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        function setCardBgPreset(index, hex) {
            const wheel = document.getElementById('card-bg-wheel-' + index);
            if (wheel) wheel.value = hex.toLowerCase();
            syncCardBg(index, hex);
        }

        // Title Typography sync
        function syncCardTitleSize(index, val) {
            const el = document.getElementById('preview-title-' + index);
            if (el) el.style.fontSize = val + 'px';
        }

        function syncCardTitleWeight(index, val) {
            const el = document.getElementById('preview-title-' + index);
            if (el) el.style.fontWeight = val;
        }

        function syncCardTitleColor(index, val) {
            if (!val.startsWith('#')) val = '#' + val;
            const el = document.getElementById('preview-title-' + index);
            if (el) el.style.color = val;
            const input = document.getElementById('input-title-color-' + index);
            if (input) input.value = val.replace('#', '');
        }

        function syncCardTitleColorFromInput(index, raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const el = document.getElementById('preview-title-' + index);
                if (el) el.style.color = hex;
                const wheel = document.getElementById('title-color-wheel-' + index);
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        // Text Typography sync
        function syncCardTextSize(index, val) {
            const el = document.getElementById('preview-text-' + index);
            if (el) el.style.fontSize = val + 'px';
        }

        function syncCardTextWeight(index, val) {
            const el = document.getElementById('preview-text-' + index);
            if (el) el.style.fontWeight = val;
        }

        function syncCardTextColor(index, val) {
            if (!val.startsWith('#')) val = '#' + val;
            const el = document.getElementById('preview-text-' + index);
            if (el) el.style.color = val;
            const input = document.getElementById('input-text-color-' + index);
            if (input) input.value = val.replace('#', '');
        }

        function syncCardTextColorFromInput(index, raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                const el = document.getElementById('preview-text-' + index);
                if (el) el.style.color = hex;
                const wheel = document.getElementById('text-color-wheel-' + index);
                if (wheel) wheel.value = hex.toLowerCase();
            }
        }

        // Reset to Defaults
        function resetWhyBuyToDefaults() {
            // Heading reset
            const headingInput = document.getElementById('why-heading');
            if (headingInput) headingInput.value = 'Why Buy from OpenBox?';
            syncSectionHeadingText('Why Buy from OpenBox?');

            const headingSize = document.getElementById('heading_size');
            if (headingSize) headingSize.value = '18';
            syncSectionHeadingSize('18');

            const headingWeight = document.getElementById('heading_weight');
            if (headingWeight) headingWeight.value = '700';
            syncSectionHeadingWeight('700');

            setHeadingColorPreset('#030712');

            // 4 Items reset
            defaultData.forEach((d, i) => {
                const titleInput = document.getElementById('input-title-' + i);
                if (titleInput) titleInput.value = d.title;
                syncCardTitle(i, d.title);

                const textInput = document.getElementById('input-text-' + i);
                if (textInput) textInput.value = d.text;
                syncCardText(i, d.text);

                const iconSelect = document.getElementById('input-icon-' + i);
                if (iconSelect) iconSelect.value = d.icon;
                syncCardIcon(i, d.icon);

                setCardIconBgPreset(i, d.icon_bg);

                const iconColorWheel = document.getElementById('icon-color-wheel-' + i);
                if (iconColorWheel) iconColorWheel.value = d.icon_color.toLowerCase();
                syncCardIconColor(i, d.icon_color);

                const iconSize = document.getElementById('input-icon-size-' + i);
                if (iconSize) iconSize.value = String(d.icon_size);
                syncCardIconSize(i, d.icon_size);

                setCardBgPreset(i, d.card_bg);

                const titleSize = document.getElementById('input-title-size-' + i);
                if (titleSize) titleSize.value = String(d.title_size);
                syncCardTitleSize(i, d.title_size);

                const titleWeight = document.getElementById('input-title-weight-' + i);
                if (titleWeight) titleWeight.value = d.title_weight;
                syncCardTitleWeight(i, d.title_weight);

                const titleColorWheel = document.getElementById('title-color-wheel-' + i);
                if (titleColorWheel) titleColorWheel.value = d.title_color.toLowerCase();
                syncCardTitleColor(i, d.title_color);

                const textSize = document.getElementById('input-text-size-' + i);
                if (textSize) textSize.value = String(d.text_size);
                syncCardTextSize(i, d.text_size);

                const textWeight = document.getElementById('input-text-weight-' + i);
                if (textWeight) textWeight.value = d.text_weight;
                syncCardTextWeight(i, d.text_weight);

                const textColorWheel = document.getElementById('text-color-wheel-' + i);
                if (textColorWheel) textColorWheel.value = d.text_color.toLowerCase();
                syncCardTextColor(i, d.text_color);
            });
        }
    </script>
@endsection
