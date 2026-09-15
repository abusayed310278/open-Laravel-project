@extends('layouts.admin')

@section('title', 'Settings — Logo')

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
        {{-- Preview Banner --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Current Logo Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">How your brand logo appears on light and dark interfaces.</p>
                </div>
                @if ($logo)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Custom Logo Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Default Openbox Logo
                    </span>
                @endif
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                {{-- Light Mode Preview --}}
                <div class="border border-gray-200 rounded-lg p-4 bg-white">
                    <div class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-3 flex items-center justify-between">
                        <span>Light Background (Public Header / Navbar)</span>
                        <span class="text-gray-300">#FFFFFF</span>
                    </div>
                    <div class="h-20 bg-gray-50/60 rounded-md border border-dashed border-gray-200 flex items-center justify-between px-6">
                        <div id="preview-logo-light">
                            @if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="Current Logo" class="h-9 max-w-[180px] object-contain">
                            @else
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('buy-and-sale.png') }}" alt="Buy & Sell" class="h-9 w-auto object-contain">
                                    <img src="{{ asset('icon.png') }}" alt="Openbox" class="h-7 w-auto object-contain">
                                </div>
                            @endif
                        </div>
                        <div class="hidden sm:flex items-center gap-3 text-xs text-gray-400">
                            <span>Shop</span>
                            <span>Deals</span>
                            <span>Support</span>
                        </div>
                    </div>
                </div>

                {{-- Dark Mode Preview --}}
                <div class="border border-gray-800 rounded-lg p-4 bg-gray-950 text-white">
                    <div class="text-[11px] font-semibold tracking-wider text-gray-400 uppercase mb-3 flex items-center justify-between">
                        <span>Dark Background (Footer / Banners)</span>
                        <span class="text-gray-500">#030712</span>
                    </div>
                    <div class="h-20 bg-gray-900/80 rounded-md border border-dashed border-gray-800 flex items-center justify-between px-6">
                        <div id="preview-logo-dark">
                            @if ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="Current Logo" class="h-9 max-w-[180px] object-contain">
                            @else
                                <div class="flex items-center gap-2">
                                    <img src="{{ asset('buy-and-sale.png') }}" alt="Buy & Sell" class="h-9 w-auto object-contain">
                                    <img src="{{ asset('icon.png') }}" alt="Openbox" class="h-7 w-auto object-contain">
                                </div>
                            @endif
                        </div>
                        <div class="hidden sm:flex items-center gap-3 text-xs text-gray-500">
                            <span>Privacy</span>
                            <span>Terms</span>
                            <span>© {{ date('Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Upload New Logo</h2>
            <p class="text-xs text-gray-500 mb-6">Replace the site logo across the entire marketplace platform.</p>

            <form method="POST" action="{{ route('admin.settings.logo.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Logo File</label>
                    <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                        <input
                            type="file"
                            name="logo"
                            id="logo-input"
                            accept="image/png,image/svg+xml,image/jpeg,image/webp"
                            required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="handleLogoChange(event)"
                        >
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-800 mb-1" id="file-chosen-text">
                                <span class="text-brand-600 font-semibold underline">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-400">PNG, SVG, WEBP, or JPG up to 2MB</p>
                        </div>
                    </div>
                </div>

                {{-- Dimension Guidelines --}}
                <div class="rounded-lg bg-blue-50/60 border border-blue-100 p-4 text-xs text-blue-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-semibold">Recommended Specifications:</span>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-700/90">
                            <li>Format: Transparent <strong>PNG</strong> or scalable vector <strong>SVG</strong> for crystal clear rendering.</li>
                            <li>Optimal Dimensions: Height between <strong>36px – 50px</strong>; Width up to <strong>240px</strong>.</li>
                            <li>Max file size: <strong>2 MB</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Logo
                    </x-button>

                    @if ($logo)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to reset to the default Openbox logo?')) document.getElementById('remove-logo-form').submit();"
                            class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline px-3 py-2"
                        >
                            Reset to Default Logo
                        </button>
                    @endif
                </div>
            </form>

            @if ($logo)
                <form id="remove-logo-form" method="POST" action="{{ route('admin.settings.logo.remove') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <script>
        function handleLogoChange(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('file-chosen-text').innerHTML = `Selected: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgHtml = `<img src="${e.target.result}" alt="Preview" class="h-9 max-w-[180px] object-contain">`;
                    document.getElementById('preview-logo-light').innerHTML = imgHtml;
                    document.getElementById('preview-logo-dark').innerHTML = imgHtml;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
