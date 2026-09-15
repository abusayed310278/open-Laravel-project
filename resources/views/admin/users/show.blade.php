@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <x-breadcrumb :items="['Users' => route('admin.users.index'), $user->name => null]" />

    <div class="grid md:grid-cols-3 gap-5">
        <x-card class="md:col-span-2">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-gray-800 rounded-md flex items-center justify-center text-white text-xl font-semibold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
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
                            @disabled($status === $user->status)
                        >
                            {{ $status->label() }}
                        </x-button>
                    </form>
                @endforeach
            </div>
        </x-card>
    </div>
@endsection
