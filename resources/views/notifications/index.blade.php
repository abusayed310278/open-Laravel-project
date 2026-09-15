@extends($layout)

@section('title', 'Notifications')

@section($section)
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
            <div class="flex items-center gap-4">
                <a href="{{ route('notification-preferences.edit') }}" class="text-sm text-gray-500 font-medium hover:underline">Email preferences</a>
                @if (auth()->user()->unreadNotifications->isNotEmpty())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-sm text-brand-600 font-medium hover:underline">Mark all as read</button>
                    </form>
                @endif
            </div>
        </div>

        <x-card>
            @forelse ($notifications as $notification)
                <div class="flex items-start justify-between gap-4 py-4 border-b border-gray-50 last:border-0 {{ $notification->read_at ? '' : 'bg-brand-50/40 -mx-2 px-2 rounded-md' }}">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $notification->data['body'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @unless ($notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-brand-600 font-medium hover:underline whitespace-nowrap">Mark read</button>
                        </form>
                    @endunless
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-10">No notifications yet.</p>
            @endforelse

            <x-pagination :paginator="$notifications" />
        </x-card>
    </div>
@endsection
