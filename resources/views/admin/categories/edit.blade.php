@extends('layouts.admin')

@section('title', 'Edit '.$category->name)

@section('content')
    <x-breadcrumb :items="['Categories' => route('admin.categories.index'), $category->name => null]" />

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <div class="grid md:grid-cols-3 gap-5">
        <x-card class="md:col-span-2">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-5">
                @include('admin.categories._form')
            </form>
        </x-card>

        <x-card title="Attributes">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">Choose which product attributes apply to this category.</p>
                <a href="{{ route('admin.categories.attributes', $category) }}" class="text-xs text-brand-600 font-medium hover:underline shrink-0 ml-2">
                    Advanced Manager →
                </a>
            </div>

            <form method="POST" action="{{ route('admin.categories.attributes.update', $category) }}" class="space-y-3">
                @csrf
                @method('PUT')

                @forelse ($attributes as $attribute)
                    <label class="flex items-center justify-between gap-3 py-2 border-b border-gray-50">
                        <span class="flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox" name="attributes[]" value="{{ $attribute->id }}"
                                @checked(in_array($attribute->id, $assignedAttributeIds, true))
                                class="w-4 h-4 rounded accent-brand-500"
                            >
                            {{ $attribute->name }}
                        </span>
                        <label class="flex items-center gap-1.5 text-xs text-gray-400">
                            <input
                                type="checkbox" name="required[{{ $attribute->id }}]" value="1"
                                @checked($category->attributes->firstWhere('id', $attribute->id)?->pivot->is_required)
                                class="w-3.5 h-3.5 rounded accent-brand-500"
                            >
                            Required
                        </label>
                    </label>
                @empty
                    <p class="text-sm text-gray-400">No attributes exist yet.</p>
                @endforelse

                @if ($attributes->isNotEmpty())
                    <x-button type="submit" size="sm" class="mt-2">Save Attributes</x-button>
                @endif
            </form>
        </x-card>
    </div>
@endsection
