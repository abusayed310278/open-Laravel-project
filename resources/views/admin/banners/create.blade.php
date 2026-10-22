@extends('layouts.admin')

@section('title', 'Add New Banner')

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
                <h1 class="text-xl font-bold text-gray-900">Add New Banner</h1>
                <p class="text-xs text-gray-500 mt-0.5">Upload multiple images and optional video for a dynamic sliding banner.</p>
            </div>
        </div>
        <a href="{{ route('admin.banners.index') }}" class="text-xs font-semibold text-gray-600 hover:text-gray-900">
            Cancel
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

    <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

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
                    value="{{ old('title') }}"
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
                        value="{{ old('position', 'homepage') }}"
                        required
                        placeholder="e.g. homepage, sidebar"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-mono text-xs"
                    >
                    <p class="text-[11px] text-gray-400 mt-1">Default is <code class="bg-gray-100 px-1 py-0.5 rounded">homepage</code></p>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-800 mb-1">
                        Sort Order
                    </label>
                    <input
                        type="number"
                        name="sort_order"
                        id="sort_order"
                        value="{{ old('sort_order', 0) }}"
                        class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors font-mono text-xs"
                    >
                    <p class="text-[11px] text-gray-400 mt-1">Lower numbers display first (0, 1, 2...)</p>
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
                    value="{{ old('link') }}"
                    placeholder="https://example.com/promotions"
                    class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm text-gray-900 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors"
                >
                <p class="text-[11px] text-gray-400 mt-1">Where users will be redirected when clicking the banner.</p>
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
                        value="{{ old('starts_at') }}"
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
                        value="{{ old('ends_at') }}"
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
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500 w-4 h-4 cursor-pointer"
                    >
                    <span class="text-sm font-medium text-gray-800">Active Banner (Visible on public site immediately)</span>
                </label>
            </div>
        </div>

        {{-- Multiple Images Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Banner Images (Multiple Allowed)
                </h2>
                <span class="text-xs text-gray-400">Select one or multiple images</span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Slides / Images</label>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-800 mb-1" id="images-drop-label">
                            <span class="text-brand-600 font-semibold underline">Click to choose images</span> or drag and drop
                        </p>
                        <p class="text-xs text-gray-400">Hold Ctrl / Cmd or Shift to select multiple images (JPG, PNG, WEBP, AVIF)</p>
                    </div>
                </div>
            </div>

            {{-- Live Previews Grid --}}
            <div id="images-preview-grid" class="hidden grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pt-2"></div>
        </div>

        {{-- Video Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Banner Video (Optional Slide)
                </h2>
                <span class="text-xs text-purple-600 font-medium">Auto-slides when video finishes</span>
            </div>

            <p class="text-xs text-gray-500">
                If provided, the video will be included in the banner slideshow. The slider will play the video and automatically transition to the next slide as soon as the video reaches its end.
            </p>

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
                        <span class="text-purple-600 font-semibold underline">Click to upload banner video</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-400">MP4, WEBM, or OGG (Max: 500 MB)</p>
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
                <span>Create Banner</span>
            </button>
        </div>
    </form>

    <script>
        function handleMultipleImagesPreview(event) {
            const files = event.target.files;
            const previewGrid = document.getElementById('images-preview-grid');
            const dropLabel = document.getElementById('images-drop-label');

            if (!files || files.length === 0) {
                previewGrid.classList.add('hidden');
                previewGrid.innerHTML = '';
                return;
            }

            dropLabel.innerHTML = `Selected <strong>${files.length} image${files.length > 1 ? 's' : ''}</strong> (Click to change)`;
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
                            Slide #${index + 1}
                        </div>
                        <span class="absolute top-1 left-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-mono">
                            #${index + 1}
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
                labelText.innerHTML = `Selected video: <strong>${file.name}</strong> (${(file.size / (1024 * 1024)).toFixed(1)} MB)`;
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
