@extends('layouts.app')

@section('title', 'All Categories')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-12">
        <x-breadcrumb :items="['All Categories' => null]" class="mb-6" />

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">All Categories</h1>
                <p class="text-sm text-gray-500 mt-1">Explore all products across verified and pre-owned categories</p>
            </div>
            <a href="{{ route('shop') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-brand-600 hover:text-brand-700">
                <span>View all products</span>
                <span>→</span>
            </a>
        </div>

        @if ($categories->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
                No categories available at the moment — check back soon.
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($categories as $category)
                    <div class="bg-white border border-gray-100 hover:border-amber-300 rounded-2xl p-5 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between group">
                        <div>
                            <div class="flex items-center gap-3.5 mb-4">
                                <div class="w-11 h-11 bg-gray-50 group-hover:bg-amber-50 border border-gray-100 group-hover:border-amber-200 rounded-xl flex items-center justify-center text-gray-700 group-hover:text-amber-600 transition-colors shrink-0">
                                    @if ($category->image)
                                        <img src="{{ $category->imageUrl() }}" alt="{{ $category->name }}" class="w-6 h-6 object-contain" />
                                    @else
                                        <x-category-icon :slug="$category->slug" class="w-6 h-6" />
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('shop', ['category' => $category->slug]) }}" class="font-bold text-gray-900 group-hover:text-amber-600 transition-colors block truncate text-base" title="{{ $category->name }}">
                                        {{ $category->name }}
                                    </a>
                                </div>
                            </div>

                            @if ($category->children->isNotEmpty())
                                <ul class="space-y-1.5 text-xs sm:text-[13px] text-gray-600 mb-4 pl-1 border-l-2 border-gray-100">
                                    @foreach ($category->children->take(6) as $child)
                                        <li class="pl-2.5">
                                            <a href="{{ route('shop', ['category' => $child->slug]) }}" class="hover:text-gray-950 hover:underline transition-colors block truncate">
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                    @if ($category->children->count() > 6)
                                        <li class="pl-2.5 text-xs text-gray-400 font-medium">
                                            + {{ $category->children->count() - 6 }} more subcategories
                                        </li>
                                    @endif
                                </ul>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between mt-2">
                            <a href="{{ route('shop', ['category' => $category->slug]) }}" class="text-xs font-semibold text-gray-700 group-hover:text-amber-600 flex items-center gap-1 transition-colors">
                                <span>Browse {{ $category->name }}</span>
                                <span class="group-hover:translate-x-0.5 transition-transform">→</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
