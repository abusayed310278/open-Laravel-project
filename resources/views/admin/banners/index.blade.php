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
            <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold bg-brand-600 text-white hover:bg-brand-700 shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add Banner</span>
            </a>
        </x-slot:action>

        <x-table :headers="['Thumbnail', 'Title', 'Position', 'Order', 'Status', 'Schedule', 'Actions']" id="admin-banners-table">
            @forelse ($banners as $banner)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    {{-- Image / Video Thumbnail --}}
                    <td class="px-4 py-3">
                        @php
                            $imagesCount = count($banner->images_urls);
                            $hasVideo = !empty($banner->video_url);
                        @endphp
                        <div class="w-24 h-12 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-gray-50 relative flex items-center justify-center">
                            @if ($hasVideo && $imagesCount === 0)
                                <video src="{{ $banner->video_url }}" muted class="w-full h-full object-cover"></video>
                                <span class="absolute top-1 right-1 bg-black/70 text-white font-bold text-[9px] px-1 py-0.2 rounded uppercase">Video</span>
                            @elseif ($imagesCount > 0)
                                <img src="{{ $banner->images_urls[0] }}" alt="{{ $banner->title }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.parentElement.querySelector('.img-fallback').classList.remove('hidden');">
                                <div class="img-fallback hidden w-full h-full text-gray-400 flex items-center justify-center bg-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                @if ($imagesCount > 1 || $hasVideo)
                                    <span class="absolute bottom-1 right-1 bg-black/75 text-white text-[9px] font-semibold px-1 py-0.5 rounded flex items-center gap-0.5">
                                        @if ($hasVideo)
                                            <svg class="w-2.5 h-2.5 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm8 6l-5 3V7l5 3z"/></svg>
                                        @endif
                                        <span>{{ $imagesCount + ($hasVideo ? 1 : 0) }} slides</span>
                                    </span>
                                @endif
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
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="font-medium text-gray-900 hover:text-brand-600 block truncate max-w-xs transition" title="{{ $banner->title }}">{{ $banner->title }}</a>
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

                    {{-- Actions (Full Page Edit & Delete) --}}
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
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

                            {{-- Edit Banner (Full Page) --}}
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition" title="Edit Banner (Full Page)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Banner --}}
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
@endsection
