@extends('layouts.admin')

@section('title', 'Conversation')

@section('content')
    @php
        $isAdminParticipant = ($conversation->buyer_id === auth()->id() || $conversation->seller_id === auth()->id());
        $other = $isAdminParticipant ? $conversation->otherParty(auth()->user()) : null;
        $otherAvatar = \App\Support\MediaUrl::resolve($other?->profile?->avatar);
        $otherInitials = $other ? strtoupper(substr($other->name, 0, 2)) : '';
        $lastId = $messages->last()?->id ?? 0;
    @endphp

    <div class="bg-white border border-gray-200/70 rounded-2xl shadow-xs flex flex-col h-[78vh] max-w-2xl sm:max-w-3xl mx-auto overflow-hidden relative">
        {{-- Chat Top Header --}}
        <div class="px-4 py-3 border-b border-gray-100 bg-white flex items-center justify-between z-10 shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('admin.chat.index') }}" class="p-1.5 -ml-1 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition cursor-pointer" title="Back to All Chats">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>

                @if ($other)
                    {{-- 1-on-1 Direct Chat with Admin --}}
                    <div class="relative shrink-0">
                        @if ($otherAvatar)
                            <img src="{{ $otherAvatar }}" alt="{{ $other->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-100 shadow-2xs">
                        @else
                            <div class="w-9 h-9 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-xs shadow-2xs">
                                {{ $otherInitials }}
                            </div>
                        @endif
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-gray-950 text-sm leading-tight truncate">{{ $other->name }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold {{ $other->role->badgeClass() }}">
                                {{ $other->role->label() }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $other->email }}</p>
                    </div>
                @else
                    {{-- Multi-party marketplace thread view --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-xs sm:text-sm font-bold text-gray-950">
                            <span>{{ $conversation->buyer->name }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $conversation->buyer->role->badgeClass() }}">{{ $conversation->buyer->role->label() }}</span>
                            <span class="text-gray-400">↔</span>
                            <span>{{ $conversation->seller->name }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $conversation->seller->role->badgeClass() }}">{{ $conversation->seller->role->label() }}</span>
                        </div>
                    </div>
                @endif
            </div>

            @if ($conversation->product)
                <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 border border-gray-200/80 rounded-xl text-xs text-gray-700 max-w-xs truncate" title="{{ $conversation->product->title }}">
                    <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="truncate font-medium">{{ $conversation->product->title }}</span>
                </div>
            @endif
        </div>

        {{-- Chat Messages Scroll Area --}}
        <div id="admin-chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3.5 bg-slate-50/60">
            @forelse ($messages as $message)
                @php
                    $isMine = $message->sender_id === auth()->id();
                    $senderAvatar = \App\Support\MediaUrl::resolve($message->sender->profile?->avatar);
                    $senderInitials = strtoupper(substr($message->sender->name, 0, 2));
                @endphp
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start items-end gap-2.5' }}">
                    @if (!$isMine)
                        <div class="shrink-0 mb-6" title="{{ $message->sender->name }} ({{ $message->sender->role->label() }})">
                            @if ($senderAvatar)
                                <img src="{{ $senderAvatar }}" alt="{{ $message->sender->name }}" class="w-7 h-7 rounded-full object-cover border border-gray-100 shadow-2xs">
                            @else
                                <div class="w-7 h-7 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shadow-2xs">
                                    {{ $senderInitials }}
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="max-w-[85%] sm:max-w-md flex flex-col {{ $isMine ? 'items-end' : 'items-start' }}">
                        @if (!$isMine && !$other)
                            <div class="flex items-center gap-1.5 mb-1 px-1">
                                <span class="text-xs font-bold text-gray-900">{{ $message->sender->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-semibold {{ $message->sender->role->badgeClass() }}">
                                    {{ $message->sender->role->label() }}
                                </span>
                            </div>
                        @endif

                        @if ($message->isImage() && !$message->body)
                            {{-- Pure Image Bubble with subtle border --}}
                            <div class="{{ $isMine ? 'bg-amber-500 rounded-2xl rounded-br-xs shadow-xs' : 'bg-white border border-gray-200/80 rounded-2xl rounded-bl-xs shadow-xs' }} p-1">
                                <div class="relative group/img overflow-hidden rounded-[12px] bg-black/5 cursor-pointer" onclick="openImageLightbox('{{ route('chat.attachment', $message) }}')">
                                    <img src="{{ route('chat.attachment', $message) }}" alt="Attachment image" class="max-h-64 sm:max-h-72 w-auto max-w-full rounded-[12px] object-cover hover:opacity-95 transition-opacity">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">
                                        Click to expand
                                    </div>
                                </div>
                            </div>
                        @elseif ($message->isImage() && $message->body)
                            {{-- Image with text body --}}
                            <div class="{{ $isMine ? 'bg-amber-500 text-white rounded-2xl rounded-br-xs shadow-xs' : 'bg-white text-gray-900 border border-gray-200/70 rounded-2xl rounded-bl-xs shadow-xs' }} p-1.5 space-y-1.5">
                                <div class="relative group/img overflow-hidden rounded-xl bg-black/5 cursor-pointer" onclick="openImageLightbox('{{ route('chat.attachment', $message) }}')">
                                    <img src="{{ route('chat.attachment', $message) }}" alt="Attachment image" class="max-h-64 sm:max-h-72 w-auto max-w-full rounded-xl object-cover hover:opacity-95 transition-opacity">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">
                                        Click to expand
                                    </div>
                                </div>
                                <div class="px-2 pb-1 pt-0.5">
                                    <p class="whitespace-pre-wrap break-words text-xs sm:text-sm leading-relaxed {{ $isMine ? 'text-white' : 'text-gray-800' }}">{{ $message->body }}</p>
                                </div>
                            </div>
                        @elseif ($message->isPdf() && !$message->body)
                            {{-- PDF Document Bubble (Direct Pill) --}}
                            <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl {{ $isMine ? 'rounded-br-xs bg-amber-500 text-white hover:bg-amber-600' : 'rounded-bl-xs bg-red-50 text-red-700 hover:bg-red-100 border border-red-200/70' }} text-xs font-semibold shadow-xs transition">
                                <svg class="w-5 h-5 shrink-0 {{ $isMine ? 'text-white' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                                <span class="truncate max-w-[180px]">{{ $message->attachmentName() }}</span>
                                <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">PDF</span>
                            </a>
                        @elseif ($message->attachment_path && !$message->body)
                            {{-- General Document Bubble (Direct Pill) --}}
                            <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl {{ $isMine ? 'rounded-br-xs bg-amber-500 text-white hover:bg-amber-600' : 'rounded-bl-xs bg-white text-gray-800 hover:bg-gray-50 border border-gray-200' }} text-xs font-semibold shadow-xs transition">
                                <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate max-w-[160px]">{{ $message->attachmentName() }}</span>
                                <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">{{ $message->attachmentExtension() }}</span>
                            </a>
                        @else
                            {{-- Standard Text / Text with document attachment --}}
                            <div class="{{ $isMine ? 'bg-amber-500 text-white rounded-2xl rounded-br-xs shadow-xs' : 'bg-white text-gray-900 border border-gray-200/70 rounded-2xl rounded-bl-xs shadow-xs' }} p-3 space-y-2">
                                @if ($message->isPdf())
                                    <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold {{ $isMine ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200/60' }} transition">
                                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                                        <span class="truncate max-w-[180px]">{{ $message->attachmentName() }}</span>
                                        <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">PDF</span>
                                    </a>
                                @elseif ($message->attachment_path)
                                    <a href="{{ route('chat.attachment', $message) }}" target="_blank" class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold {{ $isMine ? 'bg-white/20 text-white hover:bg-white/30' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200/60' }} transition">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <span class="truncate max-w-[160px]">{{ $message->attachmentName() }}</span>
                                        <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">{{ $message->attachmentExtension() }}</span>
                                    </a>
                                @endif

                                @if ($message->body)
                                    <p class="whitespace-pre-wrap break-words text-xs sm:text-sm leading-relaxed {{ $isMine ? 'text-white' : 'text-gray-800' }}">{{ $message->body }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Time --}}
                        <div class="text-[10px] text-gray-400 mt-1 px-1">
                            {{ $message->created_at->format('g:i A') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="h-full flex flex-col items-center justify-center text-center p-6 text-gray-400">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 mb-2 shadow-2xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-800">No messages yet</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Send a message below to start communicating.</p>
                </div>
            @endforelse
        </div>

        {{-- Chat Message Input Bar --}}
        <div class="border-t border-gray-100 bg-white p-3 shrink-0">
            {{-- Facebook-Style Pre-Send Attachment Preview Box --}}
            <div id="attachment-preview-container" class="hidden mb-2 flex items-center gap-2">
                <div class="relative inline-flex items-center gap-2 p-1.5 bg-gray-50 border border-gray-200 rounded-xl">
                    <img id="attachment-preview-img" src="" alt="Image preview" class="w-14 h-14 object-cover rounded-lg hidden border border-gray-200">
                    <div id="attachment-file-icon" class="w-10 h-10 rounded-lg bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-xs hidden">
                        <span id="attachment-ext-label">DOC</span>
                    </div>
                    <div class="min-w-0 pr-2">
                        <p id="attachment-filename" class="text-xs font-semibold text-gray-800 truncate max-w-xs">File attached</p>
                        <p class="text-[10px] text-emerald-600 font-medium">Ready to send</p>
                    </div>
                    <button type="button" onclick="clearAttachment()" class="w-5 h-5 rounded-full bg-gray-200 hover:bg-red-500 hover:text-white text-gray-600 flex items-center justify-center text-xs font-bold transition cursor-pointer">✕</button>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.chat.store', $conversation) }}" enctype="multipart/form-data" class="flex items-center gap-2" id="chat-form">
                @csrf
                {{-- Attachment Paperclip --}}
                <label class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition cursor-pointer shrink-0" title="Attach image, PDF or document">
                    <input type="file" name="attachment" id="chat-attachment-input" accept="image/*,application/pdf,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar" class="hidden" onchange="handleAttachmentSelect(this)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                </label>

                {{-- Input Box --}}
                <div class="relative flex-1">
                    <input
                        type="text"
                        name="body"
                        id="chat-body-input"
                        placeholder="Type a message..."
                        autocomplete="off"
                        class="w-full bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20 rounded-xl px-3.5 py-2 text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition-all outline-none"
                    >
                </div>

                {{-- Send Button --}}
                <button
                    type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-[0.98] text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer shrink-0"
                >
                    <span>Send</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Facebook Style HD Image Lightbox Modal --}}
    <div id="image-lightbox-modal" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden flex items-center justify-center p-4 cursor-pointer" onclick="closeImageLightbox()">
        <div class="relative max-w-5xl max-h-[90vh] flex flex-col items-center justify-center" onclick="event.stopPropagation()">
            <img id="lightbox-image-target" src="" alt="Full view" class="max-h-[85vh] max-w-full object-contain rounded-2xl shadow-2xl">
            <button type="button" onclick="closeImageLightbox()" class="absolute -top-10 right-0 text-white hover:text-amber-400 text-2xl font-bold p-2 cursor-pointer">✕</button>
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('admin-chat-messages');
            const conversationId = {{ $conversation->id }};
            const meId = {{ auth()->id() }};
            let lastId = {{ $lastId }};

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            window.openImageLightbox = function(url) {
                const modal = document.getElementById('image-lightbox-modal');
                const img = document.getElementById('lightbox-image-target');
                if (modal && img) {
                    img.src = url;
                    modal.classList.remove('hidden');
                }
            };

            window.closeImageLightbox = function() {
                const modal = document.getElementById('image-lightbox-modal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

            function appendMessage(message) {
                const mine = message.sender_id === meId;
                const wrapper = document.createElement('div');
                wrapper.className = 'flex ' + (mine ? 'justify-end' : 'justify-start items-end gap-2.5');

                let avatarHtml = '';
                if (!mine) {
                    const initials = (message.sender_name || 'U').substring(0, 2).toUpperCase();
                    avatarHtml = `<div class="shrink-0 mb-6"><div class="w-7 h-7 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-[10px] shadow-2xs">${initials}</div></div>`;
                }

                let contentHtml = '';
                const hasBody = Boolean(message.body && message.body.trim());

                if (message.is_image) {
                    const imgBorderClass = mine
                        ? 'bg-amber-500 rounded-2xl rounded-br-xs shadow-xs'
                        : 'bg-white border border-gray-200/80 rounded-2xl rounded-bl-xs shadow-xs';
                    const paddingClass = hasBody ? 'p-1.5 space-y-1.5' : 'p-1';
                    const bodyPart = hasBody ? `<div class="px-2 pb-1 pt-0.5"><p class="whitespace-pre-wrap break-words text-xs sm:text-sm leading-relaxed ${mine ? 'text-white' : 'text-gray-800'}">${escapeHtml(message.body)}</p></div>` : '';

                    contentHtml = `
                        <div class="${imgBorderClass} ${paddingClass}">
                            <div class="relative group/img overflow-hidden rounded-[12px] bg-black/5 cursor-pointer" onclick="openImageLightbox('${message.attachment_url}')">
                                <img src="${message.attachment_url}" alt="Attachment image" class="max-h-64 sm:max-h-72 w-auto max-w-full rounded-[12px] object-cover hover:opacity-95 transition-opacity">
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold">
                                    Click to expand
                                </div>
                            </div>
                            ${bodyPart}
                        </div>`;
                } else if (message.is_pdf && !hasBody) {
                    const pdfClasses = mine
                        ? 'rounded-br-xs bg-amber-500 text-white hover:bg-amber-600'
                        : 'rounded-bl-xs bg-red-50 text-red-700 hover:bg-red-100 border border-red-200/70';
                    contentHtml = `
                        <a href="${message.attachment_url}" target="_blank" class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl ${pdfClasses} text-xs font-semibold shadow-xs transition">
                            <svg class="w-5 h-5 shrink-0 ${mine ? 'text-white' : 'text-red-500'}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                            <span class="truncate max-w-[180px]">${escapeHtml(message.attachment_name || 'Document.pdf')}</span>
                            <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">PDF</span>
                        </a>`;
                } else if (message.attachment_url && !hasBody) {
                    const docClasses = mine
                        ? 'rounded-br-xs bg-amber-500 text-white hover:bg-amber-600'
                        : 'rounded-bl-xs bg-white text-gray-800 hover:bg-gray-50 border border-gray-200';
                    contentHtml = `
                        <a href="${message.attachment_url}" target="_blank" class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl ${docClasses} text-xs font-semibold shadow-xs transition">
                            <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            <span class="truncate max-w-[160px]">${escapeHtml(message.attachment_name || 'Download Attachment')}</span>
                            <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">${escapeHtml(message.attachment_ext || 'FILE')}</span>
                        </a>`;
                } else {
                    const bubbleClasses = mine
                        ? 'bg-amber-500 text-white rounded-2xl rounded-br-xs shadow-xs'
                        : 'bg-white text-gray-900 border border-gray-200/70 rounded-2xl rounded-bl-xs shadow-xs';
                    let attachmentPart = '';
                    if (message.attachment_url) {
                        if (message.is_pdf) {
                            const pdfClasses = mine
                                ? 'bg-white/20 text-white hover:bg-white/30'
                                : 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200/60';
                            attachmentPart = `
                                <a href="${message.attachment_url}" target="_blank" class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold ${pdfClasses} transition">
                                    <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                                    <span class="truncate max-w-[180px]">${escapeHtml(message.attachment_name || 'Document.pdf')}</span>
                                    <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">PDF</span>
                                </a>`;
                        } else {
                            const attachClasses = mine
                                ? 'text-white bg-white/20 hover:bg-white/30'
                                : 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60';
                            attachmentPart = `
                                <a href="${message.attachment_url}" target="_blank" class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-xl text-xs font-semibold ${attachClasses} transition">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="truncate max-w-[160px]">${escapeHtml(message.attachment_name || 'Download Attachment')}</span>
                                    <span class="text-[10px] uppercase font-bold opacity-80 px-1.5 py-0.5 rounded bg-black/10">${escapeHtml(message.attachment_ext || 'FILE')}</span>
                                </a>`;
                        }
                    }

                    const bodyPart = message.body ? `<p class="whitespace-pre-wrap break-words text-xs sm:text-sm leading-relaxed ${mine ? 'text-white' : 'text-gray-800'}">${escapeHtml(message.body)}</p>` : '';

                    contentHtml = `
                        <div class="${bubbleClasses} p-3 space-y-2">
                            ${attachmentPart}
                            ${bodyPart}
                        </div>`;
                }

                wrapper.innerHTML = `
                    ${avatarHtml}
                    <div class="max-w-[85%] sm:max-w-md flex flex-col ${mine ? 'items-end' : 'items-start'}">
                        ${!mine ? `<p class="text-xs font-bold text-gray-900 mb-1 px-1">${escapeHtml(message.sender_name)}</p>` : ''}
                        ${contentHtml}
                        <div class="text-[10px] text-gray-400 mt-1 px-1">
                            ${message.created_at}
                        </div>
                    </div>
                `;

                container.appendChild(wrapper);
                container.scrollTop = container.scrollHeight;
            }

            function poll() {
                fetch('/admin/chat/' + conversationId + '/poll/' + lastId)
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
            const previewImg = document.getElementById('attachment-preview-img');
            const fileIcon = document.getElementById('attachment-file-icon');
            const extLabel = document.getElementById('attachment-ext-label');
            const filenameSpan = document.getElementById('attachment-filename');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                filenameSpan.textContent = file.name;
                const ext = file.name.split('.').pop().toUpperCase() || 'FILE';

                if (file.type && file.type.startsWith('image/')) {
                    if (previewImg) {
                        previewImg.src = URL.createObjectURL(file);
                        previewImg.classList.remove('hidden');
                    }
                    if (fileIcon) fileIcon.classList.add('hidden');
                } else {
                    if (previewImg) previewImg.classList.add('hidden');
                    if (fileIcon && extLabel) {
                        extLabel.textContent = ext;
                        fileIcon.classList.remove('hidden');
                    }
                }
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }

        function clearAttachment() {
            const input = document.getElementById('chat-attachment-input');
            const container = document.getElementById('attachment-preview-container');
            const previewImg = document.getElementById('attachment-preview-img');
            const fileIcon = document.getElementById('attachment-file-icon');
            if (input) input.value = '';
            if (previewImg) {
                previewImg.src = '';
                previewImg.classList.add('hidden');
            }
            if (fileIcon) fileIcon.classList.add('hidden');
            if (container) container.classList.add('hidden');
        }
    </script>
@endsection
