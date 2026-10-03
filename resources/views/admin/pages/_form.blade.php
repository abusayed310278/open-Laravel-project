<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>

<style>
    .note-editor.note-frame {
        border-color: #e5e7eb !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }
    .note-toolbar {
        background-color: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 0.5rem !important;
    }
    .note-btn {
        background-color: #ffffff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.375rem !important;
        color: #374151 !important;
        padding: 0.35rem 0.65rem !important;
        font-size: 0.8125rem !important;
    }
    .note-btn:hover {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
    }
    .note-editable {
        background-color: #ffffff !important;
        min-height: 320px !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
        line-height: 1.6 !important;
        color: #1f2937 !important;
        padding: 1rem !important;
    }
    .note-dropdown-menu {
        z-index: 1050 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }
</style>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card>
            <x-input label="Title" name="title" type="text" :value="old('title', $page->title)" required />

            <div class="mt-4">
                <label for="summernote-content" class="block text-sm font-medium text-gray-700 mb-1.5">Content (Summernote Editor)</label>
                <textarea id="summernote-content" name="content" class="w-full">{{ old('content', $page->content) }}</textarea>
                @error('content')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <x-card title="SEO">
            <x-input label="Meta title" name="meta_title" type="text" :value="old('meta_title', $page->meta_title)" />
            <div class="mt-4">
                <x-textarea label="Meta description" name="meta_description" rows="2">{{ old('meta_description', $page->meta_description) }}</x-textarea>
            </div>
        </x-card>
    </div>

    <div>
        <x-card title="Publish">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $page->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            @if ($page->exists)
                <p class="text-xs text-gray-400 mt-3">URL: /pages/{{ $page->slug }}</p>
            @endif

            <x-button type="submit" class="w-full justify-center mt-5">{{ $page->exists ? 'Update Page' : 'Create Page' }}</x-button>
        </x-card>
    </div>
</div>

<script>
    (function () {
        function sendEditorImage(file, $editor) {
            const data = new FormData();
            data.append("image", file);
            data.append("_token", "{{ csrf_token() }}");

            window.jQuery.ajax({
                url: "{{ route('editor.upload-image') }}",
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                cache: false,
                contentType: false,
                processData: false,
                data: data,
                type: "POST",
                success: function(response) {
                    if (response && response.url) {
                        $editor.summernote('insertImage', response.url, function ($image) {
                            $image.addClass('max-w-full h-auto rounded-lg my-3 shadow-2xs');
                        });
                    }
                },
                error: function(jqXHR) {
                    let errMsg = 'Failed to upload image.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errMsg = jqXHR.responseJSON.message;
                    } else if (jqXHR.responseJSON && jqXHR.responseJSON.errors && jqXHR.responseJSON.errors.image) {
                        errMsg = jqXHR.responseJSON.errors.image[0];
                    }
                    alert(errMsg);
                }
            });
        }

        function initSummernote() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.summernote) {
                const $target = window.jQuery('#summernote-content');
                if ($target.length && !$target.data('summernote-ready')) {
                    $target.data('summernote-ready', true);
                    $target.summernote({
                        placeholder: 'Type or paste page content here...',
                        tabsize: 2,
                        height: 380,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                            ['fontname', ['fontname']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph', 'height']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture', 'video', 'hr']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        callbacks: {
                            onImageUpload: function(files) {
                                for (let i = 0; i < files.length; i++) {
                                    sendEditorImage(files[i], window.jQuery(this));
                                }
                            }
                        }
                    });
                }
            } else {
                setTimeout(initSummernote, 100);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSummernote);
        } else {
            initSummernote();
        }
    })();
</script>
