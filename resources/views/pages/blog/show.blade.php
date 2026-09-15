@extends('layouts.app')

@section('title', $post->meta_title ?: $post->title)

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="['Blog' => route('blog.index'), $post->title => null]" class="mb-6" />

        @if ($post->category)
            <p class="text-xs text-brand-600 font-medium uppercase tracking-wide mb-2">{{ $post->category->name }}</p>
        @endif

        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $post->title }}</h1>
        <p class="text-sm text-gray-400 mb-6">{{ $post->author->name }} · {{ $post->published_at?->format('F j, Y') }}</p>

        @if ($post->featured_image)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="w-full rounded-md mb-8" alt="">
        @endif

        <div class="prose max-w-none text-gray-700 leading-relaxed">{!! $post->content !!}</div>

        @if ($post->tags->isNotEmpty())
            <div class="flex flex-wrap gap-2 mt-8">
                @foreach ($post->tags as $tag)
                    <span class="text-xs bg-gray-100 text-gray-600 rounded-full px-3 py-1">{{ $tag->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($related->isNotEmpty())
            <div class="mt-12 pt-8 border-t border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 mb-5">Related Posts</h2>
                <div class="grid sm:grid-cols-3 gap-4">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item) }}" class="text-sm font-medium text-gray-800 hover:text-brand-600">{{ $item->title }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
