@extends('layouts.admin')

@section('title', 'Conversation')

@section('content')
    <x-card :title="$conversation->buyer->name.' ↔ '.$conversation->seller->name">
        <div class="space-y-3">
            @foreach ($messages as $message)
                <div class="flex {{ $message->sender_id === $conversation->buyer_id ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-md {{ $message->sender_id === $conversation->buyer_id ? 'bg-gray-100 text-gray-800' : 'bg-brand-50 text-gray-800' }} rounded-md px-4 py-2 text-sm">
                        <p class="text-xs font-medium text-gray-500 mb-1">{{ $message->sender->name }}</p>
                        @if ($message->body)
                            <p>{{ $message->body }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('M j, g:ia') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
@endsection
