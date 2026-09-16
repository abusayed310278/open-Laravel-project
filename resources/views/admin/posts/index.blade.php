@extends('layouts.admin')

@section('title', 'Blog Posts')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <x-slot:title>Blog Posts</x-slot:title>
        <x-slot:action>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.blog-categories.index') }}" class="text-xs font-semibold text-gray-600 hover:text-brand-600 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-brand-200 transition">
                    Manage Categories
                </a>
                <x-button as="a" :href="route('admin.blog.create')" class="text-xs py-1.5 px-3 font-semibold">
                    + New Post
                </x-button>
            </div>
        </x-slot:action>

        <x-table :headers="['Title', 'Category', 'Author', 'Status', 'Published', 'Actions']" id="admin-posts-table">
            @forelse ($posts as $post)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-gray-50 relative flex items-center justify-center">
                                @if ($post->featured_image_url)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover" onerror="this.classList.add('hidden'); this.parentElement.querySelector('.img-fallback').classList.remove('hidden');">
                                    <div class="img-fallback hidden w-full h-full text-gray-400 flex items-center justify-center bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                    </div>
                                @else
                                    <div class="w-full h-full text-gray-400 flex items-center justify-center bg-gray-100">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="font-medium text-gray-900 block truncate max-w-xs" title="{{ $post->title }}">{{ $post->title }}</span>
                                <span class="text-xs text-gray-400 font-mono">/{{ $post->slug }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        @if ($post->category)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $post->category->name }}
                            </span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <span class="font-medium text-gray-800">{{ $post->author?->name ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$post->status->badgeColor()">{{ $post->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $post->published_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            {{-- View Modal Trigger --}}
                            <button type="button" data-modal-open="view-post-modal-{{ $post->id }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="View Post Preview">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>

                            {{-- Active / Deactive Toggle Icon --}}
                            <form method="POST" action="{{ route('admin.blog.toggle-status', $post) }}" class="inline-block m-0">
                                @csrf
                                @method('PATCH')
                                @if ($post->status === \App\Enums\ContentStatus::Published)
                                    <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Published (Click to change to Draft)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </button>
                                @else
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-gray-100 rounded-lg transition cursor-pointer" title="Draft (Click to Publish)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                    </button>
                                @endif
                            </form>

                            {{-- Edit Icon --}}
                            <a href="{{ route('admin.blog.edit', $post) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition cursor-pointer" title="Edit Post">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>

                            {{-- Delete Icon --}}
                            <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')" data-confirm="Delete this post permanently?" class="inline-block m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Delete Post">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No posts found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$posts" />
    </x-card>

    {{-- View Post Modals --}}
    @foreach ($posts as $post)
        <x-modal id="view-post-modal-{{ $post->id }}" title="Post Details" maxWidth="max-w-2xl">
            <div class="space-y-4">
                {{-- Header & Meta --}}
                <div class="flex items-start justify-between pb-3 border-b border-gray-100 gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 leading-snug">{{ $post->title }}</h3>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">/{{ $post->slug }}</div>
                    </div>
                    <div class="shrink-0">
                        <x-badge :color="$post->status->badgeColor()">{{ $post->status->label() }}</x-badge>
                    </div>
                </div>

                {{-- Post Meta Info --}}
                <div class="grid grid-cols-3 gap-3 bg-gray-50 p-3 rounded-lg text-xs">
                    <div>
                        <span class="text-gray-400 block font-medium">Category</span>
                        <span class="font-semibold text-gray-800">{{ $post->category?->name ?? 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Author</span>
                        <span class="font-semibold text-gray-800">{{ $post->author?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block font-medium">Published Date</span>
                        <span class="font-semibold text-gray-800">{{ $post->published_at?->format('M j, Y g:i A') ?? 'Not published' }}</span>
                    </div>
                </div>

                {{-- Featured Image --}}
                @if ($post->featured_image_url)
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Featured Image</div>
                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full max-h-60 object-cover rounded-lg border border-gray-100" onerror="this.parentElement.style.display='none'">
                    </div>
                @endif

                {{-- Excerpt --}}
                @if ($post->excerpt)
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Excerpt</div>
                        <p class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg italic">{{ $post->excerpt }}</p>
                    </div>
                @endif

                {{-- Content --}}
                <div>
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Content</div>
                    <div class="text-xs text-gray-700 bg-gray-50 p-3 rounded-lg max-h-48 overflow-y-auto whitespace-pre-line leading-relaxed border border-gray-100">
                        {{ $post->content }}
                    </div>
                </div>

                {{-- Tags --}}
                @if ($post->tags && $post->tags->count() > 0)
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Tags</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($post->tags as $tag)
                                <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">#{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Modal Action Footer --}}
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" data-modal-close class="whitespace-nowrap px-4 py-2 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer">
                        Close
                    </button>
                    <a href="{{ route('admin.blog.edit', $post) }}" class="whitespace-nowrap px-4 py-2 text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 rounded-lg transition inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit Post
                    </a>
                </div>
            </div>
        </x-modal>
    @endforeach
@endsection
