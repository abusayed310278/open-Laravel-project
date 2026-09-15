<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card>
            <x-input label="Title" name="title" type="text" :value="old('title', $page->title)" required />

            <div class="mt-4">
                <x-textarea label="Content (HTML allowed)" name="content" rows="16" required>{{ old('content', $page->content) }}</x-textarea>
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
                <p class="text-xs text-gray-400 mt-3">URL: /p/{{ $page->slug }}</p>
            @endif

            <x-button type="submit" class="w-full justify-center mt-5">{{ $page->exists ? 'Update Page' : 'Create Page' }}</x-button>
        </x-card>
    </div>
</div>
