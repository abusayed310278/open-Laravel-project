@csrf
@if ($brand->exists)
    @method('PUT')
@endif

<x-input label="Name" name="name" type="text" placeholder="e.g. Nike, Apple, Samsung" :value="old('name', $brand->name)" />

<x-textarea label="Description" name="description" rows="3" placeholder="Enter a short description about this brand..." :value="old('description', $brand->description)" />

<x-file-upload
    name="logo"
    label="Logo"
    hint="PNG, JPG or SVG"
    :value="$brand->logo ? Illuminate\Support\Facades\Storage::disk('public')->url($brand->logo) : null"
    :delete-url="$brand->exists && $brand->logo ? route('admin.brands.logo.remove', $brand) : null"
/>

<x-select
    label="Status"
    name="status"
    :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])"
    :selected="old('status', $brand->status?->value ?? 'active')"
/>

<x-button type="submit">{{ $brand->exists ? 'Save Changes' : 'Create Brand' }}</x-button>
