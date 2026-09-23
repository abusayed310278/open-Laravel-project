@props([
    'name',
    'label' => null,
    'accept' => null,
    'multiple' => false,
    'hint' => 'Upload image or video file',
    'value' => null,
    'isVideo' => false,
    'removeName' => null,
    'deleteUrl' => null,
    'fill' => false,
])

@php
    $removeInputName = $removeName ?? 'remove_' . $name;
    $defaultAccept = $accept ?? ($isVideo ? 'video/*,video/mp4,video/webm,video/ogg,video/quicktime,video/x-msvideo,video/x-matroska,video/x-flv,video/x-ms-wmv,.mp4,.webm,.mov,.avi,.mkv,.wmv,.flv,.3gp' : 'image/*');
    $isMediaVideo = $isVideo || ($value && preg_match('/\.(mp4|webm|ogg|mov|avi|mkv|wmv|flv|3gp|m4v)$/i', parse_url($value, PHP_URL_PATH) ?? ''));
    $inputId = 'file_upload_' . $name . '_' . Str::random(8);
@endphp

<div class="space-y-2 js-file-upload" data-file-upload-name="{{ $name }}">
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1.5 cursor-pointer">{{ $label }}</label>
    @endif

    <input type="hidden" name="{{ $removeInputName }}" value="0" class="js-remove-flag">

    <div
        class="flex flex-col items-center justify-center min-h-[170px] border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-brand-500 hover:bg-brand-50/20 transition-all js-dropzone relative overflow-hidden group"
    >
        <!-- Default Upload Prompt (shown when NO file is selected) -->
        <div class="js-upload-prompt flex flex-col items-center justify-center gap-2 py-4 {{ $value ? 'hidden' : '' }} pointer-events-none">
            <svg class="w-10 h-10 text-gray-400 group-hover:text-brand-500 transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span class="text-sm text-gray-600 pointer-events-none">
                <span class="text-brand-600 font-semibold">Click to upload</span> or drag and drop
            </span>
            <span class="text-xs text-gray-400 pointer-events-none">{{ $hint }}</span>
        </div>

        <!-- Inside-Box Preview Area -->
        <div class="js-box-preview {{ $fill ? 'absolute inset-0' : 'w-full flex flex-col items-center justify-center gap-2 relative' }} {{ $value ? '' : 'hidden' }}">
            <div class="{{ $fill ? 'relative w-full h-full' : 'relative max-h-36 max-w-full flex items-center justify-center overflow-hidden rounded-md border border-gray-200 bg-gray-50/50 p-1' }} group/img">
                <img
                    src="{{ !$isMediaVideo ? ($value ?? '') : '' }}"
                    alt="Preview"
                    class="js-box-preview-img {{ $fill ? 'w-full h-full object-cover' : 'max-h-32 w-auto object-contain' }} rounded {{ $isMediaVideo ? 'hidden' : '' }}"
                >
                <video
                    src="{{ $isMediaVideo ? ($value ?? '') : '' }}"
                    class="js-box-preview-video {{ $fill ? 'w-full h-full object-cover' : 'max-h-32 w-auto object-contain' }} rounded {{ $isMediaVideo ? '' : 'hidden' }}"
                    muted
                    controls
                    playsinline
                ></video>
                <!-- Hover overlay hint -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center rounded text-white text-xs font-medium pointer-events-none">
                    Click or drop to replace
                </div>
            </div>

            <div class="{{ $fill ? 'absolute bottom-1.5 left-1.5 right-1.5 flex items-center justify-between gap-2' : 'flex items-center gap-2' }} text-xs">
                <span class="js-preview-badge inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold {{ $value ? 'bg-gray-200 text-gray-700' : 'bg-emerald-100 text-emerald-800' }} rounded-full">
                    {{ $value ? ($isMediaVideo ? 'Current Video' : 'Current Image') : 'New File Selected' }}
                </span>
                <button
                    type="button"
                    class="js-remove-file-btn inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-md border border-rose-200 transition-colors cursor-pointer z-10"
                    title="Delete file attachment"
                    onclick="event.preventDefault(); event.stopPropagation(); window.clearSingleFileUpload(this);"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>

        <input
            type="file"
            id="{{ $inputId }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            accept="{{ $defaultAccept }}"
            @if ($multiple) multiple @endif
            {{ $attributes->merge(['class' => 'hidden js-file-input']) }}
        >
    </div>

    @error(str_replace([']', '['], ['', '.'], $name))
        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>

