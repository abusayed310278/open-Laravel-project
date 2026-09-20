@extends($layout)

@section('title', 'Support')

@section($section)
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-black text-gray-950 tracking-tight">Support Tickets</h1>
                <p class="text-xs text-gray-500 mt-0.5">Submit inquiries, report issues, or track customer service requests.</p>
            </div>
            <button
                type="button"
                onclick="document.getElementById('new-ticket').showModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-xs font-bold rounded-xl shadow-xs hover:shadow-sm transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>New Ticket</span>
            </button>
        </div>

        @session('status')
            <x-alert type="success">{{ $value }}</x-alert>
        @endsession

        <x-card class="shadow-2xs">
            @forelse ($tickets as $ticket)
                <a href="{{ route($routePrefix.'support.show', $ticket) }}" class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0 hover:bg-gray-50/80 -mx-5 px-5 transition">
                    <div>
                        <p class="text-sm font-bold text-gray-950">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $ticket->ticket_number }} · {{ ucfirst($ticket->category) }} · {{ $ticket->created_at->diffForHumans() }}</p>
                    </div>
                    <x-badge :color="$ticket->status->badgeColor()">{{ $ticket->status->label() }}</x-badge>
                </a>
            @empty
                <div class="py-12 text-center text-gray-400">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400 mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-500">No support tickets yet.</p>
                    <p class="text-[11px] text-gray-400 mt-1">Click "New Ticket" above to open a ticket with support.</p>
                </div>
            @endforelse

            <x-pagination :paginator="$tickets" />
        </x-card>
    </div>

    {{-- New Support Ticket Modal --}}
    <dialog
        id="new-ticket"
        class="p-0 w-full max-w-lg bg-transparent border-0 backdrop:bg-gray-950/50 backdrop:backdrop-blur-xs rounded-2xl shadow-2xl text-left overflow-hidden"
        style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0;"
    >
        <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-extrabold text-gray-950">Create Support Ticket</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Submit an inquiry or report an issue to customer service.</p>
                </div>
                <button
                    type="button"
                    onclick="document.getElementById('new-ticket').close()"
                    class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route($routePrefix.'support.store') }}" class="space-y-4">
                @csrf
                <x-input label="Subject" name="subject" type="text" placeholder="Brief summary of your question or issue..." required />
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Category</label>
                    <select name="category" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-400">
                        @foreach ($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-textarea label="Message" name="body" rows="4" placeholder="Provide detailed details about your inquiry..." required />
                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        onclick="document.getElementById('new-ticket').close()"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl text-xs font-bold text-gray-700 transition cursor-pointer"
                    >
                        Cancel
                    </button>
                    <x-button type="submit">Submit Ticket</x-button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
