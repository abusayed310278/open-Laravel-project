@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
        <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-1 text-xs font-semibold">
            <span class="px-3.5 py-1.5 rounded-lg bg-white text-gray-900 shadow-xs">Static Pages ({{ $pages->total() }})</span>
            <a href="{{ route('admin.blog.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition inline-flex items-center gap-1.5">
                <span>Blog Posts</span>
                <span class="px-1.5 py-0.5 rounded-full bg-brand-50 text-brand-700 text-[10px] font-bold">{{ \App\Models\Post::count() }}</span>
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition">
                Blog Categories
            </a>
        </div>
        <x-button as="a" :href="route('admin.pages.create')">New Page</x-button>
    </div>

    <x-card>
        <x-table :headers="['Title', 'Slug', 'Status', 'Updated', 'Actions']" id="pages-table">
            @forelse ($pages as $page)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors {{ $page->slug === 'blog' ? 'bg-brand-50/20' : '' }}">
                    <td class="px-4 py-3 text-gray-800 font-medium">
                        <div class="flex items-center gap-2">
                            <span>{{ $page->title }}</span>
                            @if ($page->slug === 'blog')
                                <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-brand-100 text-brand-800 rounded-md">Blog Section</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $page->slug === 'blog' ? '/blog' : '/pages/' . $page->slug }}</td>
                    <td class="px-4 py-3"><x-badge :color="$page->status->badgeColor()">{{ $page->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $page->updated_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            <a href="{{ $page->slug === 'blog' ? route('blog.index') : route('pages.show', $page->slug) }}" target="_blank" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition inline-flex items-center" title="View Live Page (All Blogs)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </a>
                            <a href="{{ route('admin.pages.edit', $page) }}" class="p-1.5 text-gray-500 hover:text-brand-600 hover:bg-brand-50 rounded-lg transition inline-flex items-center" title="Edit Page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline-block m-0" onsubmit="return confirm('Delete this page permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer inline-flex items-center" title="Delete Page">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No pages found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$pages" />
    </x-card>
@endsection
