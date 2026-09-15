@csrf

<x-input label="Name" name="name" type="text" :value="old('name', $group->name)" placeholder="e.g. Technical Specifications" required />

<x-input label="Slug (optional)" name="slug" type="text" :value="old('slug', $group->slug)" placeholder="Leave blank to auto-generate" />

<x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', $group->sort_order ?? 0)" min="0" />

<x-textarea label="Description" name="description" rows="3">{{ old('description', $group->description) }}</x-textarea>

<label class="flex items-center gap-2 text-sm text-gray-700">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $group->is_active ?? true)) class="w-4 h-4 rounded accent-brand-500">
    Active specification group
</label>

<x-button type="submit">{{ $group->exists ? 'Save Changes' : 'Create Group' }}</x-button>
