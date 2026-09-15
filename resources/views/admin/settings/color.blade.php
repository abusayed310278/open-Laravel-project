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
                            ৳ 29,999
                        </span>
                    </div>
                </div>

                {{-- Dynamic Color Swatch Bar --}}
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-2">Generated Brand Palette Preview</span>
                    <div id="palette-swatch-bar" class="grid grid-cols-5 sm:grid-cols-10 gap-1.5 rounded-lg overflow-hidden p-1 bg-white border border-gray-200">
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
                    <p class="text-xs text-gray-400 mt-2">Enter a 6-character hex color without '#'. All Tailwind brand scales (50 to 950) are auto-calculated.</p>
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
        function updateLiveElements(hex) {
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;

            document.getElementById('active-hex-badge').textContent = hex.toUpperCase();
            document.getElementById('preview-btn-primary').style.backgroundColor = hex;
            document.getElementById('preview-btn-outline').style.borderColor = hex;
            document.getElementById('preview-btn-outline').style.color = hex;
            document.getElementById('preview-btn-subtle').style.backgroundColor = hex + '1a';
            document.getElementById('preview-btn-subtle').style.color = hex;
            document.getElementById('preview-badge-status').style.backgroundColor = hex + '1f';
            document.getElementById('preview-badge-status').style.color = hex;
            document.getElementById('preview-tab-active').style.borderColor = hex;
            document.getElementById('preview-tab-active').style.color = hex;
            document.getElementById('preview-link').style.color = hex;
            document.getElementById('preview-price').style.color = hex;

            // Generate swatch steps
            const swatches = [
                { step: '50', opacity: '0.12' },
                { step: '100', opacity: '0.25' },
                { step: '200', opacity: '0.40' },
                { step: '300', opacity: '0.55' },
                { step: '400', opacity: '0.75' },
                { step: '500', opacity: '1.00' },
                { step: '600', opacity: '0.85' },
                { step: '700', opacity: '0.70' },
                { step: '800', opacity: '0.50' },
                { step: '900', opacity: '0.30' }
            ];

            let html = '';
            swatches.forEach(s => {
                html += `
                    <div class="text-center p-1 rounded">
                        <div class="h-6 rounded shadow-xs mb-1" style="background-color: ${hex}; opacity: ${s.opacity};"></div>
                        <span class="text-[9px] font-mono text-gray-400 block">${s.step}</span>
                    </div>
                `;
            });
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
