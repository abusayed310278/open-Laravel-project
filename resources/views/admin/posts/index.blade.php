@extends('layouts.admin')

@section('title', 'Blog Posts')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('admin.blog-categories.index') }}" class="text-sm text-gray-500 font-medium hover:underline">Manage Categories</a>
        <x-button as="a" :href="route('admin.blog.create')">New Post</x-button>
    </div>

    <x-card>
        <x-table :headers="['Title', 'Category', 'Author', 'Status', 'Published', '']" id="posts-table">
            @forelse ($posts as $post)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $post->title }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $post->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $post->author->name }}</td>
                    <td class="px-4 py-3"><x-badge :color="$post->status->badgeColor()">{{ $post->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-400">{{ $post->published_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.blog.edit', $post) }}" class="text-brand-600 font-medium hover:underline text-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" class="inline" onsubmit="return confirm('Delete this post?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-medium hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No posts yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$posts" />
    </x-card>
@endsection
