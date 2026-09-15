@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :items="['Categories' => null]" class="mb-6" />

        <h1 class="text-2xl font-bold text-gray-900 mb-8">Browse Categories</h1>

        @if ($categories->isEmpty())
            <p class="text-gray-400">No categories yet — check back soon.</p>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($categories as $category)
                    <div class="bg-white border border-gray-100 rounded-md p-5">
                        <a href="{{ route('categories.show', $category) }}" class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-brand-50 rounded-md flex items-center justify-center text-brand-500 font-semibold">
                                {{ strtoupper(substr($category->name, 0, 1)) }}
                            </div>
                            <span class="font-semibold text-gray-900">{{ $category->name }}</span>
                        </a>

                        @if ($category->children->isNotEmpty())
                            <ul class="space-y-1.5 text-sm text-gray-500">
                                @foreach ($category->children as $child)
                                    <li><a href="{{ route('categories.show', $child) }}" class="hover:text-brand-600">{{ $child->name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
