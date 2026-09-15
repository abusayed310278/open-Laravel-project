@extends($layout)

@section('title', 'Support')

@section($section)
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Support</h1>
            <button type="button" onclick="document.getElementById('new-ticket').showModal()" class="text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 rounded-md px-4 py-2">New Ticket</button>
        </div>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        <x-card>
            @forelse ($tickets as $ticket)
                <a href="{{ route($routePrefix.'support.show', $ticket) }}" class="flex items-center justify-between py-4 border-b border-gray-50 last:border-0 hover:bg-gray-50 -mx-2 px-2 rounded-md">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->ticket_number }} · {{ ucfirst($ticket->category) }}</p>
                    </div>
                    <x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge>
                </a>
            @empty
                <p class="text-sm text-gray-400 text-center py-10">No support tickets yet.</p>
            @endforelse

            <x-pagination :paginator="$tickets" />
        </x-card>
    </div>

    <dialog id="new-ticket" class="rounded-md p-6 w-full max-w-md backdrop:bg-black/40">
        <form method="POST" action="{{ route($routePrefix.'support.store') }}" class="space-y-4">
            @csrf
            <x-input label="Subject" name="subject" type="text" required />
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                <select name="category" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                    @endforeach
                </select>
            </div>
            <x-textarea label="Message" name="body" rows="4" required />
            <div class="flex justify-end gap-2">
                <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                <x-button type="submit">Submit Ticket</x-button>
            </div>
        </form>
    </dialog>
@endsection
