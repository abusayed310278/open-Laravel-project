@extends('layouts.admin')

@section('title', 'Blog Categories')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card title="Add Category">
        <form method="POST" action="{{ route('admin.blog-categories.store') }}" class="grid sm:grid-cols-3 gap-4 items-end">
            @csrf
            <x-input label="Name" name="name" type="text" required />
            <x-input label="Description (optional)" name="description" type="text" />
            <div class="flex items-end gap-3">
                <select name="status" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <x-button type="submit">Add</x-button>
            </div>
        </form>
    </x-card>

    <x-card title="Categories" class="mt-6">
        <x-table :headers="['Name', 'Posts', 'Status', '']" id="blog-categories-table">
            @forelse ($categories as $category)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $category->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $category->posts_count }}</td>
                    <td class="px-4 py-3"><x-badge :color="$category->status->value === 'active' ? 'green' : 'gray'">{{ $category->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('admin.blog-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-medium hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-gray-400 text-sm">No categories yet.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
@endsection
