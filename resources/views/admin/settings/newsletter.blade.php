@extends('layouts.admin')

@section('title', 'Settings — Newsletter')

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
        {{-- Live Banner Preview Card --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Newsletter Banner Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Real-time preview of how the Stay Updated banner and subscriber button appear on the homepage.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Preview
                    </span>
                </div>
            </div>

            {{-- Mock Banner Preview Box --}}
            <div class="rounded-xl px-6 sm:px-10 py-6 flex flex-col lg:flex-row lg:items-center justify-between gap-5 lg:gap-8 transition-all shadow-sm"
                 style="background: linear-gradient(to right, #ffd814, #ffe87a); border: 1px solid rgba(0,0,0,0.06);">
                <div class="flex items-center gap-4 lg:w-5/12">
                    <div class="w-11 h-11 rounded-lg bg-black/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-gray-950" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm0 0l9 6 9-6" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="preview-title" class="text-lg font-extrabold text-gray-950 leading-tight">
                            {{ old('title', $content['title']) }}
                        </h3>
                        <p id="preview-text" class="text-xs text-gray-900/80 mt-0.5">
                            {{ old('text', $content['text']) }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-1 flex-col sm:flex-row gap-3 max-w-xl">
                    <div class="flex-1 bg-white border border-gray-300 rounded-md px-4 py-2.5 text-sm text-gray-500 flex items-center select-none shadow-2xs">
                        <span id="preview-placeholder">{{ old('placeholder', $content['placeholder']) }}</span>
                    </div>
                    <button
                        type="button"
                        id="preview-button"
                        class="font-bold px-8 py-2.5 rounded-md text-sm transition-all duration-150 shadow-sm shrink-0 flex items-center justify-center cursor-default"
                        style="color: {{ old('button_text_color', $content['button_text_color'] ?? '#ffffff') }}; background-color: {{ old('button_bg_color', $content['button_bg_color'] ?? '#090d16') }};"
                    >
                        <span id="preview-button-label">{{ old('button', $content['button']) }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Configuration Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Banner &amp; Subscriber Button Settings</h2>
            <p class="text-xs text-gray-500 mb-6">Customize the banner content and configure dynamic text &amp; background colors for the subscriber button.</p>

            <form method="POST" action="{{ route('admin.settings.newsletter.update') }}" class="space-y-6">
                @csrf

                {{-- Row 1: Copywriting --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="nl-title">Heading</label>
                        <input
                            id="nl-title"
                            type="text"
                            name="title"
                            value="{{ old('title', $content['title']) }}"
                            maxlength="80"
                            required
                            oninput="updatePreviewTitle(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">Main callout heading (e.g., "Stay Updated").</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="nl-text">Subtext</label>
                        <input
                            id="nl-text"
                            type="text"
                            name="text"
                            value="{{ old('text', $content['text']) }}"
                            maxlength="160"
                            required
                            oninput="updatePreviewText(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">Subheading or value proposition under the heading.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="nl-placeholder">Email Field Placeholder</label>
                        <input
                            id="nl-placeholder"
                            type="text"
                            name="placeholder"
                            value="{{ old('placeholder', $content['placeholder']) }}"
                            maxlength="60"
                            required
                            oninput="updatePreviewPlaceholder(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">Placeholder text in the email address field.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5" for="nl-button">Subscriber Button Label</label>
                        <input
                            id="nl-button"
                            type="text"
                            name="button"
                            value="{{ old('button', $content['button']) }}"
                            maxlength="30"
                            required
                            oninput="updatePreviewButtonLabel(this.value)"
                            class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-white shadow-2xs"
                        >
                        <p class="text-[11px] text-gray-400 mt-1">Text shown inside the submit button (e.g., "Subscribe").</p>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-gray-100 pt-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-1">Subscriber Button Color Customization</h3>
                    <p class="text-xs text-gray-500 mb-4">Set dynamic text color and background color for the newsletter subscribe button with real-time feedback.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- 1. Subscriber Button Text Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <label for="button_text_color" class="block text-xs font-semibold uppercase tracking-wider text-gray-700">
                                    Subscriber Text Color
                                </label>
                                <span class="text-[11px] font-mono font-medium text-gray-500" id="badge-text-hex">
                                    {{ old('button_text_color', $content['button_text_color'] ?? '#ffffff') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                    <input
                                        type="color"
                                        id="button-text-color-wheel"
                                        value="{{ old('button_text_color', $content['button_text_color'] ?? '#ffffff') }}"
                                        oninput="syncButtonTextColorFromWheel(this.value)"
                                        class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                    >
                                </div>
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                    <input
                                        type="text"
                                        name="button_text_color"
                                        id="button_text_color"
                                        value="{{ ltrim(old('button_text_color', $content['button_text_color'] ?? '#ffffff'), '#') }}"
                                        oninput="syncButtonTextColorFromInput(this.value)"
                                        placeholder="ffffff"
                                        maxlength="7"
                                        class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-2xs"
                                    >
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-[11px] text-gray-400 mr-1">Presets:</span>
                                <button type="button" onclick="setButtonTextColor('#ffffff')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#ffffff (White)</button>
                                <button type="button" onclick="setButtonTextColor('#000000')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#000000 (Black)</button>
                                <button type="button" onclick="setButtonTextColor('#fef08a')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#fef08a (Yellow)</button>
                                <button type="button" onclick="setButtonTextColor('#d32222')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#d32222 (Red)</button>
                                <button type="button" onclick="setButtonTextColor('#38bdf8')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#38bdf8 (Sky)</button>
                            </div>
                        </div>

                        {{-- 2. Subscriber Button Background Color --}}
                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <label for="button_bg_color" class="block text-xs font-semibold uppercase tracking-wider text-gray-700">
                                    Subscriber Button Background
                                </label>
                                <span class="text-[11px] font-mono font-medium text-gray-500" id="badge-bg-hex">
                                    {{ old('button_bg_color', $content['button_bg_color'] ?? '#090d16') }}
                                </span>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="relative w-11 h-11 rounded-xl border border-gray-200 overflow-hidden shadow-xs shrink-0 bg-white">
                                    <input
                                        type="color"
                                        id="button-bg-color-wheel"
                                        value="{{ old('button_bg_color', $content['button_bg_color'] ?? '#090d16') }}"
                                        oninput="syncButtonBgColorFromWheel(this.value)"
                                        class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer border-0"
                                    >
                                </div>
                                <div class="relative flex-1">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 font-mono text-sm">#</span>
                                    <input
                                        type="text"
                                        name="button_bg_color"
                                        id="button_bg_color"
                                        value="{{ ltrim(old('button_bg_color', $content['button_bg_color'] ?? '#090d16'), '#') }}"
                                        oninput="syncButtonBgColorFromInput(this.value)"
                                        placeholder="090d16"
                                        maxlength="7"
                                        class="w-full border border-gray-200 rounded-lg pl-8 pr-4 py-2 text-sm font-mono font-semibold uppercase text-gray-900 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent bg-white shadow-2xs"
                                    >
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-[11px] text-gray-400 mr-1">Presets:</span>
                                <button type="button" onclick="setButtonBgColor('#090d16')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#090d16 (Default)</button>
                                <button type="button" onclick="setButtonBgColor('#000000')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#000000 (Black)</button>
                                <button type="button" onclick="setButtonBgColor('#2563eb')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#2563eb (Blue)</button>
                                <button type="button" onclick="setButtonBgColor('#d32222')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#d32222 (Red)</button>
                                <button type="button" onclick="setButtonBgColor('#ffffff')" class="px-2 py-0.5 rounded text-[11px] font-mono border border-gray-200 bg-white hover:bg-gray-100 cursor-pointer shadow-2xs">#ffffff (White)</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Action --}}
                <div class="pt-2 flex items-center gap-3">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Newsletter Settings
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updatePreviewTitle(val) {
            document.getElementById('preview-title').textContent = val || 'Stay Updated';
        }

        function updatePreviewText(val) {
            document.getElementById('preview-text').textContent = val || 'Get the latest deals and new arrivals.';
        }

        function updatePreviewPlaceholder(val) {
            document.getElementById('preview-placeholder').textContent = val || 'Your email address';
        }

        function updatePreviewButtonLabel(val) {
            document.getElementById('preview-button-label').textContent = val || 'Subscribe';
        }

        // Subscriber Text Color sync
        function syncButtonTextColorFromWheel(color) {
            const cleanHex = color.replace(/^#/, '');
            document.getElementById('button_text_color').value = cleanHex;
            applyButtonTextColor(color);
        }

        function syncButtonTextColorFromInput(val) {
            let clean = val.replace(/[^0-9a-fA-F]/g, '');
            if (clean.length === 6 || clean.length === 3) {
                const hex = '#' + clean;
                document.getElementById('button-text-color-wheel').value = (clean.length === 6) ? hex : '#' + clean[0] + clean[0] + clean[1] + clean[1] + clean[2] + clean[2];
                applyButtonTextColor(hex);
            }
        }

        function setButtonTextColor(hex) {
            const clean = hex.replace(/^#/, '');
            document.getElementById('button_text_color').value = clean;
            document.getElementById('button-text-color-wheel').value = hex;
            applyButtonTextColor(hex);
        }

        function applyButtonTextColor(hex) {
            const previewBtn = document.getElementById('preview-button');
            if (previewBtn) {
                previewBtn.style.color = hex;
            }
            const badge = document.getElementById('badge-text-hex');
            if (badge) {
                badge.textContent = hex.startsWith('#') ? hex : '#' + hex;
            }
        }

        // Subscriber Button Background Color sync
        function syncButtonBgColorFromWheel(color) {
            const cleanHex = color.replace(/^#/, '');
            document.getElementById('button_bg_color').value = cleanHex;
            applyButtonBgColor(color);
        }

        function syncButtonBgColorFromInput(val) {
            let clean = val.replace(/[^0-9a-fA-F]/g, '');
            if (clean.length === 6 || clean.length === 3) {
                const hex = '#' + clean;
                document.getElementById('button-bg-color-wheel').value = (clean.length === 6) ? hex : '#' + clean[0] + clean[0] + clean[1] + clean[1] + clean[2] + clean[2];
                applyButtonBgColor(hex);
            }
        }

        function setButtonBgColor(hex) {
            const clean = hex.replace(/^#/, '');
            document.getElementById('button_bg_color').value = clean;
            document.getElementById('button-bg-color-wheel').value = hex;
            applyButtonBgColor(hex);
        }

        function applyButtonBgColor(hex) {
            const previewBtn = document.getElementById('preview-button');
            if (previewBtn) {
                previewBtn.style.backgroundColor = hex;
            }
            const badge = document.getElementById('badge-bg-hex');
            if (badge) {
                badge.textContent = hex.startsWith('#') ? hex : '#' + hex;
            }
        }
    </script>
@endsection
