@extends('layouts.admin')

@section('title', 'Admin Chat & User Messaging')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-950">Chat & Communication Hub</h1>
            <p class="text-xs sm:text-sm text-gray-500">Directly message any user across all platform roles and manage active conversations.</p>
        </div>
    </div>

    {{-- Metric Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card
            label="Total Platform Users"
            :value="$roleCounts['all']"
            hint="Available to chat"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'
        />

        <x-stat-card
            label="Sellers & Stores"
            :value="$roleCounts['saler'] + $roleCounts['business']"
            hint="Merchants & Store owners"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'
        />

        <x-stat-card
            label="Verifiers & Team"
            :value="$roleCounts['verifier']"
            hint="Quality inspection officers"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
        />

        <x-stat-card
            label="Total Conversations"
            :value="$conversations->total()"
            hint="Active marketplace threads"
            icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>'
        />
    </div>

    {{-- Main Container Card --}}
    <x-card>
        {{-- Navigation Tabs --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-3.5 mb-4">
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('admin.chat.index', ['tab' => 'users', 'role' => $currentRole, 'search' => $search]) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-colors {{ $tab === 'users' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>All User Roles ({{ $roleCounts['all'] }})</span>
                </a>

                <a
                    href="{{ route('admin.chat.index', ['tab' => 'conversations', 'search' => $search]) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs sm:text-sm font-semibold transition-colors {{ $tab === 'conversations' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:bg-gray-100' }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>Active Conversations ({{ $conversations->total() }})</span>
                </a>
            </div>
        </div>

        {{-- Filter & Search Toolbar --}}
        <form method="GET" action="{{ route('admin.chat.index') }}" class="flex flex-wrap items-center gap-3 mb-5">
            <input type="hidden" name="tab" value="{{ $tab }}">

            {{-- Role Pills (when in Users tab) --}}
            @if ($tab === 'users')
                <div class="flex flex-wrap items-center gap-1.5 flex-1 min-w-[280px]">
                    <a
                        href="{{ route('admin.chat.index', ['tab' => 'users', 'search' => $search]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ !$currentRole ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        All ({{ $roleCounts['all'] }})
                    </a>

                    @foreach ($roles as $role)
                        @if ($role->value !== 'admin')
                            <a
                                href="{{ route('admin.chat.index', ['tab' => 'users', 'role' => $role->value, 'search' => $search]) }}"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentRole === $role->value ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                            >
                                {{ $role->label() }} ({{ $roleCounts[$role->value] ?? 0 }})
                            </a>
                        @endif
                    @endforeach

                    <a
                        href="{{ route('admin.chat.index', ['tab' => 'users', 'role' => 'admin', 'search' => $search]) }}"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentRole === 'admin' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        Admins ({{ $roleCounts['admin'] }})
                    </a>
                </div>
            @endif

            {{-- Search Box --}}
            <div class="{{ $tab === 'users' ? 'w-full sm:w-72' : 'flex-1 min-w-[240px]' }}">
                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="{{ $tab === 'users' ? 'Search by name, email, phone...' : 'Search conversations, users...' }}"
                        class="w-full pl-9 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-800 placeholder:text-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs sm:text-sm font-semibold rounded-xl transition-colors cursor-pointer">
                Filter
            </button>

            @if ($search || $currentRole)
                <a href="{{ route('admin.chat.index', ['tab' => $tab]) }}" class="text-xs text-gray-500 hover:text-red-600 underline">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tab 1: All Users Directory (Chat with Any Role) --}}
        @if ($tab === 'users')
            <x-table :headers="['User', 'Role', 'Email', 'Phone', 'Joined', 'Action']" id="admin-users-chat-table">
                @forelse ($users as $user)
                    @php
                        $avatarUrl = \App\Support\MediaUrl::resolve($user->profile?->avatar);
                        $initials = strtoupper(substr($user->name, 0, 2));
                    @endphp
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        {{-- User Avatar & Name --}}
                        <td class="px-4 py-2.5 font-medium text-gray-900">
                            <div class="flex items-center gap-2.5">
                                <div class="relative shrink-0">
                                    @if ($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="w-9 h-9 rounded-full object-cover border border-gray-100">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gray-100 text-gray-600 font-bold flex items-center justify-center text-xs">
                                            {{ $initials }}
                                        </div>
                                    @endif
                                    <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-gray-950 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">ID #{{ $user->id }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Role Badge --}}
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $user->role->badgeClass() }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>

                        {{-- Email --}}
                        <td class="px-4 py-2.5 text-xs text-gray-700">
                            {{ $user->email }}
                        </td>

                        {{-- Phone --}}
                        <td class="px-4 py-2.5 text-xs text-gray-600">
                            {{ $user->phone ?? '—' }}
                        </td>

                        {{-- Joined --}}
                        <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">
                            {{ $user->created_at->format('M j, Y') }}
                        </td>

                        {{-- Action: Direct Message Button --}}
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.chat.start-user', $user) }}" class="inline-block">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-brand-600 hover:bg-brand-50 border border-transparent hover:border-brand-100 rounded-lg transition-colors cursor-pointer"
                                    title="Message {{ $user->name }} ({{ $user->role->label() }})"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            No users found matching the filter.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                <x-pagination :paginator="$users" />
            </div>

        {{-- Tab 2: Active Conversations Log --}}
        @else
            <x-table :headers="['Participant 1', 'Participant 2', 'Product', 'Last Activity', 'Action']" id="admin-chat-table">
                @forelse ($conversations as $conversation)
                    @php
                        $buyerAvatar = \App\Support\MediaUrl::resolve($conversation->buyer->profile?->avatar);
                        $sellerAvatar = \App\Support\MediaUrl::resolve($conversation->seller->profile?->avatar);
                    @endphp
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        {{-- Buyer / Initiator --}}
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                @if ($buyerAvatar)
                                    <img src="{{ $buyerAvatar }}" alt="{{ $conversation->buyer->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-100 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-bold flex items-center justify-center text-[10px] shrink-0">
                                        {{ strtoupper(substr($conversation->buyer->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-950">{{ $conversation->buyer->name }}</p>
                                    <span class="text-[10px] text-gray-400">{{ $conversation->buyer->role->label() }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Seller / Receiver --}}
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                @if ($sellerAvatar)
                                    <img src="{{ $sellerAvatar }}" alt="{{ $conversation->seller->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-100 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 font-bold flex items-center justify-center text-[10px] shrink-0">
                                        {{ strtoupper(substr($conversation->seller->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-xs sm:text-sm font-semibold text-gray-950">{{ $conversation->seller->name }}</p>
                                    <span class="text-[10px] text-gray-400">{{ $conversation->seller->role->label() }}</span>
                                </div>
                            </div>
                        </td>

                        {{-- Product Context --}}
                        <td class="px-4 py-2.5 text-xs text-gray-600 max-w-xs truncate">
                            @if ($conversation->product)
                                <span class="font-medium text-gray-800">{{ $conversation->product->title }}</span>
                            @else
                                <span class="text-gray-400">Direct Chat (No Product)</span>
                            @endif
                        </td>

                        {{-- Last Message Timestamp --}}
                        <td class="px-4 py-2.5 text-xs text-gray-500 whitespace-nowrap">
                            <p class="font-medium text-gray-800">{{ $conversation->last_message_at?->diffForHumans() ?? '—' }}</p>
                            @if ($conversation->latestMessage)
                                <p class="text-[11px] text-gray-400 truncate max-w-[180px]">{{ $conversation->latestMessage->body }}</p>
                            @endif
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <a
                                href="{{ route('admin.chat.show', $conversation) }}"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-900 hover:text-white text-gray-700 rounded-lg text-xs font-bold transition-colors"
                            >
                                <span>Open Chat</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 mx-auto mb-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            No active conversations found.
                        </td>
                    </tr>
                @endforelse
            </x-table>

            <div class="mt-4">
                <x-pagination :paginator="$conversations" />
            </div>
        @endif
    </x-card>
@endsection
