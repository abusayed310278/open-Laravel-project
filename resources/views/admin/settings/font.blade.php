@extends('layouts.admin')

@section('title', 'Settings — Typography & Font')

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
        {{-- Live Typography Playground --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Live Typography Playground</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Test and preview how your selected font renders across headlines, body copy, and UI controls.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">Current Font:</span>
                    <span id="active-font-badge" class="px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                        {{ $currentFont }}
                    </span>
                </div>
            </div>

            {{-- Playground Canvas --}}
            <div id="font-sandbox" class="p-6 rounded-xl border border-gray-200 bg-gray-50/60 transition-all duration-300" style="font-family: '{{ $currentFont }}', sans-serif;">
                <div class="space-y-4">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-brand-600 block mb-1">Headline 1 (Bold 700)</span>
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                            Discover Certified &amp; Authenticated Tech on Openbox
                        </h1>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Headline 2 (Semi-Bold 600)</span>
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">
                            Grade A &amp; B Inspected Electronics with 100% Buyer Protection
                        </h2>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 block mb-1">Body Text (Regular 400)</span>
                        <p class="text-sm text-gray-600 leading-relaxed max-w-3xl">
                            Every refurbished laptop, smartphone, and openbox gadget undergoes our rigorous 25-point physical condition check, motherboard diagnostic, and battery health report before arriving at warehouse custody.
                        </p>
                    </div>

                    {{-- Sample UI Components --}}
                    <div class="pt-4 border-t border-gray-200 flex flex-wrap items-center gap-3">
                        <span class="text-xl font-bold text-brand-600">৳ 45,900</span>
                        <button type="button" class="px-4 py-2 rounded-lg bg-brand-500 text-white text-xs font-semibold shadow-xs">
                            Add to Cart
                        </button>
                        <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 text-xs font-semibold hover:bg-gray-50">
                            View Inspection Report
                        </button>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✓ Grade A Certified
                        </span>
                        <span class="text-xs text-gray-400 font-mono">SKU: OPB-9024-X</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Font Selection Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Choose Site Typography</h2>
            <p class="text-xs text-gray-500 mb-6">Select a Google Font to apply site-wide to public pages, vendor portals, and the admin console.</p>

            <form method="POST" action="{{ route('admin.settings.font.update') }}" class="space-y-6">
                @csrf

                {{-- Visual Font Cards Grid --}}
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @php
                        $fontDescriptions = [
                            'Montserrat' => 'Modern geometric sans with high impact (Default)',
                            'Inter' => 'Ultra-crisp, optimized for digital screens & apps',
                            'Roboto' => 'Clean, accessible, and balanced everyday font',
                            'Poppins' => 'Geometric with playful curved terminals',
                            'Outfit' => 'Trendy luxury tech display sans',
                            'Plus Jakarta Sans' => 'Sharp modern neo-grotesque for SaaS',
                            'Nunito Sans' => 'Soft, rounded, and welcoming readability',
                            'Source Sans 3' => 'Adobe professional editorial typeface',
                            'Manrope' => 'Compact contemporary European design',
                            'Work Sans' => 'Optimized for legibility and UI clarity',
                        ];
                    @endphp

                    @foreach ($fonts as $font)
                        <label
                            class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 hover:border-brand-400 group bg-gray-50/40 hover:bg-white font-option-card"
                            data-font="{{ $font }}"
                            onclick="applyFontPreview('{{ $font }}')"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-bold text-gray-900 group-hover:text-brand-600">{{ $font }}</span>
                                <input
                                    type="radio"
                                    name="brand_font"
                                    value="{{ $font }}"
                                    @checked($currentFont === $font)
                                    class="w-4 h-4 text-brand-600 border-gray-300 focus:ring-brand-400"
                                >
                            </div>
                            <p class="text-[11px] text-gray-500 mb-3">{{ $fontDescriptions[$font] ?? 'Google Web Font' }}</p>
                            <div class="mt-auto text-base font-semibold text-gray-800" style="font-family: '{{ $font }}', sans-serif;">
                                Aa Bb Gg 123
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Font Setting
                    </x-button>

                    <span class="text-xs text-gray-400">
                        Automatically optimized &amp; preloaded via Google Fonts CDN
                    </span>
                </div>
            </form>
        </div>
    </div>

    {{-- Dynamically load all font previews --}}
    @push('head')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Manrope:wght@400;600;700&family=Montserrat:wght@400;600;700&family=Nunito+Sans:wght@400;600;700&family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;600;700&family=Poppins:wght@400;600;700&family=Roboto:wght@400;600;700&family=Source+Sans+3:wght@400;600;700&family=Work+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @endpush

    <script>
        function applyFontPreview(fontName) {
            const sandbox = document.getElementById('font-sandbox');
            sandbox.style.fontFamily = `'${fontName}', sans-serif`;
            document.getElementById('active-font-badge').textContent = fontName;

            // Highlight selected card
            document.querySelectorAll('.font-option-card').forEach(card => {
                if (card.dataset.font === fontName) {
                    card.classList.add('border-brand-500', 'bg-brand-50/20');
                    card.classList.remove('border-gray-200');
                    const radio = card.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                } else {
                    card.classList.remove('border-brand-500', 'bg-brand-50/20');
                    card.classList.add('border-gray-200');
                }
            });
        }

        // Initialize active card on page load
        document.addEventListener('DOMContentLoaded', () => {
            applyFontPreview('{{ $currentFont }}');
        });
    </script>
@endsection
