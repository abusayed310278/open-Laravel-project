@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="flex justify-end mb-5">
        <x-button as="a" :href="route('admin.pages.create')">New Page</x-button>
    </div>

    <x-card>
        <x-table :headers="['Title', 'Slug', 'Status', 'Updated', '']" id="pages-table">
            @forelse ($pages as $page)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-800 font-medium">{{ $page->title }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">/p/{{ $page->slug }}</td>
                    <td class="px-4 py-3"><x-badge :color="$page->status->badgeColor()">{{ $page->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-400">{{ $page->updated_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-brand-600 font-medium hover:underline text-sm">Edit</a>
                        <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Delete this page?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 font-medium hover:underline text-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No pages yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$pages" />
    </x-card>
@endsection
