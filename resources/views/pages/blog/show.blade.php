@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)

@section('content')
    @php
        $commentCount = $post->approvedComments->count();
        $readMinutes = max(1, (int) ceil(str_word_count(strip_tags((string) $post->content)) / 220));
        $openCommentsTab = session('comment_status') || session('comment_error') || $errors->has('comment');
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <x-breadcrumb :items="['Blog' => route('blog.index'), $post->title => null]" class="mb-6" />

        <div class="grid lg:grid-cols-5 gap-10">
            {{-- Featured image --}}
            <div class="lg:col-span-2">
                <div class="bg-gray-50 rounded-xl aspect-square flex items-center justify-center overflow-hidden">
                    @if ($post->featured_image_url)
                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                    @else
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z"/></svg>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="lg:col-span-3">
                <div class="flex items-center gap-2 mb-3">
                    @if ($post->category)
                        <x-badge color="blue">{{ $post->category->name }}</x-badge>
                    @endif
                    <x-badge color="gray">{{ $readMinutes }} min read</x-badge>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ $post->title }}</h1>

                <div class="flex flex-wrap items-center gap-2 mb-4 text-sm">
                    <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                        <span class="text-gray-400">Author:</span>
                        <span class="font-semibold text-gray-900">{{ $post->author?->name ?? config('app.name') }}</span>
                    </div>
                    <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                        <span class="text-gray-400">Published:</span>
                        <span class="font-semibold text-gray-900">{{ $post->published_at?->format('M j, Y') }}</span>
                    </div>
                    <div class="inline-flex items-baseline gap-1.5 bg-gray-50 border border-gray-200 rounded-md px-3 py-1.5">
                        <span class="text-gray-400">Comments:</span>
                        <span class="font-semibold text-gray-900">{{ $commentCount }}</span>
                    </div>
                </div>

                @if ($post->excerpt)
                    <div class="prose prose-sm max-w-none text-gray-600 mb-6 leading-relaxed">
                        {{ $post->excerpt }}
                    </div>
                @endif

                @if ($post->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach ($post->tags as $tag)
                            <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-3 py-1 font-medium">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3 text-sm mb-6">
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Expert reviewed
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                        <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $readMinutes }} minute read
                    </div>
                </div>

                <a href="#comments" data-open-tab="comments" class="inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-700 border-t border-gray-100 pt-4 w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Join the discussion ({{ $commentCount }})
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mt-12">
            <div class="border-b border-gray-200 flex items-center gap-8 mb-6">
                <button type="button" data-tab="article" class="tab-btn pb-3 text-sm sm:text-base border-b-2 transition-colors duration-150 cursor-pointer -mb-px {{ $openCommentsTab ? 'font-medium border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' : 'font-semibold border-brand-500 text-brand-600' }}">Article</button>
                <button type="button" data-tab="comments" class="tab-btn pb-3 text-sm sm:text-base border-b-2 transition-colors duration-150 cursor-pointer -mb-px {{ $openCommentsTab ? 'font-semibold border-brand-500 text-brand-600' : 'font-medium border-transparent text-gray-500 hover:text-gray-800 hover:border-gray-300' }}">Comments ({{ $commentCount }})</button>
            </div>

            <div data-tab-content="article" class="py-2 {{ $openCommentsTab ? 'hidden' : '' }}">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
                <div class="prose max-w-none text-gray-800 text-base sm:text-lg leading-8 font-normal [&_h1]:font-semibold [&_h2]:font-semibold [&_h3]:font-semibold [&_h4]:font-semibold [&_strong]:font-semibold [&_b]:font-semibold" style="font-family: 'Source Serif 4', Georgia, serif; font-weight: 400;">{!! $post->content !!}</div>
            </div>

            <div id="comments" data-tab-content="comments" class="py-2 {{ $openCommentsTab ? '' : 'hidden' }}">
                <p class="text-xs text-gray-500 mb-6">Join the conversation and share your feedback on this article.</p>
                @if (session('comment_status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">{{ session('comment_status') }}</p>
                        </div>
                    </div>
                @endif

                @if (session('comment_error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-rose-800">{{ session('comment_error') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Existing Comments List --}}
                @if ($post->approvedComments->isNotEmpty())
                    <div class="space-y-4 mb-10">
                        @foreach ($post->approvedComments as $comment)
                            <div class="bg-gray-50/70 border border-gray-100 rounded-2xl p-5 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between gap-4 mb-2.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-800 flex items-center justify-center font-bold text-sm shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($comment->author_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                                <span>{{ $comment->author_name }}</span>
                                                @if ($comment->user)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full">
                                                        Verified Customer
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-700 leading-relaxed pl-12">
                                    {{ $comment->comment }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 mb-8 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <p class="text-sm text-gray-500 font-medium">No comments yet</p>
                        <p class="text-xs text-gray-400 mt-0.5">Be the first to share your thoughts on this post!</p>
                    </div>
                @endif

                {{-- Comment Section Box: Restricted to Customers --}}
                @auth
                    @if (auth()->user()->isCustomer() || auth()->user()->isAdmin())
                        <div class="bg-white border border-gray-200/80 rounded-2xl p-6 sm:p-7 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 pb-4 border-b border-gray-100">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">Leave a Comment</h3>
                                    <p class="text-xs text-gray-500">Share your feedback and thoughts on this article</p>
                                </div>
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-50 border border-brand-100 text-xs text-brand-900 w-fit">
                                    <span class="w-6 h-6 rounded-full bg-brand-500 text-white font-bold flex items-center justify-center text-[10px]">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </span>
                                    <span>Posting as <strong class="font-bold text-gray-900">{{ auth()->user()->name }}</strong></span>
                                    <span class="text-[10px] px-2 py-0.5 font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        {{ auth()->user()->isCustomer() ? 'Customer' : 'Admin' }}
                                    </span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('blog.comments.store', $post) }}" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="comment" class="block text-xs font-semibold text-gray-700 mb-1">Your Comment <span class="text-red-500">*</span></label>
                                    <textarea
                                        name="comment"
                                        id="comment"
                                        rows="4"
                                        required
                                        placeholder="Write your thoughts or questions here..."
                                        class="w-full text-sm p-3.5 border border-gray-200 rounded-xl focus:outline-hidden focus:border-brand-500 focus:ring-1 focus:ring-brand-500 bg-white"
                                    >{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition cursor-pointer inline-flex items-center gap-2"
                                    >
                                        <span>Post Comment</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bg-amber-50/80 border border-amber-200 rounded-2xl p-6 sm:p-7 text-center">
                            <div class="w-11 h-11 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <h3 class="text-sm font-bold text-amber-900 mb-1">Customer Account Required</h3>
                            <p class="text-xs text-amber-700 max-w-md mx-auto">
                                You are signed in as a {{ auth()->user()->role?->label() ?? 'partner' }}. Only customer accounts can leave comments on blog posts.
                            </p>
                        </div>
                    @endif
                @else
                    <div class="bg-gradient-to-br from-brand-50/50 via-white to-gray-50 border border-brand-100 rounded-2xl p-6 sm:p-8 text-center shadow-xs">
                        <div class="w-12 h-12 rounded-2xl bg-brand-100 text-brand-600 flex items-center justify-center mx-auto mb-3 shadow-2xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mb-1">Sign In to Join the Discussion</h3>
                        <p class="text-xs text-gray-500 max-w-md mx-auto mb-5">
                            Only registered customer accounts can leave comments on our blog articles. Sign in to share your thoughts, tips, or questions.
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('login') }}" class="px-5 py-2.5 bg-brand-500 hover:bg-brand-600 text-white rounded-xl text-xs font-bold shadow-xs hover:shadow transition inline-flex items-center gap-2">
                                <span>Sign In as Customer</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-4 py-2.5 border border-gray-200 hover:bg-gray-100/70 text-gray-700 rounded-xl text-xs font-semibold transition">
                                    Create an Account
                                </a>
                            @endif
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        @php
            $relatedItems = $related ?? $relatedPosts ?? collect();
        @endphp

        @if ($relatedItems->isNotEmpty())
            <div class="mt-14 pt-10 border-t border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Related Articles</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Explore more guides, tips, and insights</p>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1 transition">
                        <span>View all articles</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($relatedItems as $item)
                        <a href="{{ route('blog.show', $item) }}" class="group bg-white border border-gray-100 hover:border-brand-200 rounded-2xl overflow-hidden flex flex-col text-left shadow-2xs hover:shadow-md transition-all duration-200 h-full">
                            {{-- Product-like Image Thumbnail with fixed height and object-cover --}}
                            <div class="relative w-full h-44 sm:h-48 overflow-hidden bg-gray-100">
                                @if ($item->featured_image_url)
                                    <img
                                        src="{{ $item->featured_image_url }}"
                                        alt="{{ $item->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M4 8h16M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z" />
                                        </svg>
                                    </div>
                                @endif

                                @if ($item->category)
                                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur-xs text-brand-700 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full shadow-2xs">
                                        {{ $item->category->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Product-like Card Body --}}
                            <div class="p-4 sm:p-5 flex flex-col flex-1">
                                <h3 class="font-bold text-gray-950 group-hover:text-brand-600 transition-colors line-clamp-2 text-sm sm:text-base leading-snug mb-1.5">
                                    {{ $item->title }}
                                </h3>

                                @if ($item->excerpt)
                                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed mb-3">
                                        {{ $item->excerpt }}
                                    </p>
                                @endif

                                <div class="mt-auto pt-3 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-400">
                                    <span class="font-medium text-gray-600 truncate max-w-[130px]">{{ $item->author?->name ?? 'Openbox' }}</span>
                                    <span>{{ $item->published_at?->format('M j, Y') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('[data-tab-content]');

        function activateTab(target) {
            tabBtns.forEach(b => {
                const active = b.dataset.tab === target;
                b.classList.toggle('border-brand-500', active);
                b.classList.toggle('text-brand-600', active);
                b.classList.toggle('font-semibold', active);
                b.classList.toggle('border-transparent', !active);
                b.classList.toggle('text-gray-500', !active);
                b.classList.toggle('font-medium', !active);
            });
            tabContents.forEach(c => c.classList.toggle('hidden', c.dataset.tabContent !== target));
        }

        tabBtns.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));
        document.querySelectorAll('[data-open-tab]').forEach(link => {
            link.addEventListener('click', () => activateTab(link.dataset.openTab));
        });
        if (location.hash === '#comments') {
            activateTab('comments');
        }
    </script>
@endsection
