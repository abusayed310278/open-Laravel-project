@php
    $currentTags = $post->exists ? $post->tags->pluck('name')->implode(', ') : '';
@endphp

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
            <x-input label="Title" name="title" type="text" :value="old('title', $post->title)" required />

            <div class="mt-4">
                <x-textarea label="Excerpt" name="excerpt" rows="2">{{ old('excerpt', $post->excerpt) }}</x-textarea>
            </div>

            <div class="mt-4">
                <label for="summernote-content" class="block text-sm font-medium text-gray-700 mb-1.5">Content (Summernote Editor)</label>
                <textarea id="summernote-content" name="content" class="w-full">{{ old('content', $post->content) }}</textarea>
                @error('content')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
        </x-card>

        <x-card title="SEO">
            <x-input label="Meta title" name="meta_title" type="text" :value="old('meta_title', $post->meta_title)" />
            <div class="mt-4">
                <x-textarea label="Meta description" name="meta_description" rows="2">{{ old('meta_description', $post->meta_description) }}</x-textarea>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card title="Publish">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(old('status', $post->status?->value) === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-input label="Publish date" name="published_at" type="datetime-local" :value="old('published_at', $post->published_at?->format('Y-m-d\TH:i'))" />
            </div>

            <x-button type="submit" class="w-full justify-center mt-5">{{ $post->exists ? 'Update Post' : 'Create Post' }}</x-button>
        </x-card>

        <x-card title="Organize">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select name="category_id" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    <option value="">None</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <x-input label="Tags (comma-separated)" name="tags" type="text" :value="old('tags', $currentTags)" />
            </div>
        </x-card>

        <x-card title="Featured Image">
            <x-file-upload
                name="featured_image"
                hint="PNG, JPG, or WebP up to 4MB"
                :value="$post->featured_image_url"
            />
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
                        placeholder: 'Type or paste post content here...',
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
