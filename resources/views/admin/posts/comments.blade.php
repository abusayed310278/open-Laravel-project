@extends('layouts.admin')

@section('title', 'Blog Comments')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    {{-- Top Section Navigation Tabs --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
        <div class="inline-flex p-1 bg-gray-100 rounded-xl gap-1 text-xs font-semibold">
            <a href="{{ route('admin.blog.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition inline-flex items-center gap-1.5">
                <span>Blog Posts</span>
                <span class="px-1.5 py-0.5 rounded-full bg-brand-50 text-brand-700 text-[10px] font-bold">{{ \App\Models\Post::count() }}</span>
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="px-3.5 py-1.5 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-white/60 transition">
                Blog Categories
            </a>
            <span class="px-3.5 py-1.5 rounded-lg bg-white text-gray-900 shadow-xs inline-flex items-center gap-1.5">
                <span>Blog Comments</span>
                <span class="px-1.5 py-0.5 rounded-full bg-brand-500 text-white text-[10px] font-bold">{{ $counts['all'] }}</span>
            </span>
        </div>

        <a href="{{ route('blog.index') }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 transition">
            <span>View Public Blog</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    <x-card>
        {{-- Filter & Search Bar --}}
        <div class="p-4 sm:p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Status Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1 sm:pb-0">
                <a
                    href="{{ route('admin.blog.comments.index', array_merge(request()->query(), ['status' => 'all'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-gray-900 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}"
                >
                    All ({{ $counts['all'] }})
                </a>
                <a
                    href="{{ route('admin.blog.comments.index', array_merge(request()->query(), ['status' => 'approved'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}"
                >
                    Approved ({{ $counts['approved'] }})
                </a>
                <a
                    href="{{ route('admin.blog.comments.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}"
                >
                    Pending ({{ $counts['pending'] }})
                </a>
                <a
                    href="{{ route('admin.blog.comments.index', array_merge(request()->query(), ['status' => 'spam'])) }}"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ $statusFilter === 'spam' ? 'bg-rose-600 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100' }}"
                >
                    Spam ({{ $counts['spam'] }})
                </a>
            </div>

            {{-- Search Bar --}}
            <form method="GET" action="{{ route('admin.blog.comments.index') }}" class="flex items-center gap-2">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search author, text..."
                        class="w-full text-xs pl-8 pr-3 py-2 border border-gray-200 rounded-lg focus:outline-hidden focus:border-brand-500 bg-white"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">Filter</button>
                @if (request('search'))
                    <a href="{{ route('admin.blog.comments.index', ['status' => request('status', 'all')]) }}" class="p-2 text-gray-400 hover:text-gray-600 text-xs">Clear</a>
                @endif
            </form>
        </div>

        {{-- Comments Table --}}
        <div class="overflow-x-auto">
            <x-table :headers="['Author', 'Comment & Article', 'Status', 'Submitted', 'Actions']" id="admin-comments-table">
                @forelse ($comments as $comment)
                    <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors align-top">
                        {{-- Author info --}}
                        <td class="px-4 py-3.5 w-56">
                            <div class="flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-brand-50 border border-brand-100 text-brand-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900 text-xs truncate">{{ $comment->author_name }}</div>
                                    @if ($comment->author_email)
                                        <div class="text-[11px] text-gray-400 truncate">{{ $comment->author_email }}</div>
                                    @endif
                                    @if ($comment->user)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-brand-600 font-semibold mt-0.5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            Registered User
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-400">Guest</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Comment Text & Post context --}}
                        <td class="px-4 py-3.5">
                            <div class="mb-2">
                                <p class="text-xs text-gray-800 leading-relaxed bg-gray-50/80 p-3 rounded-xl border border-gray-100">
                                    {{ $comment->comment }}
                                </p>
                            </div>
                            @if ($comment->post)
                                <div class="flex items-center gap-2 text-[11px] text-gray-500">
                                    <span class="font-medium text-gray-400">On:</span>
                                    <a href="{{ route('blog.show', $comment->post) }}#comments" target="_blank" class="font-semibold text-brand-600 hover:text-brand-700 hover:underline truncate max-w-md inline-flex items-center gap-1">
                                        <span>{{ $comment->post->title }}</span>
                                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                </div>
                            @else
                                <span class="text-[11px] text-gray-400 italic">Post deleted</span>
                            @endif
                        </td>

                        {{-- Status badge --}}
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <x-badge :color="$comment->statusBadgeColor()">
                                {{ ucfirst($comment->status) }}
                            </x-badge>
                        </td>

                        {{-- Submitted date --}}
                        <td class="px-4 py-3.5 text-xs text-gray-500 whitespace-nowrap">
                            <div>{{ $comment->created_at->format('M j, Y') }}</div>
                            <div class="text-[10px] text-gray-400">{{ $comment->created_at->format('h:i A') }}</div>
                        </td>

                        {{-- Moderation Actions --}}
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1">
                                {{-- Approve Button --}}
                                @if ($comment->status !== 'approved')
                                    <form method="POST" action="{{ route('admin.blog.comments.approve', $comment) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition cursor-pointer" title="Approve Comment">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Mark Pending Button --}}
                                @if ($comment->status !== 'pending')
                                    <form method="POST" action="{{ route('admin.blog.comments.pending', $comment) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-amber-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition cursor-pointer" title="Mark as Pending">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Mark Spam Button --}}
                                @if ($comment->status !== 'spam')
                                    <form method="POST" action="{{ route('admin.blog.comments.spam', $comment) }}" class="inline-block m-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Mark as Spam">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete Button --}}
                                <form method="POST" action="{{ route('admin.blog.comments.destroy', $comment) }}" class="inline-block m-0" onsubmit="return confirm('Delete this comment permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer" title="Delete Comment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>No blog comments found matching your filters.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-table>
        </div>

        <div class="p-4 border-t border-gray-50">
            <x-pagination :paginator="$comments" />
        </div>
    </x-card>
@endsection
