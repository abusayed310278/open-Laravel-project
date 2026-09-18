@extends($layout)

@section('title', 'Conversation')

@section($section)
    @php
        $other = $conversation->otherParty(auth()->user());
        $avatarUrl = $other->profile?->avatar ? \Illuminate\Support\Facades\Storage::url($other->profile->avatar) : null;
        $initials = strtoupper(substr($other->name, 0, 2));
        $lastId = $messages->last()?->id ?? 0;
    @endphp

    <div class="bg-white border border-gray-200/70 rounded-2xl shadow-xs flex flex-col h-[78vh] overflow-hidden">
        {{-- Chat Top Header --}}
        <div class="px-5 py-3.5 border-b border-gray-100 bg-white flex items-center justify-between z-10 shrink-0">
            <div class="flex items-center gap-3.5 min-w-0">
                <a href="{{ route($routePrefix.'messages.index') }}" class="p-1.5 -ml-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition cursor-pointer" title="Back to Messages">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>

                {{-- User Avatar & Online Indicator --}}
                <div class="relative shrink-0">
                    @if ($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $other->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-100 shadow-2xs">
                    @else
                        <div class="w-10 h-10 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-xs shadow-2xs">
                            {{ $initials }}
                        </div>
                    @endif
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                </div>

                <div class="min-w-0">
                    <p class="font-bold text-gray-950 text-sm sm:text-base leading-tight truncate">{{ $other->name }}</p>
                    <p class="text-[11px] text-emerald-600 font-medium flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Active</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route($routePrefix.'messages.index') }}" class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-800 px-3 py-1.5 rounded-xl hover:bg-gray-50 border border-gray-200/80 transition">
                    <span>All Chats</span>
                </a>
            </div>
        </div>

        {{-- Chat Messages Scroll Area --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3.5 bg-slate-50/60">
            @forelse ($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                @endphp
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start items-end gap-2' }}">
                    @if (!$isMine)
                        <div class="shrink-0 mb-0.5">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $other->name }}" class="w-7 h-7 rounded-full object-cover border border-gray-100 shadow-2xs">
                            @else
                                <div class="w-7 h-7 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shadow-2xs">
                                    {{ $initials }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="max-w-[85%] sm:max-w-md {{ $isMine ? 'bg-amber-500 text-white rounded-2xl rounded-br-xs shadow-xs' : 'bg-white text-gray-900 border border-gray-200/70 rounded-2xl rounded-bl-xs shadow-xs' }} px-4 py-2.5">
                        @if ($message->body)
                            <p class="whitespace-pre-wrap break-words text-[13px] sm:text-sm leading-relaxed {{ $isMine ? 'text-white' : 'text-gray-800' }}">{{ $message->body }}</p>
                        @endif

                        @if ($message->attachment_path)
                            <div class="mt-2 pt-2 {{ $isMine ? 'border-t border-amber-400/60' : 'border-t border-gray-100' }}">
                                <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="inline-flex items-center gap-1.5 {{ $isMine ? 'text-white/95 hover:text-white bg-white/20 hover:bg-white/30' : 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60' }} px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span>Attachment</span>
                                </a>
                            </div>
                        @endif

                        <div class="flex items-center {{ $isMine ? 'justify-end text-amber-100/90' : 'justify-start text-gray-400' }} gap-1 mt-1 text-[10px] font-medium">
                            <span>{{ $message->created_at->format('g:i A') }}</span>
                            @if ($isMine)
                                <svg class="w-3 h-3 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-8 text-gray-400">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 mb-3 shadow-2xs">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-800">No messages yet</p>
                    <p class="text-xs text-gray-400 mt-0.5">Send a message below to start your conversation with {{ $other->name }}.</p>
                </div>
            @endforelse
        </div>

        {{-- Chat Message Input Bar --}}
        <div class="border-t border-gray-100 bg-white p-3 sm:p-4 shrink-0">
            {{-- File Attachment Preview Badge --}}
            <div id="attachment-preview-container" class="hidden mb-2.5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-semibold">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span id="attachment-filename" class="truncate max-w-xs">File attached</span>
                    <button type="button" onclick="clearAttachment()" class="text-amber-700 hover:text-red-600 ml-1 cursor-pointer font-bold">×</button>
                </span>
            </div>

            <form method="POST" action="{{ route('chat.store', $conversation) }}" enctype="multipart/form-data" class="flex items-center gap-2.5" id="chat-form">
                @csrf
                {{-- Attachment Paperclip --}}
                <label class="p-2.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition cursor-pointer shrink-0" title="Attach image or file">
                    <input type="file" name="attachment" id="chat-attachment-input" class="hidden" onchange="handleAttachmentSelect(this)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </label>

                {{-- Input Box --}}
                <div class="relative flex-1">
                    <input
                        type="text"
                        name="body"
                        id="chat-body-input"
                        placeholder="Type your message..."
                        autocomplete="off"
                        class="w-full bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 focus:border-amber-400 focus:ring-3 focus:ring-amber-400/20 rounded-xl px-4 py-2.5 text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition-all outline-none"
                    >
                </div>

                {{-- Send Button --}}
                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 px-4 sm:px-5 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-white text-xs sm:text-sm font-bold rounded-xl shadow-xs transition-all cursor-pointer shrink-0"
                >
                    <span>Send</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('chat-messages');
            const conversationId = {{ $conversation->id }};
            const meId = {{ auth()->id() }};
            const otherAvatar = @json($avatarUrl);
            const otherInitials = @json($initials);
            let lastId = {{ $lastId }};

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            function appendMessage(message) {
                const mine = message.sender_id === meId;
                const wrapper = document.createElement('div');
                wrapper.className = 'flex ' + (mine ? 'justify-end' : 'justify-start items-end gap-2');

                let avatarHtml = '';
                if (!mine) {
                    if (otherAvatar) {
                        avatarHtml = `<div class="shrink-0 mb-0.5"><img src="${otherAvatar}" alt="Avatar" class="w-7 h-7 rounded-full object-cover border border-gray-100 shadow-2xs"></div>`;
                    } else {
                        avatarHtml = `<div class="shrink-0 mb-0.5"><div class="w-7 h-7 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shadow-2xs">${otherInitials}</div></div>`;
                    }
                }

                const bubbleClasses = mine
                    ? 'bg-amber-500 text-white rounded-2xl rounded-br-xs shadow-xs'
                    : 'bg-white text-gray-900 border border-gray-200/70 rounded-2xl rounded-bl-xs shadow-xs';

                let attachmentHtml = '';
                if (message.attachment_url) {
                    const attachClasses = mine
                        ? 'text-white/95 hover:text-white bg-white/20 hover:bg-white/30'
                        : 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60';
                    attachmentHtml = `
                        <div class="mt-2 pt-2 ${mine ? 'border-t border-amber-400/60' : 'border-t border-gray-100'}">
                            <a href="${message.attachment_url}" target="_blank" class="inline-flex items-center gap-1.5 ${attachClasses} px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>Attachment</span>
                            </a>
                        </div>`;
                }

                const checkmark = mine ? '<svg class="w-3 h-3 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' : '';

                wrapper.innerHTML = `
                    ${avatarHtml}
                    <div class="max-w-[85%] sm:max-w-md ${bubbleClasses} px-4 py-2.5">
                        ${message.body ? `<p class="whitespace-pre-wrap break-words text-[13px] sm:text-sm leading-relaxed ${mine ? 'text-white' : 'text-gray-800'}">${escapeHtml(message.body)}</p>` : ''}
                        ${attachmentHtml}
                        <div class="flex items-center ${mine ? 'justify-end text-amber-100/90' : 'justify-start text-gray-400'} gap-1 mt-1 text-[10px] font-medium">
                            <span>${message.created_at}</span>
                            ${checkmark}
                        </div>
                    </div>
                `;

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
            setInterval(poll, 3500);
        })();

        function handleAttachmentSelect(input) {
            const container = document.getElementById('attachment-preview-container');
            const filenameSpan = document.getElementById('attachment-filename');
            if (input.files && input.files[0]) {
                filenameSpan.textContent = input.files[0].name;
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function clearAttachment() {
            const input = document.getElementById('chat-attachment-input');
            const container = document.getElementById('attachment-preview-container');
            if (input) input.value = '';
            if (container) container.classList.add('hidden');
        }
    </script>
@endsection
