@csrf
@if ($brand->exists)
    @method('PUT')
@endif

<x-input label="Name" name="name" type="text" :value="old('name', $brand->name)" />

<x-textarea label="Description" name="description" rows="3" :value="old('description', $brand->description)" />

<x-file-upload name="logo" label="Logo" hint="PNG or SVG" />
@if ($brand->logo)
    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($brand->logo) }}" alt="{{ $brand->name }}" class="w-16 h-16 rounded-md object-contain border border-gray-100 -mt-3">
@endif

<x-select
    label="Status"
    name="status"
    :options="collect(\App\Enums\PublishStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])"
    :selected="old('status', $brand->status?->value ?? 'active')"
/>

<x-button type="submit">{{ $brand->exists ? 'Save Changes' : 'Create Brand' }}</x-button>
