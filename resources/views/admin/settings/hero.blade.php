@extends('layouts.admin')

@section('title', 'Settings — Hero')

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
        {{-- Hero Slides --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Homepage Hero Slides</h2>
            <p class="text-xs text-gray-500 mb-4">Upload several images (up to 10 at a time). They rotate as a slider at the top of the homepage, and clicking a slide opens the product you assign to it. When at least one active slide exists, it replaces the single hero image and hero text. Recommended wide landscape (~3.5:1), e.g. 1920 × 550 px.</p>

            <form method="POST" action="{{ route('admin.settings.hero-slides.store') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
                @csrf
                <input type="file" name="slides[]" accept="image/png,image/jpeg,image/webp" multiple required class="text-sm text-gray-600 flex-1">
                <x-button type="submit" class="shadow-sm">Upload Slides</x-button>
            </form>

            @forelse ($heroSlides as $slide)
                <div class="border border-gray-100 rounded-xl p-3 mb-3" style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;">
                    <img src="{{ $slide->imageUrl() }}" alt="Hero slide {{ $loop->iteration }}" class="rounded-lg border border-gray-200" style="width:200px;height:72px;object-fit:cover;flex-shrink:0;">

                    <div style="flex:1;min-width:200px;">
                        <p class="text-xs text-gray-500 mb-0.5">Links to product</p>
                        <p class="text-sm font-medium text-gray-900">{{ $slide->product?->title ?? '— No link —' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500">Order {{ $slide->sort_order }} · {{ $slide->is_active ? 'Active' : 'Hidden' }}</span>
                            @if ($slide->enable_overlay)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-brand-50 text-brand-700 border border-brand-200">
                                    Text Overlay Active
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="flex-shrink:0;">
                        <button type="button" data-modal-open="edit-slide-modal-{{ $slide->id }}" title="Edit slide" aria-label="Edit slide" style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;cursor:pointer;" class="bg-brand-50 text-brand-600 hover:bg-brand-100 transition">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.768-6.768a2 2 0 112.828 2.828L11.828 15.83a2 2 0 01-.89.514L7 17l.656-3.938A2 2 0 018.172 12.17L9 13z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14"/></svg>
                        </button>
                    </div>

                    <x-modal id="edit-slide-modal-{{ $slide->id }}" title="Edit slide" maxWidth="max-w-4xl">
                        <form method="POST" action="{{ route('admin.settings.hero-slides.update', $slide) }}" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            @method('PUT')

                            <div class="relative rounded-lg overflow-hidden border border-gray-200 bg-gray-900/5">
                                <img src="{{ $slide->imageUrl() }}" alt="Current slide image" class="w-full object-cover" style="max-height:180px;">
                                <div class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded font-medium backdrop-blur-xs">
                                    Current Image · Aspect ratio ~3.5:1
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Replace image (optional)</label>
                                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="text-xs text-gray-600 w-full file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 hover:file:bg-gray-200">
                                </div>

                                <div class="sm:col-span-1">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Links to product</label>
                                    <select name="product_id" class="w-full border border-gray-200 rounded-md px-3 py-1.5 text-xs">
                                        <option value="">— No link —</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected($slide->product_id === $product->id)>{{ $product->title }}</option>
                                        @endforeach
                                        @if ($slide->product && ! $products->contains('id', $slide->product_id))
                                            <option value="{{ $slide->product_id }}" selected>{{ $slide->product->title }} (not live)</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="sm:col-span-1 flex items-center gap-4">
                                    <div style="width:90px;">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Order</label>
                                        <input type="number" name="sort_order" min="0" value="{{ $slide->sort_order }}" class="w-full border border-gray-200 rounded-md px-2.5 py-1.5 text-xs">
                                    </div>
                                    <label class="flex items-center gap-2 text-xs font-medium text-gray-700 mt-4 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" @checked($slide->is_active) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"> Active
                                    </label>
                                </div>
                            </div>

                            {{-- Overlay & Content Settings --}}
                            <div class="border border-brand-200 bg-brand-50/30 rounded-xl p-3.5">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="checkbox" name="enable_overlay" value="1" @checked($slide->enable_overlay) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4">
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900">Enable Dynamic Text, Badges & Callout Overlay</span>
                                        <p class="text-[11px] text-gray-500">Render customizable badges, headlines, subtext, colors, sticker callout, and 3 feature badges dynamically over this background image.</p>
                                    </div>
                                </label>
                            </div>

                            {{-- Top Badge --}}
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/40 space-y-3">
                                <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    Top Pill Badge
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Badge Text</label>
                                        <input type="text" name="badge_text" value="{{ old('badge_text', $slide->badge_text) }}" placeholder="e.g. PREMIUM OPEN-BOX ELECTRONICS" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-brand-400">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Font Size (px)</label>
                                        <div class="flex items-center border border-gray-200 rounded-lg bg-white overflow-hidden focus-within:ring-2 focus-within:ring-brand-400">
                                            <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||8, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||11)-1); i.dispatchEvent(new Event('input'))" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-bold select-none cursor-pointer border-r border-gray-200">−</button>
                                            <input type="number" name="badge_font_size" min="8" max="60" value="{{ old('badge_font_size', $slide->badge_font_size) }}" placeholder="11" class="w-full text-center border-0 py-1 px-1 text-xs font-semibold focus:ring-0 focus:outline-hidden">
                                            <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||60, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||11)+1); i.dispatchEvent(new Event('input'))" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-bold select-none cursor-pointer border-l border-gray-200">+</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Badge Background Color</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" value="{{ old('badge_bg_color', $slide->badge_bg_color ?: '#FEF08A') }}" oninput="this.nextElementSibling.value = this.value" class="w-7 h-7 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                            <input type="text" name="badge_bg_color" value="{{ old('badge_bg_color', $slide->badge_bg_color ?: '#FEF08A') }}" oninput="this.previousElementSibling.value = this.value" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Badge Text Color</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" value="{{ old('badge_text_color', $slide->badge_text_color ?: '#854D0E') }}" oninput="this.nextElementSibling.value = this.value" class="w-7 h-7 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                            <input type="text" name="badge_text_color" value="{{ old('badge_text_color', $slide->badge_text_color ?: '#854D0E') }}" oninput="this.previousElementSibling.value = this.value" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs font-mono">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Headlines & Subtitle --}}
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/40 space-y-3">
                                <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                                    Headlines & Subtitle
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Headline Line 1</label>
                                        <input type="text" name="title_line1" value="{{ old('title_line1', $slide->title_line1) }}" placeholder="e.g. Top Brands." class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-brand-400 mb-1.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-gray-500">Color:</span>
                                                <input type="color" value="{{ old('title_line1_color', $slide->title_line1_color ?: '#0F172A') }}" oninput="this.nextElementSibling.value = this.value" class="w-6 h-6 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                                <input type="text" name="title_line1_color" value="{{ old('title_line1_color', $slide->title_line1_color ?: '#0F172A') }}" oninput="this.previousElementSibling.value = this.value" class="w-20 border border-gray-200 rounded px-1.5 py-0.5 text-[10px] font-mono">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-gray-500">Size:</span>
                                                <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                    <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||12, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||48)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                    <input type="number" name="title_line1_font_size" min="12" max="150" value="{{ old('title_line1_font_size', $slide->title_line1_font_size) }}" placeholder="48" class="w-12 text-center border-0 p-0 text-[10px] font-semibold focus:ring-0 focus:outline-hidden">
                                                    <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||150, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||48)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                </div>
                                                <span class="text-[10px] text-gray-400">px</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Headline Line 2 (Highlight)</label>
                                        <input type="text" name="title_line2" value="{{ old('title_line2', $slide->title_line2) }}" placeholder="e.g. Better Value." class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs font-semibold focus:ring-2 focus:ring-brand-400 mb-1.5">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-gray-500">Color:</span>
                                                <input type="color" value="{{ old('title_line2_color', $slide->title_line2_color ?: '#EAB308') }}" oninput="this.nextElementSibling.value = this.value" class="w-6 h-6 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                                <input type="text" name="title_line2_color" value="{{ old('title_line2_color', $slide->title_line2_color ?: '#EAB308') }}" oninput="this.previousElementSibling.value = this.value" class="w-20 border border-gray-200 rounded px-1.5 py-0.5 text-[10px] font-mono">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-gray-500">Size:</span>
                                                <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                    <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||12, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||48)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                    <input type="number" name="title_line2_font_size" min="12" max="150" value="{{ old('title_line2_font_size', $slide->title_line2_font_size) }}" placeholder="48" class="w-12 text-center border-0 p-0 text-[10px] font-semibold focus:ring-0 focus:outline-hidden">
                                                    <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||150, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||48)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                </div>
                                                <span class="text-[10px] text-gray-400">px</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-[11px] font-medium text-gray-700">Subtitle Description</label>
                                        <div class="flex items-center gap-3 text-xs text-gray-500">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px]">Color:</span>
                                                <input type="color" value="{{ old('subtitle_color', $slide->subtitle_color ?: '#475569') }}" oninput="this.nextElementSibling.value = this.value" class="w-5 h-5 rounded border border-gray-200 cursor-pointer p-0.5">
                                                <input type="text" name="subtitle_color" value="{{ old('subtitle_color', $slide->subtitle_color ?: '#475569') }}" oninput="this.previousElementSibling.value = this.value" class="w-20 border border-gray-200 rounded px-1.5 py-0.5 text-[10px] font-mono">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px]">Size:</span>
                                                <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                    <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||9, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||14)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                    <input type="number" name="subtitle_font_size" min="9" max="60" value="{{ old('subtitle_font_size', $slide->subtitle_font_size) }}" placeholder="14" class="w-12 text-center border-0 p-0 text-[10px] font-semibold focus:ring-0 focus:outline-hidden">
                                                    <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||60, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||14)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-5 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                </div>
                                                <span class="text-[10px] text-gray-400">px</span>
                                            </div>
                                        </div>
                                    </div>
                                    <textarea name="subtitle" rows="2" placeholder="e.g. Quality open-box and pre-owned electronics at unbeatable prices in Abu Dhabi." class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-brand-400">{{ old('subtitle', $slide->subtitle) }}</textarea>
                                </div>
                            </div>

                            {{-- Top-Right Sticker / Callout --}}
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/40 space-y-3">
                                <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    Top-Right Sticker Callout
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Sticker Text</label>
                                        <textarea name="callout_text" rows="2" placeholder="e.g. Same&#10;Performance&#10;Lower Price" class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-brand-400">{{ old('callout_text', $slide->callout_text) }}</textarea>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Enter each line on a new line.</p>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Font Size (px)</label>
                                        <div class="flex items-center border border-gray-200 rounded-lg bg-white overflow-hidden focus-within:ring-2 focus-within:ring-brand-400">
                                            <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||10, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||26)-1); i.dispatchEvent(new Event('input'))" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-bold select-none cursor-pointer border-r border-gray-200">−</button>
                                            <input type="number" name="callout_font_size" min="10" max="80" value="{{ old('callout_font_size', $slide->callout_font_size) }}" placeholder="26" class="w-full text-center border-0 py-1 px-1 text-xs font-semibold focus:ring-0 focus:outline-hidden">
                                            <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||80, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||26)+1); i.dispatchEvent(new Event('input'))" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:bg-gray-100 hover:text-gray-900 font-bold select-none cursor-pointer border-l border-gray-200">+</button>
                                        </div>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Default ~26px</p>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Text Color</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" value="{{ old('callout_color', $slide->callout_color ?: '#1E3A8A') }}" oninput="this.nextElementSibling.value = this.value" class="w-7 h-7 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                            <input type="text" name="callout_color" value="{{ old('callout_color', $slide->callout_color ?: '#1E3A8A') }}" oninput="this.previousElementSibling.value = this.value" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs font-mono">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-medium text-gray-700 mb-1">Swoosh Underline Color</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" value="{{ old('callout_accent_color', $slide->callout_accent_color ?: '#EAB308') }}" oninput="this.nextElementSibling.value = this.value" class="w-7 h-7 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                            <input type="text" name="callout_accent_color" value="{{ old('callout_accent_color', $slide->callout_accent_color ?: '#EAB308') }}" oninput="this.previousElementSibling.value = this.value" class="w-full border border-gray-200 rounded-lg px-2 py-1 text-xs font-mono">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Bottom Feature Badges --}}
                            <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/40 space-y-3">
                                <div class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Bottom Feature Badges (3 Items)
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    @php
                                        $featDefaults = [
                                            ['icon' => 'shield-check', 'color' => '#EAB308', 'icon_size' => 28, 'title' => 'Quality Checked', 'title_size' => 13, 'subtitle' => 'Tested & Verified', 'subtitle_size' => 11],
                                            ['icon' => 'truck', 'color' => '#EAB308', 'icon_size' => 28, 'title' => 'Fast Delivery', 'title_size' => 13, 'subtitle' => 'Across UAE', 'subtitle_size' => 11],
                                            ['icon' => 'handshake', 'color' => '#EAB308', 'icon_size' => 28, 'title' => 'Great Prices', 'title_size' => 13, 'subtitle' => 'Save More', 'subtitle_size' => 11],
                                        ];
                                    @endphp
                                    @for ($i = 0; $i < 3; $i++)
                                        @php
                                            $item = ($slide->features[$i] ?? null) ?: ($featDefaults[$i] ?? ['icon' => 'shield-check', 'color' => '#EAB308', 'title' => '', 'subtitle' => '']);
                                        @endphp
                                        <div class="border border-gray-200 rounded-lg p-3 bg-white space-y-2.5">
                                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Badge {{ $i + 1 }}</div>

                                            <div>
                                                <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Icon</label>
                                                <select name="features[{{ $i }}][icon]" class="w-full border border-gray-200 rounded px-1.5 py-1 text-xs">
                                                    <option value="shield-check" @selected(($item['icon'] ?? '') === 'shield-check')>Shield Check</option>
                                                    <option value="truck" @selected(($item['icon'] ?? '') === 'truck')>Delivery Truck</option>
                                                    <option value="handshake" @selected(($item['icon'] ?? '') === 'handshake')>Handshake</option>
                                                    <option value="tag" @selected(($item['icon'] ?? '') === 'tag')>Price Tag</option>
                                                    <option value="heart" @selected(($item['icon'] ?? '') === 'heart')>Heart</option>
                                                    <option value="star" @selected(($item['icon'] ?? '') === 'star')>Star</option>
                                                    <option value="award" @selected(($item['icon'] ?? '') === 'award')>Award</option>
                                                    <option value="zap" @selected(($item['icon'] ?? '') === 'zap')>Fast (Zap)</option>
                                                    <option value="rotate-ccw" @selected(($item['icon'] ?? '') === 'rotate-ccw')>Easy Return</option>
                                                    <option value="clock" @selected(($item['icon'] ?? '') === 'clock')>24/7 Clock</option>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Icon Color</label>
                                                    <div class="flex items-center gap-1">
                                                        <input type="color" value="{{ $item['color'] ?? '#EAB308' }}" oninput="this.nextElementSibling.value = this.value" class="w-6 h-6 rounded border border-gray-200 cursor-pointer p-0.5 shrink-0">
                                                        <input type="text" name="features[{{ $i }}][color]" value="{{ $item['color'] ?? '#EAB308' }}" oninput="this.previousElementSibling.value = this.value" class="w-full border border-gray-200 rounded px-1 py-0.5 text-[10px] font-mono">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Icon Size (px)</label>
                                                    <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                        <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||12, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||28)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                        <input type="number" name="features[{{ $i }}][icon_size]" min="12" max="80" value="{{ $item['icon_size'] ?? '' }}" placeholder="28" class="w-full text-center border-0 p-0 text-[11px] font-semibold focus:ring-0 focus:outline-hidden">
                                                        <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||80, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||28)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Title</label>
                                                    <input type="text" name="features[{{ $i }}][title]" value="{{ $item['title'] ?? '' }}" placeholder="e.g. Quality Checked" class="w-full border border-gray-200 rounded px-2 py-1 text-xs font-semibold">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Title Size</label>
                                                    <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                        <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||9, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||13)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                        <input type="number" name="features[{{ $i }}][title_size]" min="9" max="40" value="{{ $item['title_size'] ?? '' }}" placeholder="13" class="w-full text-center border-0 p-0 text-[11px] font-semibold focus:ring-0 focus:outline-hidden">
                                                        <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||40, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||13)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2">
                                                <div class="col-span-2">
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Subtitle</label>
                                                    <input type="text" name="features[{{ $i }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}" placeholder="e.g. Tested & Verified" class="w-full border border-gray-200 rounded px-2 py-1 text-xs">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] font-medium text-gray-700 mb-0.5">Subtitle Size</label>
                                                    <div class="flex items-center border border-gray-200 rounded bg-white overflow-hidden">
                                                        <button type="button" onclick="const i=this.nextElementSibling; i.value=Math.max(parseInt(i.min)||8, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||11)-1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-r border-gray-200 text-xs">−</button>
                                                        <input type="number" name="features[{{ $i }}][subtitle_size]" min="8" max="30" value="{{ $item['subtitle_size'] ?? '' }}" placeholder="11" class="w-full text-center border-0 p-0 text-[11px] font-semibold focus:ring-0 focus:outline-hidden">
                                                        <button type="button" onclick="const i=this.previousElementSibling; i.value=Math.min(parseInt(i.max)||30, (parseInt(i.value)||parseInt(i.placeholder.replace(/\D/g,''))||11)+1); i.dispatchEvent(new Event('input'))" class="w-5 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold select-none cursor-pointer border-l border-gray-200 text-xs">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                                <button type="button" data-modal-close class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">Cancel</button>
                                <x-button type="submit" class="shadow-sm">Save Changes</x-button>
                            </div>
                        </form>
                    </x-modal>

                    <div style="flex-shrink:0;">
                        <button type="button" data-modal-open="remove-slide-modal-{{ $slide->id }}" title="Delete slide" aria-label="Delete slide" style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;cursor:pointer;" class="bg-red-50 text-red-600 hover:bg-red-100 transition">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>

                    <x-modal id="remove-slide-modal-{{ $slide->id }}" title="Remove slide" maxWidth="max-w-md">
                        <form method="POST" action="{{ route('admin.settings.hero-slides.destroy', $slide) }}">
                            @csrf
                            @method('DELETE')
                            <p class="text-sm text-gray-600">Are you sure you want to remove this hero slide? The image will be deleted from the homepage slider.</p>
                            <div class="flex justify-end gap-2 mt-5">
                                <button type="button" data-modal-close class="px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">Cancel</button>
                                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition cursor-pointer">Yes, remove</button>
                            </div>
                        </form>
                    </x-modal>
                </div>
            @empty
                <div class="border border-dashed border-gray-200 rounded-xl p-6 text-center text-xs text-gray-400">
                    No slides yet — upload images above.
                </div>
            @endforelse
        </div>

        {{-- Hero Text --}}
        <div class="bg-white border border-gray-100 rounded-xl p-6 shadow-xs">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Hero Text</h2>
            <p class="text-xs text-gray-500 mb-4">Shown on the homepage when no hero image is uploaded.</p>

            <form method="POST" action="{{ route('admin.settings.hero-text.update') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-badge">Top badge</label>
                    <input id="hero-badge" type="text" name="badge" value="{{ old('badge', $hero['badge']) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to hide the badge.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-title">Headline (line 1)</label>
                        <input id="hero-title" type="text" name="title" value="{{ old('title', $hero['title']) }}" maxlength="120" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-highlight">Headline (highlighted line 2)</label>
                        <input id="hero-highlight" type="text" name="highlight" value="{{ old('highlight', $hero['highlight']) }}" maxlength="120" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="hero-subtitle">Subtext</label>
                    <textarea id="hero-subtitle" name="subtitle" rows="3" maxlength="500" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">{{ old('subtitle', $hero['subtitle']) }}</textarea>
                </div>

                <x-button type="submit" class="shadow-sm">Save Hero Text</x-button>
            </form>
        </div>
    </div>
@endsection
