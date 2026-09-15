@extends($layout)

@section('title', $ticket->ticket_number)

@section($section)
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                <p class="text-xs text-gray-400">{{ $ticket->ticket_number }} · {{ ucfirst($ticket->category) }}</p>
            </div>
            <x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge>
        </div>

        <x-card>
            <div class="space-y-4">
                @foreach ($ticket->messages as $message)
                    <div class="{{ $message->sender_id === auth()->id() ? 'ml-auto text-right' : '' }} max-w-lg">
                        <div class="inline-block {{ $message->sender_id === auth()->id() ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800' }} rounded-md px-4 py-2 text-sm text-left">
                            <p>{{ $message->body }}</p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $message->sender->name }} · {{ $message->created_at->format('M j, g:ia') }}</p>
                    </div>
                @endforeach
            </div>

            @unless (in_array($ticket->status->value, ['resolved', 'closed']))
                <form method="POST" action="{{ route($routePrefix.'support.reply', $ticket) }}" class="mt-6 pt-4 border-t border-gray-100 space-y-3">
                    @csrf
                    <x-textarea label="Reply" name="body" rows="3" required />
                    <x-button type="submit">Send Reply</x-button>
                </form>
            @endunless
        </x-card>
    </div>
@endsection
