@extends('layouts.admin')

@section('title', 'Banners')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Banner">
        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <x-input label="Title" name="title" type="text" required />
            <x-input label="Link (optional)" name="link" type="text" />
            <x-input label="Position" name="position" type="text" value="homepage" required />
            <x-input label="Sort order" name="sort_order" type="number" value="0" />
            <x-input label="Starts at (optional)" name="starts_at" type="datetime-local" />
            <x-input label="Ends at (optional)" name="ends_at" type="datetime-local" />
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Image</label>
                <input type="file" name="image" accept="image/*" required class="text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded accent-brand-500"> Active
            </label>
            <div class="flex items-end">
                <x-button type="submit">Add Banner</x-button>
            </div>
        </form>
    </x-card>

    <x-card title="Banners" class="mt-6">
        <x-table :headers="['Image', 'Title', 'Position', 'Order', 'Status', '']" id="banners-table">
            @forelse ($banners as $banner)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image) }}" class="w-20 h-10 object-cover rounded" alt="">
                    </td>
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $banner->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $banner->position }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $banner->sort_order }}</td>
                    <td class="px-4 py-3"><x-badge :color="$banner->is_active ? 'green' : 'gray'">{{ $banner->is_active ? 'Active' : 'Inactive' }}</x-badge></td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <form method="POST" action="{{ route('admin.banners.update', $banner) }}" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="title" value="{{ $banner->title }}">
                            <input type="hidden" name="position" value="{{ $banner->position }}">
                            <input type="hidden" name="sort_order" value="{{ $banner->sort_order }}">
                            <input type="hidden" name="is_active" value="{{ $banner->is_active ? 0 : 1 }}">
                            <button type="submit" class="text-brand-600 font-medium hover:underline text-sm">{{ $banner->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="inline" onsubmit="return confirm('Delete this banner?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-medium hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No banners yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
