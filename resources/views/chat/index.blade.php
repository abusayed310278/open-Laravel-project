@extends($layout)

@section('title', 'Messages')

@section($section)
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-gray-900">Messages</h1>

        <x-card>
            @forelse ($conversations as $conversation)
                @php
                    $other = $conversation->otherParty(auth()->user());
                @endphp
                <a href="{{ route($routePrefix.'chat.show', $conversation) }}" class="flex items-center justify-between py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-md">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $other->name }}</p>
                        @if ($conversation->product)
                            <p class="text-xs text-gray-400">Re: {{ $conversation->product->title }}</p>
                        @endif
                        @if ($conversation->latestMessage)
                            <p class="text-sm text-gray-500 mt-1 truncate max-w-md">{{ $conversation->latestMessage->body }}</p>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400">{{ $conversation->last_message_at?->diffForHumans() }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-400 text-center py-10">No conversations yet.</p>
            @endforelse

            <x-pagination :paginator="$conversations" />
        </x-card>
    </div>
@endsection
