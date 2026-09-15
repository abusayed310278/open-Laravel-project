@extends($layout)

@section('title', 'Conversation')

@section($section)
    @php
        $other = $conversation->otherParty(auth()->user());
        $lastId = $messages->last()?->id ?? 0;
    @endphp

    <div class="bg-white border border-gray-100 rounded-md flex flex-col h-[70vh]">
        <div class="px-5 py-4 border-b border-gray-100">
            <p class="font-semibold text-gray-900">{{ $other->name }}</p>
            @if ($conversation->product)
                <p class="text-xs text-gray-400">Re: {{ $conversation->product->title }}</p>
            @endif
        </div>

        <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
            @foreach ($messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs {{ $message->sender_id === auth()->id() ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800' }} rounded-md px-4 py-2 text-sm">
                        @if ($message->body)
                            <p>{{ $message->body }}</p>
                        @endif
                        @if ($message->attachment_path)
                            <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="underline text-xs">Attachment</a>
                        @endif
                        <p class="text-xs opacity-70 mt-1">{{ $message->created_at->format('M j, g:ia') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('chat.store', $conversation) }}" enctype="multipart/form-data" class="border-t border-gray-100 p-4 flex items-center gap-3">
            @csrf
            <input type="text" name="body" placeholder="Write a message..." class="flex-1 border border-gray-200 rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400">
            <label class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <input type="file" name="attachment" class="hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
            </label>
            <x-button type="submit">Send</x-button>
        </form>
    </div>

    <script>
        (function () {
            const container = document.getElementById('chat-messages');
            const conversationId = {{ $conversation->id }};
            const meId = {{ auth()->id() }};
            let lastId = {{ $lastId }};

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function appendMessage(message) {
                const mine = message.sender_id === meId;
                const wrapper = document.createElement('div');
                wrapper.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');

                const bubble = document.createElement('div');
                bubble.className = 'max-w-xs ' + (mine ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-800') + ' rounded-md px-4 py-2 text-sm';

                let html = '';
                if (message.body) {
                    html += '<p>' + escapeHtml(message.body) + '</p>';
                }
                if (message.attachment_url) {
                    html += '<a href="' + message.attachment_url + '" target="_blank" class="underline text-xs">Attachment</a>';
                }
                html += '<p class="text-xs opacity-70 mt-1">' + message.created_at + '</p>';
                bubble.innerHTML = html;

                wrapper.appendChild(bubble);
                container.appendChild(wrapper);
                container.scrollTop = container.scrollHeight;
            }

            function poll() {
                fetch('/chat/' + conversationId + '/poll/' + lastId)
                    .then((r) => r.json())
                    .then((messages) => {
                        messages.forEach((m) => {
                            appendMessage(m);
                            lastId = m.id;
                        });
                    })
                    .catch(() => {});
            }

            container.scrollTop = container.scrollHeight;
            setInterval(poll, 4000);
        })();
    </script>
@endsection
