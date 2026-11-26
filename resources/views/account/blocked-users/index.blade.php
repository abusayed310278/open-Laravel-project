@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Blocked Users</h1>
            <p class="text-xs text-gray-500 mt-1">Sellers and users you have blocked. Their products are hidden from your catalog, search results, and feed.</p>
        </div>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        <x-card>
            <div class="divide-y divide-gray-100">
                @forelse ($blockedUsers as $blocked)
                    @php
                        $name = $blocked->businessProfile?->business_name ?? $blocked->salerProfile?->display_name ?? $blocked->name;
                    @endphp
                    <div class="py-3.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-600 text-sm shrink-0 overflow-hidden">
                                @if ($blocked->avatar)
                                    <img src="{{ \App\Support\MediaUrl::resolve($blocked->avatar) }}" alt="{{ $name }}" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $name }}</p>
                                <p class="text-xs text-gray-400">Blocked {{ $blocked->pivot?->created_at?->diffForHumans() }}</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('users.unblock', $blocked) }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors cursor-pointer">
                                Unblock
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <p class="text-sm font-medium">You have not blocked any users.</p>
                    </div>
                @endforelse
            </div>
        </x-card>
    </div>
@endsection
