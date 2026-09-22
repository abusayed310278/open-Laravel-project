@csrf
@if ($category->exists)
    @method('PUT')
@endif

<div class="grid sm:grid-cols-2 gap-5">
    <x-select
        label="Parent category"
        name="parent_id"
        placeholder="None (top level)"
        :options="$parents->pluck('name', 'id')"
        :selected="old('parent_id', $category->parent_id)"
    />
    <x-select
        label="Status"
        name="status"
        :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])"
        :selected="old('status', $category->status?->value ?? 'active')"
    />
</div>

<x-input label="Name" name="name" type="text" placeholder="e.g. Electronics, Men's Clothing" :value="old('name', $category->name)" />

<x-textarea label="Description" name="description" rows="3" placeholder="Enter a short description for this category..." :value="old('description', $category->description)" />

<x-file-upload
    name="image"
    label="Image"
    hint="PNG or JPG, square recommended"
    :value="$category->image ? Illuminate\Support\Facades\Storage::disk('public')->url($category->image) : null"
/>

<div class="grid sm:grid-cols-2 gap-5">
    <x-input label="Sort order" name="sort_order" type="number" placeholder="e.g. 0" :value="old('sort_order', $category->sort_order ?? 0)" />
</div>

<hr class="border-gray-100">

<p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">SEO</p>

<div class="grid sm:grid-cols-2 gap-5">
    <x-input label="Meta title" name="meta_title" type="text" placeholder="SEO title for search engines" :value="old('meta_title', $category->meta_title)" />
    <x-input label="Meta description" name="meta_description" type="text" placeholder="SEO meta description for search engines" :value="old('meta_description', $category->meta_description)" />
</div>

<x-button type="submit">{{ $category->exists ? 'Save Changes' : 'Create Category' }}</x-button>
