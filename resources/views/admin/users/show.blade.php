@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <x-breadcrumb :items="['Users' => route('admin.users.index'), $user->name => null]" />

    <div class="grid md:grid-cols-3 gap-5">
        <x-card class="md:col-span-2">
            <div class="flex items-center gap-4 mb-6">
                @php
                    $userAvatarUrl = \App\Support\MediaUrl::resolve($user->profile?->avatar ?? $user->avatar);
                @endphp
                @if ($userAvatarUrl)
                    <img src="{{ $userAvatarUrl }}" alt="{{ $user->name }}" class="w-14 h-14 rounded-full object-cover object-top border-2 border-gray-100 shadow-xs shrink-0" style="object-position: top center !important;">
                @else
                    <div class="w-14 h-14 bg-gray-800 rounded-full flex items-center justify-center text-white text-xl font-semibold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>

            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-400 text-xs">Role</dt>
                    <dd class="text-gray-800 font-medium mt-0.5">{{ $user->role->label() }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs">Status</dt>
                    <dd class="mt-0.5"><x-badge :color="$user->status->badgeColor()">{{ $user->status->label() }}</x-badge></dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs">Phone</dt>
                    <dd class="text-gray-800 font-medium mt-0.5">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs">Email verified</dt>
                    <dd class="text-gray-800 font-medium mt-0.5">{{ $user->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs">Joined</dt>
                    <dd class="text-gray-800 font-medium mt-0.5">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400 text-xs">Last login</dt>
                    <dd class="text-gray-800 font-medium mt-0.5">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</dd>
                </div>
            </dl>

            <div class="mt-6 pt-6 border-t border-gray-100 grid sm:grid-cols-2 gap-4">
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Buyer Activity</span>
                        <span class="text-xs font-bold text-brand-600">{{ $user->orders()->count() }} orders</span>
                    </div>
                    <p class="text-xs text-gray-500">Total Spent: <strong class="text-gray-900">Tk {{ number_format((float) $user->orders()->whereNotIn('status', ['cancelled', 'failed'])->sum('total'), 2) }}</strong></p>
                </div>

                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Seller Activity</span>
                        <span class="text-xs font-bold text-brand-600">{{ $user->products()->count() }} listings</span>
                    </div>
                    <p class="text-xs text-gray-500">Store: <strong class="text-gray-900">{{ $user->salerProfile?->display_name ?? $user->businessProfile?->business_name ?? 'Personal Store' }}</strong></p>
                    <p class="text-xs text-gray-500 mt-0.5">Wallet: <strong class="text-emerald-600">Tk {{ number_format((float) (app(\App\Services\WalletService::class)->walletFor($user)->available_balance ?? 0), 2) }}</strong></p>
                </div>
            </div>
        </x-card>

        <x-card title="Change Status">
            <div class="space-y-2">
                @foreach (\App\Enums\UserStatus::cases() as $status)
                    <form method="POST" action="{{ route('admin.users.status', $user) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status->value }}">
                        <x-button
                            type="submit"
                            :variant="$status === $user->status ? 'primary' : 'secondary'"
                            class="w-full justify-center"
                            :disabled="$status === $user->status"
                        >
                            {{ $status->label() }}
                        </x-button>
                    </form>
                @endforeach
            </div>
        </x-card>
    </div>
@endsection
