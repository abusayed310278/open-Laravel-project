@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="['Blog' => null]" class="mb-6" />

        <div class="grid lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1">
                <p class="text-sm font-semibold text-gray-800 mb-2">Categories</p>
                <div class="space-y-1.5 text-sm">
                    <a href="{{ route('blog.index') }}" class="block {{ ! request('category') ? 'text-brand-600 font-medium' : 'text-gray-600' }} hover:text-brand-600">All Posts</a>
                    @foreach ($categories as $category)
                        <a href="{{ route('blog.index', ['category' => $category->slug]) }}" class="block {{ request('category') === $category->slug ? 'text-brand-600 font-medium' : 'text-gray-600' }} hover:text-brand-600">{{ $category->name }}</a>
                    @endforeach
                </div>
            </aside>

            <div class="lg:col-span-3">
                <div class="grid sm:grid-cols-2 gap-6">
                    @forelse ($posts as $post)
                        <a href="{{ route('blog.show', $post) }}" class="block bg-white border border-gray-100 rounded-md overflow-hidden hover:shadow-sm transition-shadow">
                            @if ($post->featured_image)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="w-full h-40 object-cover" alt="">
                            @endif
                            <div class="p-4">
                                @if ($post->category)
                                    <p class="text-xs text-brand-600 font-medium uppercase tracking-wide mb-1">{{ $post->category->name }}</p>
                                @endif
                                <h2 class="font-semibold text-gray-900 mb-1">{{ $post->title }}</h2>
                                <p class="text-sm text-gray-500 line-clamp-2">{{ $post->excerpt }}</p>
                                <p class="text-xs text-gray-400 mt-2">{{ $post->author->name }} · {{ $post->published_at?->format('M j, Y') }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-400 text-sm col-span-2 text-center py-16">No posts published yet.</p>
                    @endforelse
                </div>

                <div class="mt-8">
                    <x-pagination :paginator="$posts" />
                </div>
            </div>
        </div>
    </div>
@endsection
