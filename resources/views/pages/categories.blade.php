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
                    <div class="bg-white border border-gray-100 hover:border-gray-900 rounded-xl p-5 shadow-2xs hover:shadow-md transition-all">
                        <a href="{{ route('categories.show', $category) }}" class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-center text-gray-900 font-bold text-sm">
                                {{ strtoupper(substr($category->name, 0, 1)) }}
                            </div>
                            <span class="font-bold text-gray-900 hover:text-black">{{ $category->name }}</span>
                        </a>

                        @if ($category->children->isNotEmpty())
                            <ul class="space-y-1.5 text-sm text-gray-500">
                                @foreach ($category->children as $child)
                                    <li><a href="{{ route('categories.show', $child) }}" class="hover:text-gray-900 transition-colors">{{ $child->name }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
