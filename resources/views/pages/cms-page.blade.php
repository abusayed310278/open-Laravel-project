@extends('layouts.app')

@section('title', $page->meta_title ?: $page->title)

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
        <x-breadcrumb :items="[$page->title => null]" class="mb-6" />

        <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ $page->title }}</h1>

        <div class="prose max-w-none text-gray-700 leading-relaxed">{!! $page->content !!}</div>
    </div>
@endsection
