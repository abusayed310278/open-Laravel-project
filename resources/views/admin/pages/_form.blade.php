<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

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

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.summernote) {
                window.jQuery('#summernote-content').summernote({
                    placeholder: 'Type or paste page content here...',
                    tabsize: 2,
                    height: 400,
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
