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
                        <p class="text-xs text-gray-500 mt-1">Order {{ $slide->sort_order }} · {{ $slide->is_active ? 'Active' : 'Hidden' }}</p>
                    </div>

                    <div style="flex-shrink:0;">
                        <button type="button" data-modal-open="edit-slide-modal-{{ $slide->id }}" title="Edit slide" aria-label="Edit slide" style="width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;cursor:pointer;" class="bg-brand-50 text-brand-600 hover:bg-brand-100 transition">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6.768-6.768a2 2 0 112.828 2.828L11.828 15.83a2 2 0 01-.89.514L7 17l.656-3.938A2 2 0 018.172 12.17L9 13z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14"/></svg>
                        </button>
                    </div>

                    <x-modal id="edit-slide-modal-{{ $slide->id }}" title="Edit slide" maxWidth="max-w-xl">
                        <form method="POST" action="{{ route('admin.settings.hero-slides.update', $slide) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <img src="{{ $slide->imageUrl() }}" alt="Current slide image" class="w-full rounded-lg border border-gray-200" style="max-height:180px;object-fit:cover;">

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Replace image (optional)</label>
                                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="text-sm text-gray-600 w-full">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Links to product</label>
                                <select name="product_id" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                                    <option value="">— No link —</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected($slide->product_id === $product->id)>{{ $product->title }}</option>
                                    @endforeach
                                    @if ($slide->product && ! $products->contains('id', $slide->product_id))
                                        <option value="{{ $slide->product_id }}" selected>{{ $slide->product->title }} (not live)</option>
                                    @endif
                                </select>
                            </div>

                            <div class="flex items-center gap-6">
                                <div style="width:100px;">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Order</label>
                                    <input type="number" name="sort_order" min="0" value="{{ $slide->sort_order }}" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm">
                                </div>
                                <label class="flex items-center gap-2 text-sm text-gray-700 mt-5">
                                    <input type="checkbox" name="is_active" value="1" @checked($slide->is_active)> Active
                                </label>
                            </div>

                            <div class="flex justify-end gap-2 pt-2">
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
