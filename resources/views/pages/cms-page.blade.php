@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <x-breadcrumb :items="[$page->title => null]" class="mb-6" />

        <div class="border-b border-gray-100 pb-6 mb-8">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight mb-2">{{ $page->title }}</h1>
            <div class="flex items-center gap-3 text-xs text-gray-400">
                <span>Last updated: {{ $page->updated_at?->format('F j, Y') ?? 'October 2026' }}</span>
                <span>•</span>
                <span class="text-brand-600 font-semibold">Official Openbox Document</span>
            </div>
        </div>

        <div class="prose prose-slate max-w-none text-gray-700 leading-relaxed">{!! $page->content !!}</div>
    </div>
@endsection
