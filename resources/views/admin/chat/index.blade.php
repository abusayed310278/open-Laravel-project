@extends('layouts.admin')

@section('title', 'Chat')

@section('content')
    <x-card>
        <x-table :headers="['Buyer', 'Seller', 'Product', 'Last Message', '']" id="admin-chat-table">
            @forelse ($conversations as $conversation)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $conversation->buyer->name }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $conversation->seller->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $conversation->product?->title ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $conversation->last_message_at?->diffForHumans() ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.chat.show', $conversation) }}" class="text-brand-600 font-medium hover:underline text-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No conversations yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$conversations" />
    </x-card>
@endsection
