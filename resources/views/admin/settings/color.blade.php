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
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">Current Hex:</span>
                    <span id="active-hex-badge" class="px-2.5 py-1 rounded-full text-xs font-mono font-bold text-gray-800 bg-gray-100 border border-gray-200">
                        {{ $currentColor }}
                    </span>
                </div>
            </div>

            {{-- Interactive Component Canvas --}}
            <div class="p-6 rounded-xl border border-gray-200 bg-gray-50/60 space-y-6">
                {{-- Button Styles --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Interactive Buttons</span>
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            type="button"
                            id="preview-btn-primary"
                            class="px-5 py-2.5 rounded-lg text-white text-xs font-semibold shadow-sm transition-all"
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
                                class="px-3 py-1.5 rounded-md text-white text-xs font-semibold shadow-xs transition-all pointer-events-none"
                                style="background-color: {{ $currentColor }};"
                            >
                                Search
                            </button>
                            <button
                                type="button"
                                id="preview-search-adv"
                                class="w-7 h-7 flex items-center justify-center rounded-md text-white shadow-xs transition-all pointer-events-none"
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
                                onclick="setColor('{{ $p['hex'] }}')"
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

                {{-- Custom Hex / Color Input --}}
                <div class="pt-4 border-t border-gray-100">
                    <label for="brand_color_hex" class="block text-sm font-medium text-gray-700 mb-2">Custom Hex Color Code</label>
                    <div class="flex items-center gap-3 max-w-md">
                        <div class="relative w-12 h-12 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0">
                            <input
                                type="color"
                                id="color-wheel"
                                value="{{ $currentColor }}"
                                oninput="syncColorFromWheel(this.value)"
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
                                oninput="syncColorFromInput(this.value)"
                                placeholder="f59e0b"
                                maxlength="7"
                                required
                                class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-3 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent shadow-xs"
                            >
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">Enter a 6-character hex color without '#'. All Tailwind brand scales (50 to 900) are auto-calculated and selectable above.</p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Brand Color
                    </x-button>

                    <button
                        type="button"
                        onclick="setColor('#f59e0b')"
                        class="text-xs font-medium text-gray-500 hover:text-gray-700 underline"
                    >
                        Reset to Openbox Amber (#f59e0b)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
            const [h, s, l] = hexToHsl(hex);
            const lightness = {
                '50': 97,
                '100': 93,
                '200': 85,
                '300': 74,
                '400': 60,
                '500': Math.max(35, Math.min(55, l)),
                '600': 42,
                '700': 33,
                '800': 26,
                '900': 20
            };

            const scale = {};
            for (const [step, targetL] of Object.entries(lightness)) {
                scale[step] = hslToHex(h, s, targetL);
            }
            return scale;
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

        function updateLiveElements(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;

            const contrastColor = getContrastText(hex);

            document.getElementById('active-hex-badge').textContent = hex.toUpperCase();

            const primaryBtn = document.getElementById('preview-btn-primary');
            primaryBtn.style.backgroundColor = hex;
            primaryBtn.style.color = contrastColor;

            const outlineBtn = document.getElementById('preview-btn-outline');
            outlineBtn.style.borderColor = hex;
            outlineBtn.style.color = hex;

            const subtleBtn = document.getElementById('preview-btn-subtle');
            subtleBtn.style.backgroundColor = hex + '1a';
            subtleBtn.style.color = hex;

            const badgeStatus = document.getElementById('preview-badge-status');
            badgeStatus.style.backgroundColor = hex + '1f';
            badgeStatus.style.color = hex;

            const tabActive = document.getElementById('preview-tab-active');
            tabActive.style.borderColor = hex;
            tabActive.style.color = hex;

            document.getElementById('preview-link').style.color = hex;
            document.getElementById('preview-price').style.color = hex;

            const searchSubmit = document.getElementById('preview-search-submit');
            const searchAdv = document.getElementById('preview-search-adv');
            if (searchSubmit) {
                searchSubmit.style.backgroundColor = hex;
                searchSubmit.style.color = contrastColor;
            }
            if (searchAdv) {
                searchAdv.style.backgroundColor = hex;
                searchAdv.style.color = contrastColor;
            }

            // Generate full mathematical 50-900 tonal scale
            const scale = generateColorScale(hex);
            const currentNorm = hex.toLowerCase();

            let html = '';
            for (const [step, shadeHex] of Object.entries(scale)) {
                const isSelected = shadeHex.toLowerCase() === currentNorm;
                html += `
                    <button
                        type="button"
                        onclick="setColor('${shadeHex}')"
                        title="Click to select weight ${step} (${shadeHex.toUpperCase()}) as brand color"
                        class="flex flex-col items-center justify-between p-1.5 rounded-lg border transition-all hover:scale-105 hover:shadow-md group text-center cursor-pointer ${isSelected ? 'border-gray-900 ring-2 ring-gray-900/20 bg-gray-50 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'}"
                    >
                        <div class="w-full h-8 rounded-md mb-1.5 shadow-2xs relative flex items-center justify-center border border-black/5" style="background-color: ${shadeHex};">
                            ${isSelected ? `<span class="w-4 h-4 rounded-full bg-white/95 text-gray-900 flex items-center justify-center text-[9px] font-bold shadow-xs">✓</span>` : ''}
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="text-[11px] font-bold text-gray-800 leading-none">${step}</span>
                            ${step === '500' ? '<span class="text-[8px] font-bold px-1 py-0.5 rounded bg-amber-100 text-amber-800 leading-none">Base</span>' : ''}
                        </div>
                        <span class="text-[9px] font-mono text-gray-400 uppercase mt-0.5 leading-none">${shadeHex}</span>
                    </button>
                `;
            }
            document.getElementById('palette-swatch-bar').innerHTML = html;

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

        function setColor(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            document.getElementById('color-wheel').value = hex;
            document.getElementById('brand_color_hex').value = hex.replace('#', '');
            updateLiveElements(hex);
        }

        function syncColorFromWheel(hex) {
            document.getElementById('brand_color_hex').value = hex.replace('#', '');
            updateLiveElements(hex);
        }

        function syncColorFromInput(raw) {
            let clean = raw.trim().replace('#', '');
            if (clean.length === 6) {
                const hex = '#' + clean;
                document.getElementById('color-wheel').value = hex;
                updateLiveElements(hex);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateLiveElements('{{ $currentColor }}');
        });
    </script>
@endsection
