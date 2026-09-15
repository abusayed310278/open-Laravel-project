@php
    $currentTags = $post->exists ? $post->tags->pluck('name')->implode(', ') : '';
@endphp

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card>
            <x-input label="Title" name="title" type="text" :value="old('title', $post->title)" required />

            <div class="mt-4">
                <x-textarea label="Excerpt" name="excerpt" rows="2">{{ old('excerpt', $post->excerpt) }}</x-textarea>
            </div>

            <div class="mt-4">
                <x-textarea label="Content" name="content" rows="16" required>{{ old('content', $post->content) }}</x-textarea>
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
            @if ($post->featured_image)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="w-full rounded-md mb-3" alt="">
            @endif
            <input type="file" name="featured_image" accept="image/*" class="text-sm">
        </x-card>
    </div>
</div>
