@extends('layouts.admin')

@section('title', 'Settings — Banner')

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
                    <h2 class="text-base font-semibold text-gray-900">Current Banner / Cover Image Preview</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Used as the store header banner across web and mobile app store views.</p>
                </div>
                @if ($banner)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Custom Banner Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Default Banner / Cover
                    </span>
                @endif
            </div>

            @php
                $resolvedBannerUrl = $banner ? \App\Support\MediaUrl::resolve($banner) : (file_exists(public_path('banner image.png')) ? asset('banner image.png') : null);
            @endphp

            <div class="space-y-4">
                {{-- Storefront Desktop Mockup Preview --}}
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-xs">
                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-[11px] font-semibold tracking-wider text-gray-500 uppercase flex items-center justify-between">
                        <span>Storefront Banner Preview (Desktop & Web)</span>
                        <span class="text-gray-400">Recommended 1920 × 600</span>
                    </div>
                    <div id="preview-banner-container" class="relative h-48 sm:h-64 w-full bg-gradient-to-r from-brand-600 to-amber-500 overflow-hidden flex items-end p-6">
                        @if (!empty($resolvedBannerUrl))
                            <img id="preview-banner-img" src="{{ $resolvedBannerUrl }}" alt="Current Banner" class="absolute inset-0 w-full h-full object-cover">
                        @else
                            <div id="preview-banner-placeholder" class="absolute inset-0 flex items-center justify-center text-white/30 text-sm font-medium">
                                No custom banner set (Gradient default)
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                        <div class="relative z-10 flex items-center gap-4 text-white">
                            <div class="w-14 h-14 rounded-full bg-white p-1 shadow-md shrink-0 flex items-center justify-center overflow-hidden">
                                <img src="{{ \App\Support\MediaUrl::resolve(setting('brand_logo')) ?: asset('icon.png') }}" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <div class="text-lg font-bold flex items-center gap-1.5">
                                    {{ config('app.name', 'Openbox') }} Official Store
                                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="text-xs text-white/80">Verified Marketplace Headquarters · All items physically inspected</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile App Store Header Mockup Preview --}}
                <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-xs max-w-md">
                    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200 text-[11px] font-semibold tracking-wider text-gray-500 uppercase flex items-center justify-between">
                        <span>Mobile App Store Header</span>
                        <span class="text-gray-400">Mobile View</span>
                    </div>
                    <div id="preview-mobile-container" class="relative h-36 w-full bg-gradient-to-r from-brand-600 to-amber-500 overflow-hidden flex items-end p-4">
                        @if (!empty($resolvedBannerUrl))
                            <img id="preview-mobile-img" src="{{ $resolvedBannerUrl }}" alt="Current Banner" class="absolute inset-0 w-full h-full object-cover">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                        <div class="relative z-10 flex items-center gap-3 text-white">
                            <div class="w-10 h-10 rounded-full bg-white p-1 shadow-md shrink-0 flex items-center justify-center overflow-hidden">
                                <img src="{{ \App\Support\MediaUrl::resolve(setting('brand_logo')) ?: asset('icon.png') }}" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <div class="text-sm font-bold leading-tight">{{ config('app.name', 'Openbox') }} Admin</div>
                                <div class="text-[11px] text-white/80">Member since 2026</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Upload Form --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Upload New Banner / Cover Image</h2>
            <p class="text-xs text-gray-500 mb-6">Upload an official store cover banner displayed on the mobile app store page and web storefronts.</p>

            <form method="POST" action="{{ route('admin.settings.banner.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Banner Image</label>
                    <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                        <input
                            type="file"
                            name="banner"
                            id="banner-input"
                            accept="image/png,image/jpeg,image/webp"
                            required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            onchange="handleBannerChange(event)"
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
                            <p class="text-xs text-gray-400">WEBP, PNG, or JPG up to 5MB</p>
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
                            <li>Resolution: <strong>1920 × 600 px</strong> or <strong>1200 × 400 px</strong> (wide landscape ratio ~3:1).</li>
                            <li>Format: <strong>WEBP</strong>, <strong>JPG</strong>, or <strong>PNG</strong>.</li>
                            <li>Max file size: <strong>5 MB</strong>.</li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <x-button type="submit" class="shadow-sm">
                        <svg class="w-4 h-4 mr-1.5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Banner
                    </x-button>

                    @if ($banner)
                        <button
                            type="button"
                            onclick="if(confirm('Are you sure you want to reset to the default banner?')) document.getElementById('remove-banner-form').submit();"
                            class="text-xs font-medium text-red-600 hover:text-red-700 hover:underline px-3 py-2"
                        >
                            Reset to Default Banner
                        </button>
                    @endif
                </div>
            </form>

            @if ($banner)
                <form id="remove-banner-form" method="POST" action="{{ route('admin.settings.banner.remove') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>

    <script>
        function handleBannerChange(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const file = input.files[0];
                document.getElementById('file-chosen-text').innerHTML = `Selected: <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)`;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const result = e.target.result;
                    
                    let desktopImg = document.getElementById('preview-banner-img');
                    if (!desktopImg) {
                        desktopImg = document.createElement('img');
                        desktopImg.id = 'preview-banner-img';
                        desktopImg.className = 'absolute inset-0 w-full h-full object-cover';
                        const container = document.getElementById('preview-banner-container');
                        const placeholder = document.getElementById('preview-banner-placeholder');
                        if (placeholder) placeholder.remove();
                        container.insertBefore(desktopImg, container.firstChild);
                    }
                    desktopImg.src = result;

                    let mobileImg = document.getElementById('preview-mobile-img');
                    if (!mobileImg) {
                        mobileImg = document.createElement('img');
                        mobileImg.id = 'preview-mobile-img';
                        mobileImg.className = 'absolute inset-0 w-full h-full object-cover';
                        const mContainer = document.getElementById('preview-mobile-container');
                        mContainer.insertBefore(mobileImg, mContainer.firstChild);
                    }
                    mobileImg.src = result;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
