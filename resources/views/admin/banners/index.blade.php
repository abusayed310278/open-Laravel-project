@extends('layouts.admin')

@section('title', 'Banners')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    @if ($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card>
        <x-slot:title>Banners</x-slot:title>
        <x-slot:action>
            <x-button type="button" data-modal-open="add-banner-modal" class="text-xs py-1.5 px-3 font-semibold cursor-pointer">
                + Add Banner
            </x-button>
        </x-slot:action>

        <x-table :headers="['Image', 'Title', 'Position', 'Order', 'Status', 'Schedule', 'Actions']" id="admin-banners-table">
            @forelse ($banners as $banner)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    {{-- Image / Video Thumbnail --}}
                    <td class="px-4 py-3">
                        <div class="w-24 h-12 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-gray-50 relative flex items-center justify-center">
                            @if ($banner->video_url)
                                <video src="{{ $banner->video_url }}" muted class="w-full h-full object-cover"></video>
                                <span class="absolute top-1 right-1 bg-black/70 text-white font-bold text-[9px] px-1 py-0.2 rounded uppercase">Video</span>
                            @elseif ($banner->image_url)
                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.parentElement.querySelector('.img-fallback').classList.remove('hidden');">
                                <div class="img-fallback hidden w-full h-full text-gray-400 flex items-center justify-center bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            @else
                                <div class="w-full h-full text-gray-400 flex items-center justify-center bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                            @endif
                        </div>
                    </td>

                    {{-- Title & Link --}}
                    <td class="px-4 py-3">
                        <div class="min-w-0">
                            <span class="font-medium text-gray-900 block truncate max-w-xs" title="{{ $banner->title }}">{{ $banner->title }}</span>
                            @if ($banner->link)
                                <a href="{{ $banner->link }}" target="_blank" class="text-xs text-brand-600 hover:underline inline-flex items-center gap-1 mt-0.5 max-w-xs truncate" title="{{ $banner->link }}">
                                    <span>{{ $banner->link }}</span>
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            @endif
                        </div>
                    </td>

                    {{-- Position --}}
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 font-mono">
                            {{ $banner->position }}
                        </span>
                    </td>

                    {{-- Sort Order --}}
                    <td class="px-4 py-3 text-xs text-gray-500 font-mono">
                        #{{ $banner->sort_order }}
                    </td>

                    {{-- Status Badge --}}
                    <td class="px-4 py-3">
                        <x-badge :color="$banner->is_active ? 'green' : 'gray'">{{ $banner->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </td>

                    {{-- Schedule --}}
                    <td class="px-4 py-3 text-xs text-gray-500">
                        @if ($banner->starts_at || $banner->ends_at)
                            <div class="space-y-0.5">
                                <div>{{ $banner->starts_at?->format('M j, Y') ?? 'Now' }}</div>
                                <div class="text-gray-400">to {{ $banner->ends_at?->format('M j, Y') ?? 'Indefinite' }}</div>
                            </div>
                        @else
                            <span class="text-gray-400">Always Active</span>
                        @endif
                    </td>

                    {{-- Actions (SVG Icons) --}}
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- View Icon Modal Trigger --}}
                            <button type="button" data-modal-open="view-banner-modal-{{ $banner->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="View Banner Details">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>

                            {{-- Active / Deactive Toggle Icon --}}
                            <form method="POST" action="{{ route('admin.banners.toggle-active', $banner) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($banner->is_active)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Active (Click to Deactivate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Inactive (Click to Activate)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon Modal Trigger --}}
                            <button type="button" data-modal-open="edit-banner-modal-{{ $banner->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit Banner">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </button>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" onsubmit="return confirm('Delete this banner permanently?')" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Banner">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No banners found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$banners" />
    </x-card>

    {{-- Add Banner Modal --}}
    <x-modal id="add-banner-modal" title="Add New Banner" maxWidth="max-w-xl">
        <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <x-input label="Banner Title" name="title" type="text" placeholder="e.g. Summer Sale 50% Off" required />

            <div class="grid sm:grid-cols-2 gap-4">
                <x-input label="Position" name="position" type="text" value="homepage" placeholder="e.g. homepage, sidebar" required />
                <x-input label="Sort Order" name="sort_order" type="number" value="0" />
            </div>

            <x-input label="Target Link URL (Optional)" name="link" type="text" placeholder="https://example.com/collection" />

            <div class="grid sm:grid-cols-2 gap-4">
                <x-input label="Starts At (Optional)" name="starts_at" type="datetime-local" />
                <x-input label="Ends At (Optional)" name="ends_at" type="datetime-local" />
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <x-file-upload
                    name="image"
                    label="Banner Image"
                    accept="image/*"
                    hint="Recommended size: 1600x500px or 1200x400px (Max 20MB)"
                />

                <x-file-upload
                    name="video"
                    label="Banner Video (Optional)"
                    accept="video/*"
                    hint="Supports all video formats: MP4, WEBM, MOV, AVI, MKV, WMV, etc. (Max 500MB)"
                    :isVideo="true"
                />
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="is_active" id="add_is_active" value="1" checked class="w-4 h-4 rounded accent-brand-500 border-gray-300">
                <label for="add_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">Set as Active Banner</label>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                    Cancel
                </button>
                <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">
                    Create Banner
                </x-button>
            </div>
        </form>
    </x-modal>

    {{-- View & Edit Modals --}}
    @foreach ($banners as $banner)
        {{-- View Banner Modal --}}
        <x-modal id="view-banner-modal-{{ $banner->id }}" title="Banner Details" maxWidth="max-w-2xl">
            <div class="space-y-4">
                {{-- Banner Full Image/Video Preview --}}
                <div class="w-full rounded-lg overflow-hidden border border-gray-100 bg-gray-50 flex items-center justify-center relative min-h-[140px] max-h-72">
                    @if ($banner->video_url)
                        <video src="{{ $banner->video_url }}" controls autoplay loop muted playsinline class="w-full h-full max-h-72 object-cover"></video>
                    @elseif ($banner->image_url)
                        <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-full h-full max-h-72 object-cover" onerror="this.classList.add('hidden'); this.parentElement.querySelector('.view-fallback').classList.remove('hidden');">
                        <div class="view-fallback hidden w-full h-40 text-gray-400 flex items-center justify-center bg-gray-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    @else
                        <div class="w-full h-40 text-gray-400 flex items-center justify-center bg-gray-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    @endif
                </div>

                {{-- Header & Status --}}
                <div class="flex items-start justify-between pb-3 border-b border-gray-100 gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 leading-snug">{{ $banner->title }}</h3>
                        @if ($banner->link)
                            <a href="{{ $banner->link }}" target="_blank" class="text-xs text-brand-600 hover:underline inline-flex items-center gap-1 mt-1">
                                <span>{{ $banner->link }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        @endif
                    </div>
                    <div class="shrink-0">
                        <x-badge :color="$banner->is_active ? 'green' : 'gray'">{{ $banner->is_active ? 'Active' : 'Inactive' }}</x-badge>
                    </div>
                </div>

                {{-- Key Details Grid --}}
                <div class="grid grid-cols-3 gap-3 bg-gray-50 p-3 rounded-lg text-xs">
                    <div>
                        <span class="text-gray-400 block font-medium">Position</span>
                        <span class="font-semibold text-gray-800 font-mono">{{ $banner->position }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Sort Order</span>
                        <span class="font-semibold text-gray-800 font-mono">#{{ $banner->sort_order }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Schedule</span>
                        <span class="font-semibold text-gray-800">
                            @if ($banner->starts_at || $banner->ends_at)
                                {{ $banner->starts_at?->format('M j, Y') ?? 'Now' }} - {{ $banner->ends_at?->format('M j, Y') ?? 'Indefinite' }}
                            @else
                                Always Active
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Modal Action Footer --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Close
                    </button>
                    <button type="button" data-modal-close data-modal-open="edit-banner-modal-{{ $banner->id }}" class="whitespace-nowrap px-4 py-2 text-xs font-semibold text-brand-600 bg-brand-50 hover:bg-brand-100 rounded-lg transition cursor-pointer inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit Banner
                    </button>
                </div>
            </div>
        </x-modal>

        {{-- Edit Banner Modal --}}
        <x-modal id="edit-banner-modal-{{ $banner->id }}" title="Edit Banner" maxWidth="max-w-xl">
            <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <x-input label="Banner Title" name="title" type="text" :value="old('title', $banner->title)" required />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="Position" name="position" type="text" :value="old('position', $banner->position)" required />
                    <x-input label="Sort Order" name="sort_order" type="number" :value="old('sort_order', $banner->sort_order)" />
                </div>

                <x-input label="Target Link URL (Optional)" name="link" type="text" :value="old('link', $banner->link)" />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input label="Starts At (Optional)" name="starts_at" type="datetime-local" :value="old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i'))" />
                    <x-input label="Ends At (Optional)" name="ends_at" type="datetime-local" :value="old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i'))" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-file-upload
                        name="image"
                        label="Banner Image"
                        accept="image/*"
                        hint="Leave empty to keep existing image (Max 20MB)"
                        :value="$banner->image_url"
                    />

                    <x-file-upload
                        name="video"
                        label="Banner Video (Optional)"
                        accept="video/*"
                        hint="Leave empty to keep existing video (Max 500MB)"
                        :value="$banner->video_url"
                        :isVideo="true"
                    />
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="edit_is_active_{{ $banner->id }}" value="1" @checked(old('is_active', $banner->is_active)) class="w-4 h-4 rounded accent-brand-500 border-gray-300">
                    <label for="edit_is_active_{{ $banner->id }}" class="text-sm font-medium text-gray-700 cursor-pointer">Active Banner</label>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Cancel
                    </button>
                    <x-button type="submit" class="whitespace-nowrap px-4 py-2 text-xs font-semibold">
                        Save Changes
                    </x-button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
