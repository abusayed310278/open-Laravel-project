@extends($layout)

@section('title', 'Messages')

@section($section)
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Messages</h1>
            <p class="text-xs sm:text-sm text-gray-500">Real-time conversations with sellers, buyers, and team members.</p>
        </div>

        <x-card>
            <div class="divide-y divide-gray-100">
                @forelse ($conversations as $conversation)
                    @php
                        $other = $conversation->otherParty(auth()->user());
                        $avatarUrl = $other->profile?->avatar ? \Illuminate\Support\Facades\Storage::url($other->profile->avatar) : null;
                        $initials = strtoupper(substr($other->name, 0, 2));
                    @endphp
                    <a href="{{ route($routePrefix.'chat.show', $conversation) }}" class="flex items-center justify-between py-4 px-3 -mx-1 hover:bg-amber-50/40 rounded-xl transition-all group">
                        <div class="flex items-center gap-3.5 min-w-0">
                            {{-- User Avatar --}}
                            <div class="relative shrink-0">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $other->name }}" class="w-11 h-11 rounded-full object-cover border border-gray-100 shadow-2xs group-hover:scale-105 transition-transform">
                                @else
                                    <div class="w-11 h-11 rounded-full bg-amber-400 text-gray-950 font-bold flex items-center justify-center text-xs shadow-2xs group-hover:scale-105 transition-transform">
                                        {{ $initials }}
                                    </div>
                                @endif
                                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-950 group-hover:text-amber-700 transition-colors truncate">{{ $other->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Active Conversation</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            <span class="text-xs text-gray-400 font-medium">{{ $conversation->last_message_at?->diffForHumans() ?? 'Active' }}</span>
                            <div class="w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-amber-500 group-hover:text-white text-gray-400 flex items-center justify-center transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="py-16 text-center text-gray-400">
                        <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/60 flex items-center justify-center text-amber-600 mx-auto mb-3 shadow-2xs">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">No conversations yet</p>
                        <p class="text-xs text-gray-400 mt-0.5">When you message or receive inquiries, your chats will appear here.</p>
                    </div>
                @endforelse
            </div>

            @if ($conversations->hasPages())
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <x-pagination :paginator="$conversations" />
                </div>
            @endif
        </x-card>
    </div>
@endsection
