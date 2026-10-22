@extends('layouts.admin')

@section('title', 'Edit Banner: ' . $banner->title)

@section('content')
    @include('admin.settings._tabs')

    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.banners.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 shadow-2xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Banner: {{ $banner->title }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Manage slides, multiple images, and video for this banner.</p>
            </div>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
            Back to Banners
        </a>
    </div>

    @if ($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc pl-5 space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Basic Information Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7 shadow-xs space-y-5">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2 border-b border-gray-100 pb-3">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Banner Details
            </h2>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-800 mb-1">
                    Banner Title <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title', $banner->title) }}"
                    required
                    placeholder="e.g. Summer Mega Sale 2026"
                    class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                >
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="position" class="block text-sm font-medium text-gray-800 mb-1">
                        Position / Placement <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="position"
                        id="position"
                        value="{{ old('position', $banner->position) }}"
                        required
                        placeholder="e.g. homepage, sidebar"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-mono text-xs"
                    >
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-800 mb-1">
                        Sort Order
                    </label>
                    <input
                        type="number"
                        name="sort_order"
                        id="sort_order"
                        value="{{ old('sort_order', $banner->sort_order) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-mono text-xs"
                    >
                </div>
            </div>

            <div>
                <label for="link" class="block text-sm font-medium text-gray-800 mb-1">
                    Target Link URL (Optional)
                </label>
                <input
                    type="url"
                    name="link"
                    id="link"
                    value="{{ old('link', $banner->link) }}"
                    placeholder="https://example.com/promotions"
                    class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                >
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-800 mb-1">
                        Starts At (Optional)
                    </label>
                    <input
                        type="datetime-local"
                        name="starts_at"
                        id="starts_at"
                        value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                    >
                </div>

                <div>
                    <label for="ends_at" class="block text-sm font-medium text-gray-800 mb-1">
                        Ends At (Optional)
                    </label>
                    <input
                        type="datetime-local"
                        name="ends_at"
                        id="ends_at"
                        value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                    >
                </div>
            </div>

            <div class="pt-2">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $banner->is_active) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                    >
                    <span class="text-sm font-medium text-gray-800">Active Banner (Visible on public site)</span>
                </label>
            </div>
        </div>

        {{-- Existing & New Images Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Banner Images & Slides
                </h2>
                <span class="text-xs text-gray-400">Multiple slides allowed</span>
            </div>

            @php
                $existingImages = is_array($banner->images) && !empty($banner->images)
                    ? $banner->images
                    : ($banner->image ? [$banner->image] : []);
            @endphp

            @if (!empty($existingImages))
                <div>
                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Current Image Slides ({{ count($existingImages) }}):</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5" id="existing-images-container">
                        @foreach ($existingImages as $idx => $imgPath)
                            @php $imgUrl = \App\Support\MediaUrl::resolve($imgPath); @endphp
                            <div class="relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50 aspect-video shadow-2xs group existing-image-card" data-path="{{ $imgPath }}">
                                <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                                <span class="absolute top-1.5 left-1.5 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-mono z-10">
                                    Slide #{{ $idx + 1 }}
                                </span>

                                {{-- Removal Overlay & Toggle --}}
                                <div class="absolute inset-0 bg-red-950/70 text-white flex flex-col items-center justify-center p-2 text-center opacity-0 pointer-events-none transition-opacity duration-200 removal-overlay">
                                    <svg class="w-5 h-5 text-red-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    <span class="text-xs font-bold">Marked for Deletion</span>
                                    <span class="text-[10px] text-red-200">Click undo to keep</span>
                                </div>

                                <button
                                    type="button"
                                    onclick="toggleRemoveExistingImage(this, '{{ addslashes($imgPath) }}')"
                                    class="absolute bottom-1.5 right-1.5 px-2 py-1 rounded bg-white/95 hover:bg-red-600 hover:text-white text-gray-700 text-xs font-semibold shadow-xs transition cursor-pointer z-20 flex items-center gap-1"
                                    title="Remove this image"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    <span class="btn-text">Delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div id="hidden-remove-inputs"></div>
                </div>
            @endif

            {{-- Upload Additional Images --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Additional Images / Slides</label>
                <div class="relative border-2 border-dashed border-gray-200 hover:border-brand-400 rounded-2xl p-6 text-center transition-colors bg-gray-50/50 hover:bg-white cursor-pointer group">
                    <input
                        type="file"
                        name="images[]"
                        id="banner-images-input"
                        multiple
                        accept="image/png,image/jpeg,image/webp,image/avif,image/svg+xml"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        onchange="handleMultipleImagesPreview(event)"
                    >
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800 mb-1" id="images-drop-label">
                            <span class="text-brand-600 font-semibold underline">Click to add more images</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-400">Add more slides (JPG, PNG, WEBP, AVIF)</p>
                    </div>
                </div>
            </div>

            <div id="images-preview-grid" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-2"></div>
        </div>

        {{-- Video Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Banner Video (Slide with Auto-Advance on End)
                </h2>
                <span class="text-xs text-purple-600 font-medium">Slides when video ends</span>
            </div>

            @if ($banner->video_url)
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 space-y-3" id="current-video-box">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-700">Current Video Slide:</span>
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-red-600 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remove_video"
                                value="1"
                                id="remove-video-checkbox"
                                onchange="handleRemoveVideoToggle(this.checked)"
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500 cursor-pointer"
                            >
                            <span>Remove this video</span>
                        </label>
                    </div>

                    <div class="relative rounded-xl overflow-hidden border border-gray-300 bg-black aspect-video max-w-sm mx-auto shadow-2xs">
                        <video src="{{ $banner->video_url }}" controls class="w-full h-full object-contain"></video>
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ $banner->video_url ? 'Upload New Video (Replaces existing)' : 'Upload Banner Video' }}
                </label>
                <div class="relative border-2 border-dashed border-gray-200 hover:border-purple-400 rounded-2xl p-6 text-center transition-colors bg-purple-50/20 hover:bg-white cursor-pointer group">
                    <input
                        type="file"
                        name="video"
                        id="banner-video-input"
                        accept="video/mp4,video/webm,video/ogg,video/quicktime"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                        onchange="handleVideoPreview(event)"
                    >
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800 mb-1" id="video-chosen-text">
                            <span class="text-purple-600 font-semibold underline">Click to choose video</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-400">MP4, WEBM, or OGG (Max: 500 MB)</p>
                    </div>
                </div>
            </div>

            <div id="video-preview-wrapper" class="hidden pt-2">
                <div class="relative rounded-2xl overflow-hidden border border-gray-200 bg-black aspect-video max-w-md mx-auto">
                    <video id="video-preview-player" controls class="w-full h-full object-contain"></video>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                Cancel
            </a>

            <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-sm transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>Save Changes</span>
            </button>
        </div>
    </form>

    <script>
        function toggleRemoveExistingImage(btn, imgPath) {
            const card = btn.closest('.existing-image-card');
            const overlay = card.querySelector('.removal-overlay');
            const btnText = btn.querySelector('.btn-text');
            const hiddenContainer = document.getElementById('hidden-remove-inputs');

            const existingInput = hiddenContainer.querySelector(`input[value="${CSS.escape(imgPath)}"]`);

            if (existingInput) {
                // Undo removal
                existingInput.remove();
                overlay.classList.add('opacity-0', 'pointer-events-none');
                card.classList.remove('ring-2', 'ring-red-500');
                btn.classList.remove('bg-red-600', 'text-white');
                btn.classList.add('bg-white/95', 'text-gray-700');
                btnText.textContent = 'Delete';
            } else {
                // Mark for removal
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'remove_images[]';
                input.value = imgPath;
                hiddenContainer.appendChild(input);

                overlay.classList.remove('opacity-0', 'pointer-events-none');
                card.classList.add('ring-2', 'ring-red-500');
                btn.classList.remove('bg-white/95', 'text-gray-700');
                btn.classList.add('bg-red-600', 'text-white');
                btnText.textContent = 'Undo';
            }
        }

        function handleRemoveVideoToggle(checked) {
            const videoBox = document.getElementById('current-video-box');
            if (videoBox) {
                if (checked) {
                    videoBox.classList.add('opacity-50', 'ring-2', 'ring-red-400');
                } else {
                    videoBox.classList.remove('opacity-50', 'ring-2', 'ring-red-400');
                }
            }
        }

        function handleMultipleImagesPreview(event) {
            const files = event.target.files;
            const previewGrid = document.getElementById('images-preview-grid');
            const dropLabel = document.getElementById('images-drop-label');

            if (!files || files.length === 0) {
                previewGrid.classList.add('hidden');
                previewGrid.innerHTML = '';
                return;
            }

            dropLabel.innerHTML = `Selected <strong>${files.length} new image${files.length > 1 ? 's' : ''}</strong> (Click to change)`;
            previewGrid.innerHTML = '';
            previewGrid.classList.remove('hidden');

            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.className = 'relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50 aspect-video group shadow-2xs';
                    card.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">
                            New Slide #${index + 1}
                        </div>
                        <span class="absolute top-1 left-1 bg-emerald-600 text-white text-[10px] px-1.5 py-0.5 rounded font-mono">
                            New +
                        </span>
                    `;
                    previewGrid.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
        }

        function handleVideoPreview(event) {
            const input = event.target;
            const previewWrapper = document.getElementById('video-preview-wrapper');
            const videoPlayer = document.getElementById('video-preview-player');
            const labelText = document.getElementById('video-chosen-text');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                labelText.innerHTML = `Selected replacement video: <strong>${file.name}</strong> (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
                const url = URL.createObjectURL(file);
                videoPlayer.src = url;
                previewWrapper.classList.remove('hidden');
            } else {
                previewWrapper.classList.add('hidden');
                videoPlayer.src = '';
            }
        }
    </script>
@endsection
