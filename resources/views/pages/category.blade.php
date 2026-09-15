@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :items="['Categories' => route('categories.index'), $category->name => null]" class="mb-6" />

        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="text-gray-500 max-w-2xl mb-8">{{ $category->description }}</p>
        @endif

        @if ($category->children->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                @foreach ($category->children as $child)
                    <a href="{{ route('categories.show', $child) }}" class="flex items-center gap-2 p-4 rounded-md border border-gray-100 hover:border-brand-300 transition-colors">
                        <div class="w-8 h-8 bg-brand-50 rounded-md flex items-center justify-center text-brand-500 text-sm font-semibold">
                            {{ strtoupper(substr($child->name, 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $child->name }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="bg-gray-50 border border-gray-100 rounded-md p-8 text-center text-gray-400 text-sm">
            Products in this category will appear here once listings go live.
        </div>
    </div>
@endsection
