@extends('layouts.admin')

@section('title', 'Settings — Footer & Icon')

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
        {{-- Preview Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- 1. Public Website Footer Live Preview --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Public Website Footer Preview</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Live real-time preview of the brand bio and address on your public website.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            Live Preview
                        </span>
                    </div>

                    {{-- Public Footer Mockup --}}
                    <div id="live-footer-bg-preview" class="rounded-xl border border-slate-800 p-5 shadow-inner transition-colors duration-200" style="background-color: {{ $footerBgColor }}; color: #94a3b8;">
                        <div class="max-w-xs">
                            <div id="public-footer-logo-preview" class="mb-3.5 inline-block">
                                <x-brand-logo :dark="true" :footer="true" />
                            </div>
                            <p id="live-footer-desc" class="text-xs leading-relaxed text-gray-300 mb-3.5 transition-all">
                                {{ $footerDescription }}
                            </p>
                            <p id="live-footer-addr" class="text-xs text-gray-400 font-medium flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $footerAddress }}</span>
                            </p>
                        </div>

                        <div id="live-footer-bottom-preview" class="mt-4 pt-3 border-t border-slate-800 text-[11px] text-gray-500 flex items-center justify-between rounded px-2.5 py-1.5 transition-colors duration-200" style="background-color: {{ $footerBottomBgColor }};">
                            <span id="live-footer-copy">{!! $footerCopyright !!}</span>
                            <span class="text-gray-400">Public Site</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Dashboard Footer Preview Card --}}
            <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-base font-semibold text-gray-900">Dashboard Footer Preview</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Real-time preview of how your dynamic footer icon looks in the admin dashboard.</p>
                        </div>
                        @if ($footerIcon)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Custom Icon Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Default Icon
                            </span>
                        @endif
                    </div>

                    {{-- Admin Footer Mockup Bar --}}
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 shadow-2xs mt-4">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
                            <div class="flex items-center gap-3">
                                @php $resolvedFooterIconUrl = \App\Support\MediaUrl::resolve($footerIcon); @endphp
                                <div id="footer-icon-preview" class="h-7 w-auto flex items-center justify-center shrink-0">
                                    @if (!empty($resolvedFooterIconUrl))
                                        <img src="{{ $resolvedFooterIconUrl }}" alt="Footer Icon" class="h-7 w-auto max-h-7 object-contain">
                                    @else
                                        <img src="{{ asset('icon.png') }}" alt="Openbox" class="h-7 w-auto max-h-7 object-contain">
                                    @endif
                                </div>
                                <span class="font-medium text-gray-700">&copy; {{ date('Y') }} {{ config('app.name', 'Openbox') }}. All rights reserved.</span>
                            </div>
                            <div class="flex items-center gap-3 text-gray-400">
                                <span>Admin Dashboard</span>
                                <span>•</span>
                                <span>v1.0.0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unified Settings Form Card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 sm:p-7 shadow-xs">
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-base font-semibold text-gray-900">Edit Dynamic Footer Settings</h2>
                <p class="text-xs text-gray-500 mt-1">Configure dynamic text and branding for the website footer. Changes appear immediately on the public website.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.footericon.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Part 1: Dynamic Footer Text Fields --}}
                <div class="space-y-5">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Footer Dynamic Text (Highlighted in Screenshot)
                    </h3>

                    {{-- Description / Bio Text --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="footer-desc-input" class="block text-sm font-medium text-gray-800">
                                Footer Description / Bio Text
                            </label>
                            <button
                                type="button"
                                onclick="resetDefaultDescription()"
                                class="text-xs text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                            >
                                Reset to Default Description
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Descriptive paragraph shown directly below the logo in the website footer.</p>
                        <textarea
                            name="footer_description"
                            id="footer-desc-input"
                            rows="3"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 p-3 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="Enter footer description..."
                        >{{ old('footer_description', $footerDescription) }}</textarea>
                    </div>

                    {{-- Address / Location Text --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="footer-addr-input" class="block text-sm font-medium text-gray-800">
                                Footer Address / Location
                            </label>
                            <button
                                type="button"
                                onclick="resetDefaultAddress()"
                                class="text-xs text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                            >
                                Reset to Default Location
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Office city, region, or physical address text shown beneath the description.</p>
                        <input
                            type="text"
                            name="footer_address"
                            id="footer-addr-input"
                            value="{{ old('footer_address', $footerAddress) }}"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="e.g. Dhaka, Bangladesh"
                        >
                    </div>

                    {{-- Copyright Notice --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="footer-copy-input" class="block text-sm font-medium text-gray-800">
                                Footer Copyright Notice
                            </label>
                            <button
                                type="button"
                                onclick="resetDefaultCopyright()"
                                class="text-xs text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                            >
                                Reset to Default Copyright
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Notice shown in the bottom bar of the public website footer.</p>
                        <input
                            type="text"
                            name="footer_copyright"
                            id="footer-copy-input"
                            value="{{ old('footer_copyright', $footerCopyright) }}"
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                            placeholder="e.g. &copy; {{ date('Y') }} Openbox. All rights reserved."
                        >
                    </div>
                </div>

                {{-- Part 2: Footer Background Color --}}
                <div class="border-t border-gray-100 pt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4 4 4 0 014-4h1a1 1 0 001-1V6a3 3 0 013-3 3 3 0 013 3v2a1 1 0 001 1h1a4 4 0 014 4 4 4 0 01-4 4H7z"/>
                            </svg>
                            Footer Background Color
                        </h3>
                        <button
                            type="button"
                            onclick="resetDefaultColors()"
                            class="text-xs text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                        >
                            Reset to Default (#0c0f1d)
                        </button>
                    </div>
                    <p class="text-xs text-gray-500">Configure the background color for the entire website footer. Changes apply to the whole footer (navigation columns, app download bar, and bottom copyright bar).</p>

                    {{-- Main Unified Color Picker --}}
                    <div class="rounded-xl border border-gray-200/80 p-5 bg-gray-50/40">
                        <label for="footer-bg-color-picker" class="block text-sm font-semibold text-gray-800 mb-1">
                            Whole Footer Background Color
                        </label>
                        <p class="text-xs text-gray-500 mb-3.5">Select a color or enter a hex code to style the whole footer.</p>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="relative w-12 h-12 rounded-xl border border-gray-300 overflow-hidden shadow-2xs shrink-0 cursor-pointer">
                                    <input
                                        type="color"
                                        id="footer-bg-color-picker"
                                        value="{{ old('footer_bg_color', $footerBgColor) }}"
                                        class="absolute inset-0 w-[150%] h-[150%] -top-1/4 -left-1/4 cursor-pointer border-0 p-0"
                                        oninput="handleMainBgInput(this.value)"
                                    >
                                </div>
                                <input
                                    type="text"
                                    name="footer_bg_color"
                                    id="footer-bg-color-text"
                                    value="{{ old('footer_bg_color', $footerBgColor) }}"
                                    class="w-36 rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm font-mono text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 uppercase"
                                    placeholder="#102C54"
                                    maxlength="7"
                                    oninput="handleMainBgInput(this.value)"
                                >
                            </div>

                            <div class="text-xs text-gray-400 sm:border-l sm:border-gray-200 sm:pl-3">
                                Live preview updates automatically above. Click "Save Footer Settings" below to apply to the public site.
                            </div>
                        </div>

                        {{-- Optional checkbox for separate bottom bar color --}}
                        <div class="mt-4 pt-3.5 border-t border-gray-200/60">
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer">
                                <input
                                    type="checkbox"
                                    id="toggle-custom-bottom"
                                    class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer"
                                    onchange="toggleCustomBottomBar(this.checked)"
                                    {{ $footerBgColor !== $footerBottomBgColor ? 'checked' : '' }}
                                >
                                <span>Customize bottom copyright bar color separately (optional)</span>
                            </label>

                            <div id="custom-bottom-panel" class="mt-3 {{ $footerBgColor !== $footerBottomBgColor ? '' : 'hidden' }} space-y-2">
                                <div class="flex items-center gap-3">
                                    <div class="relative w-9 h-9 rounded-lg border border-gray-300 overflow-hidden shadow-2xs shrink-0 cursor-pointer">
                                        <input
                                            type="color"
                                            id="footer-bottom-bg-color-picker"
                                            value="{{ old('footer_bottom_bg_color', $footerBottomBgColor) }}"
                                            class="absolute inset-0 w-[150%] h-[150%] -top-1/4 -left-1/4 cursor-pointer border-0 p-0"
                                            oninput="handleBottomBgInput(this.value)"
                                        >
                                    </div>
                                    <input
                                        type="text"
                                        name="footer_bottom_bg_color"
                                        id="footer-bottom-bg-color-text"
                                        value="{{ old('footer_bottom_bg_color', $footerBottomBgColor) }}"
                                        class="w-36 rounded-xl border border-gray-200 px-3 py-2 text-xs font-mono text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 uppercase"
                                        placeholder="#102C54"
                                        maxlength="7"
                                        oninput="handleBottomBgInput(this.value)"
                                    >
                                    <button
                                        type="button"
                                        onclick="matchMainBgColor()"
                                        class="text-xs text-brand-600 hover:text-brand-700 hover:underline cursor-pointer"
                                    >
                                        Sync with whole footer
                                    </button>
                                </div>
                                <p class="text-[11px] text-gray-400">If unchecked, the bottom bar automatically uses the whole footer background color.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Popular Presets --}}
                    <div>
                        <span class="block text-xs font-semibold text-gray-600 mb-2">Popular Footer Color Presets (Applies to Whole Footer):</span>
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onclick="applyColorPreset('#102c54')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #102c54;"></span>
                                <span>Deep Navy Blue</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#0c0f1d')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #0c0f1d;"></span>
                                <span>Midnight Navy <span class="text-gray-400 text-[10px]">(Default)</span></span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#0f172a')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #0f172a;"></span>
                                <span>Slate Obsidian</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#111827')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #111827;"></span>
                                <span>Deep Charcoal</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#18181b')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #18181b;"></span>
                                <span>Zinc Dark</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#000000')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #000000;"></span>
                                <span>Pitch Black</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#1e1b4b')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #1e1b4b;"></span>
                                <span>Rich Indigo</span>
                            </button>
                            <button
                                type="button"
                                onclick="applyColorPreset('#062319')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:border-brand-400 text-xs font-medium text-gray-700 shadow-2xs hover:shadow-xs transition-all cursor-pointer group"
                            >
                                <span class="w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0" style="background-color: #062319;"></span>
                                <span>Dark Forest</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Part 3: Footer Icon / Logo Upload --}}
                <div class="border-t border-gray-100 pt-6 space-y-4">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Footer Icon / Logo Image
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Footer Icon Image (Optional)</label>
                        <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                            <input
                                type="file"
                                name="footer_icon"
                                id="footer-icon-input"
                                accept="image/png,image/svg+xml,image/jpeg,image/webp,image/x-icon"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="handleFooterIconChange(event)"
                            >
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-800 mb-1" id="footer-chosen-text">
                                    <span class="text-brand-600 font-semibold underline">Click to upload icon</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-400">PNG, SVG, WEBP, or JPG (Recommended height: 28-36px)</p>
                            </div>
                        </div>
                    </div>

                    {{-- Specs Box --}}
                    <div class="rounded-lg bg-amber-50/60 border border-amber-100 p-4 text-xs text-amber-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <span class="font-semibold">Footer Icon Recommendations:</span>
                            <ul class="list-disc list-inside mt-1 space-y-0.5 text-amber-700/90">
                                <li>Format: <strong>PNG</strong> or <strong>SVG</strong> with transparent background.</li>
                                <li>Optimal Dimensions: <strong>120 × 32 px</strong> or square <strong>32 × 32 px</strong>.</li>
                                <li>Maximum file size: <strong>2 MB</strong>.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Submit & Reset Action Bar --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <x-button type="submit" class="shadow-sm px-6 py-2.5">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Footer Settings
                    </x-button>

                    @if ($footerIcon)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to reset to the default footer icon?')) document.getElementById('remove-footericon-form').submit();"
                            class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline px-3 py-2 cursor-pointer"
                        >
                            Reset to Default Icon
                        </button>
                    @endif
                </div>
            </form>

            @if ($footerIcon)
                <form id="remove-footericon-form" method="POST" action="{{ route('admin.settings.footericon.remove') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <script>
        const defaultDesc = "Bangladesh's premier verified electronics marketplace. Every device inspected, graded, and warehoused for authentic peace of mind.";
        const defaultAddr = "Dhaka, Bangladesh";
        const defaultCopy = "© {{ date('Y') }} {{ config('app.name', 'Openbox') }}. All rights reserved.";
        const defaultMainBg = '#0c0f1d';
        const defaultBottomBg = '#05070f';

        const descInput = document.getElementById('footer-desc-input');
        const addrInput = document.getElementById('footer-addr-input');
        const copyInput = document.getElementById('footer-copy-input');

        const liveDesc = document.getElementById('live-footer-desc');
        const liveAddr = document.getElementById('live-footer-addr');
        const liveCopy = document.getElementById('live-footer-copy');

        const mainBgPicker = document.getElementById('footer-bg-color-picker');
        const mainBgText = document.getElementById('footer-bg-color-text');
        const bottomBgPicker = document.getElementById('footer-bottom-bg-color-picker');
        const bottomBgText = document.getElementById('footer-bottom-bg-color-text');

        const liveFooterBg = document.getElementById('live-footer-bg-preview');
        const liveFooterBottom = document.getElementById('live-footer-bottom-preview');

        function isValidHex(hex) {
            return /^#([0-9A-F]{3}){1,2}$/i.test(hex);
        }

        function toggleCustomBottomBar(checked) {
            const panel = document.getElementById('custom-bottom-panel');
            if (panel) {
                if (checked) {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                    matchMainBgColor();
                }
            }
        }

        function handleMainBgInput(val) {
            val = (val || '').trim();
            if (val && !val.startsWith('#') && /^[0-9A-F]{3,6}$/i.test(val)) {
                val = '#' + val;
            }
            if (mainBgText && mainBgText.value !== val) {
                mainBgText.value = val;
            }
            if (isValidHex(val)) {
                if (mainBgPicker) mainBgPicker.value = val;
                if (liveFooterBg) liveFooterBg.style.backgroundColor = val;

                const customBottomCheck = document.getElementById('toggle-custom-bottom');
                if (!customBottomCheck || !customBottomCheck.checked) {
                    setBottomBgColor(val);
                }
            }
        }

        function handleBottomBgInput(val) {
            val = (val || '').trim();
            if (val && !val.startsWith('#') && /^[0-9A-F]{3,6}$/i.test(val)) {
                val = '#' + val;
            }
            if (bottomBgText && bottomBgText.value !== val) {
                bottomBgText.value = val;
            }
            if (isValidHex(val)) {
                if (bottomBgPicker) bottomBgPicker.value = val;
                if (liveFooterBottom) liveFooterBottom.style.backgroundColor = val;
            }
        }

        function setMainBgColor(hex) {
            if (mainBgPicker) mainBgPicker.value = hex;
            if (mainBgText) mainBgText.value = hex;
            if (liveFooterBg) liveFooterBg.style.backgroundColor = hex;

            const customBottomCheck = document.getElementById('toggle-custom-bottom');
            if (!customBottomCheck || !customBottomCheck.checked) {
                setBottomBgColor(hex);
            }
        }

        function setBottomBgColor(hex) {
            if (bottomBgPicker) bottomBgPicker.value = hex;
            if (bottomBgText) bottomBgText.value = hex;
            if (liveFooterBottom) liveFooterBottom.style.backgroundColor = hex;
        }

        function matchMainBgColor() {
            const currentMain = (mainBgText ? mainBgText.value.trim() : '') || defaultMainBg;
            setBottomBgColor(isValidHex(currentMain) ? currentMain : defaultMainBg);
        }

        function resetDefaultColors() {
            setMainBgColor(defaultMainBg);
            setBottomBgColor(defaultMainBg);
            const customBottomCheck = document.getElementById('toggle-custom-bottom');
            if (customBottomCheck) {
                customBottomCheck.checked = false;
                toggleCustomBottomBar(false);
            }
        }

        function applyColorPreset(colorHex) {
            setMainBgColor(colorHex);
            setBottomBgColor(colorHex);
            const customBottomCheck = document.getElementById('toggle-custom-bottom');
            if (customBottomCheck) {
                customBottomCheck.checked = false;
                toggleCustomBottomBar(false);
            }
        }

        // Real-time dynamic typing preview
        if (descInput && liveDesc) {
            descInput.addEventListener('input', function() {
                liveDesc.textContent = this.value.trim() || defaultDesc;
            });
        }

        if (addrInput && liveAddr) {
            addrInput.addEventListener('input', function() {
                const span = liveAddr.querySelector('span');
                if (span) {
                    span.textContent = this.value.trim() || defaultAddr;
                }
            });
        }

        if (copyInput && liveCopy) {
            copyInput.addEventListener('input', function() {
                liveCopy.textContent = this.value.trim() || defaultCopy;
            });
        }

        function resetDefaultDescription() {
            if (descInput) {
                descInput.value = defaultDesc;
                if (liveDesc) liveDesc.textContent = defaultDesc;
            }
        }

        function resetDefaultAddress() {
            if (addrInput) {
                addrInput.value = defaultAddr;
                if (liveAddr) {
                    const span = liveAddr.querySelector('span');
                    if (span) span.textContent = defaultAddr;
                }
            }
        }

        function resetDefaultCopyright() {
            if (copyInput) {
                copyInput.value = defaultCopy;
                if (liveCopy) liveCopy.textContent = defaultCopy;
            }
        }

        function handleFooterIconChange(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('footer-chosen-text').innerHTML = `Selected: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const dataUrl = e.target.result;
                    const dashPreview = document.getElementById('footer-icon-preview');
                    if (dashPreview) {
                        dashPreview.innerHTML = `<img src="${dataUrl}" class="h-7 w-auto max-h-7 object-contain">`;
                    }
                    const publicPreview = document.getElementById('public-footer-logo-preview');
                    if (publicPreview) {
                        publicPreview.innerHTML = `<img src="${dataUrl}" class="h-10 sm:h-12 w-auto max-h-12 object-contain">`;
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
