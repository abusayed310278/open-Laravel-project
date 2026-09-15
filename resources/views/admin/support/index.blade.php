@extends('layouts.admin')

@section('title', 'Support')

@section('content')
    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Ticket', 'Subject', 'Customer', 'Assigned to', 'Status', '']" id="admin-support-table">
            @forelse ($tickets as $ticket)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $ticket->ticket_number }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $ticket->subject }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $ticket->user->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $ticket->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.support.show', $ticket) }}" class="text-brand-600 font-medium hover:underline text-sm">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No support tickets yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$tickets" />
    </x-card>
@endsection