<script>
    (function() {
        if (!window.clearSingleFileUpload) {
            window.clearSingleFileUpload = function (btnElement) {
                const wrapper = btnElement ? btnElement.closest('.js-file-upload') : null;
                if (!wrapper) return;

                const fileInput = wrapper.querySelector('.js-file-input');
                const removeFlag = wrapper.querySelector('.js-remove-flag');
                const uploadPrompt = wrapper.querySelector('.js-upload-prompt');
                const boxPreview = wrapper.querySelector('.js-box-preview');
                const boxImg = wrapper.querySelector('.js-box-preview-img');
                const boxVideo = wrapper.querySelector('.js-box-preview-video');

                if (fileInput) fileInput.value = '';
                if (removeFlag) removeFlag.value = '1';

                if (boxImg) {
                    if (boxImg.dataset.activeUrl) {
                        URL.revokeObjectURL(boxImg.dataset.activeUrl);
                        delete boxImg.dataset.activeUrl;
                    }
                    boxImg.src = '';
                    boxImg.classList.add('hidden');
                }

                if (boxVideo) {
                    if (boxVideo.dataset.activeUrl) {
                        URL.revokeObjectURL(boxVideo.dataset.activeUrl);
                        delete boxVideo.dataset.activeUrl;
                    }
                    boxVideo.src = '';
                    boxVideo.classList.add('hidden');
                }

                if (boxPreview) boxPreview.classList.add('hidden');
                if (uploadPrompt) uploadPrompt.classList.remove('hidden');
            };
            window.clearFileUpload = window.clearSingleFileUpload;
        }

        function setupFileUploads() {
            document.querySelectorAll('.js-file-upload').forEach((wrapper) => {
                const fileInput = wrapper.querySelector('.js-file-input');
                const dropzone = wrapper.querySelector('.js-dropzone');
                const uploadPrompt = wrapper.querySelector('.js-upload-prompt');
                const boxPreview = wrapper.querySelector('.js-box-preview');
                const boxImg = wrapper.querySelector('.js-box-preview-img');
                const boxVideo = wrapper.querySelector('.js-box-preview-video');
                const previewBadge = wrapper.querySelector('.js-preview-badge');
                const removeFlag = wrapper.querySelector('.js-remove-flag');

                if (!fileInput) return;

                // Sync initial preview state
                if (boxImg && boxImg.getAttribute('src') && boxImg.getAttribute('src').trim() !== '') {
                    if (uploadPrompt) uploadPrompt.classList.add('hidden');
                    if (boxPreview) boxPreview.classList.remove('hidden');
                    if (boxImg) boxImg.classList.remove('hidden');
                    if (boxVideo) boxVideo.classList.add('hidden');
                } else if (boxVideo && boxVideo.getAttribute('src') && boxVideo.getAttribute('src').trim() !== '') {
                    if (uploadPrompt) uploadPrompt.classList.add('hidden');
                    if (boxPreview) boxPreview.classList.remove('hidden');
                    if (boxVideo) boxVideo.classList.remove('hidden');
                    if (boxImg) boxImg.classList.add('hidden');
                }

                function showPreview(file) {
                    if (!file) return;

                    const isVideo = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov|avi|mkv|wmv|flv|3gp|m4v)$/i.test(file.name);
                    const objectUrl = URL.createObjectURL(file);

                    if (isVideo) {
                        if (boxImg) {
                            boxImg.classList.add('hidden');
                            boxImg.src = '';
                        }
                        if (boxVideo) {
                            if (boxVideo.dataset.activeUrl) {
                                URL.revokeObjectURL(boxVideo.dataset.activeUrl);
                            }
                            boxVideo.dataset.activeUrl = objectUrl;
                            boxVideo.src = objectUrl;
                            boxVideo.classList.remove('hidden');
                        }
                    } else {
                        if (boxVideo) {
                            boxVideo.classList.add('hidden');
                            boxVideo.src = '';
                        }
                        if (boxImg) {
                            if (boxImg.dataset.activeUrl) {
                                URL.revokeObjectURL(boxImg.dataset.activeUrl);
                            }
                            boxImg.dataset.activeUrl = objectUrl;
                            boxImg.src = objectUrl;
                            boxImg.classList.remove('hidden');
                        }
                    }

                    if (uploadPrompt) uploadPrompt.classList.add('hidden');
                    if (boxPreview) boxPreview.classList.remove('hidden');

                    if (previewBadge) {
                        previewBadge.textContent = isVideo ? 'New Video Selected' : 'New Image Selected';
                        previewBadge.className = 'js-preview-badge inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold bg-emerald-100 text-emerald-800 rounded-full';
                    }

                    if (removeFlag) removeFlag.value = '0';
                }

                if (!fileInput.dataset.bound) {
                    fileInput.dataset.bound = 'true';
                    fileInput.addEventListener('change', (e) => {
                        if (e.target.files && e.target.files[0]) {
                            showPreview(e.target.files[0]);
                        }
                    });
                }

                if (dropzone && !dropzone.dataset.bound) {
                    dropzone.dataset.bound = 'true';

                    dropzone.addEventListener('click', (e) => {
                        if (e.target.closest('.js-remove-file-btn') || e.target.closest('video')) {
                            return;
                        }
                        fileInput.click();
                    });

                    ['dragenter', 'dragover'].forEach((eventName) => {
                        dropzone.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.add('border-brand-500', 'bg-brand-50/40', 'ring-2', 'ring-brand-500/20');
                        });
                    });

                    ['dragleave', 'drop'].forEach((eventName) => {
                        dropzone.addEventListener(eventName, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.remove('border-brand-500', 'bg-brand-50/40', 'ring-2', 'ring-brand-500/20');
                        });
                    });

                    dropzone.addEventListener('drop', (e) => {
                        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                            fileInput.files = e.dataTransfer.files;
                            showPreview(e.dataTransfer.files[0]);
                        }
                    });
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupFileUploads);
        } else {
            setupFileUploads();
        }
        window.initFileUploads = setupFileUploads;
    })();
</script>
