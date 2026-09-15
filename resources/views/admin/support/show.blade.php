@extends('layouts.admin')

@section('title', $ticket->ticket_number)

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card :title="$ticket->subject">
                <x-slot:action>
                    <x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge>
                </x-slot:action>

                <div class="space-y-4">
                    @foreach ($ticket->messages as $message)
                        <div class="{{ $message->sender_id === $ticket->user_id ? '' : 'ml-auto text-right' }} max-w-lg">
                            <div class="inline-block {{ $message->sender_id === $ticket->user_id ? 'bg-gray-100 text-gray-800' : 'bg-brand-50 text-gray-800' }} rounded-md px-4 py-2 text-sm text-left">
                                <p>{{ $message->body }}</p>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">{{ $message->sender->name }} · {{ $message->created_at->format('M j, g:ia') }}</p>
                        </div>
                    @endforeach
                </div>

                <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="mt-6 pt-4 border-t border-gray-100 space-y-3">
                    @csrf
                    <x-textarea label="Reply" name="body" rows="3" required />
                    <x-button type="submit">Send Reply</x-button>
                </form>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Customer">
                <p class="text-sm text-gray-700">{{ $ticket->user->name }}</p>
                <p class="text-xs text-gray-400">{{ $ticket->user->email }}</p>
            </x-card>

            <x-card title="Status">
                <form method="POST" action="{{ route('admin.support.status', $ticket) }}" class="space-y-3">
                    @csrf
                    <select name="status" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($ticket->status === $status)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </form>
            </x-card>

            <x-card title="Assignee">
                <form method="POST" action="{{ route('admin.support.assign', $ticket) }}" class="space-y-3">
                    @csrf
                    <select name="assigned_to" onchange="this.form.submit()" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected($ticket->assigned_to === $member->id)>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </form>
            </x-card>
        </div>
    </div>
@endsection
