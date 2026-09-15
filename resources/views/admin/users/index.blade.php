@extends('layouts.admin')

@section('title', 'Users')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <input
                type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                class="flex-1 border border-gray-200 rounded-md px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-400"
            >
            <select name="role" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->value }}" @selected(request('role') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <x-button type="submit" variant="secondary">Filter</x-button>
        </form>

        <x-table :headers="['User', 'Role', 'Status', 'Joined', '']" id="users-table">
            @forelse ($users as $user)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.users.show', $user) }}" class="font-medium text-gray-900 hover:text-brand-600">{{ $user->name }}</a>
                        <p class="text-xs text-gray-400">{{ $user->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                            @csrf @method('PATCH')
                            <select name="role" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-2.5 py-1.5 text-xs bg-white">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$user->status->badgeColor()">{{ $user->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $user->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 justify-end">
                            @foreach (\App\Enums\UserStatus::cases() as $status)
                                @continue($status === $user->status)
                                <form method="POST" action="{{ route('admin.users.status', $user) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $status->value }}">
                                    <button type="submit" class="text-xs font-medium text-gray-500 hover:text-brand-600 border border-gray-200 hover:bg-gray-50 rounded-md px-2.5 py-1.5">
                                        {{ $status->label() }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No users found.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$users" />
    </x-card>
@endsection
