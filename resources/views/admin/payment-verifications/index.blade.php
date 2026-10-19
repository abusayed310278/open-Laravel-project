@extends('layouts.admin')

@section('title', 'Payment Verifications')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" action="{{ route('admin.payment-verifications.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5 max-w-xl">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search order #, vendor, reference, amount..."
                    autocomplete="off"
                    class="w-full text-xs pl-8 pr-8 py-2 border border-gray-200 rounded-lg focus:outline-hidden focus:border-brand-500 bg-white"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if (request()->filled('search'))
                    <a href="{{ route('admin.payment-verifications.index', ['status' => request('status')]) }}" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs search-clear-btn" title="Clear">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-xs bg-white text-gray-700 hover:border-gray-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 cursor-pointer shrink-0">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <div id="search-results-wrapper">
        <x-table :headers="['Order', 'Vendor', 'Amount', 'Reference', 'Status', 'Submitted', '']" id="payment-verifications-table">
            @forelse ($submissions as $submission)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $submission->vendorOrder->vendor_order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $submission->order->order_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->vendorOrder->vendor->name }}</td>
                    <td class="px-4 py-3 text-gray-900">${{ number_format($submission->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->reference ?? '—' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$submission->status->badgeColor()">{{ $submission->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.payment-verifications.proof', $submission) }}" target="_blank" class="text-gray-500 font-medium hover:underline">Proof</a>
                        @if ($submission->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.payment-verifications.verify', $submission) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Verify</button>
                            </form>
                            <button type="button" onclick="document.getElementById('reject-{{ $submission->id }}').showModal()" class="text-red-600 font-medium hover:underline">Reject</button>

                            <dialog id="reject-{{ $submission->id }}" class="rounded-2xl p-6 w-full max-w-sm border-0 shadow-2xl backdrop:bg-gray-950/50 backdrop:backdrop-blur-xs text-left" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); margin: 0;">
                                <form method="POST" action="{{ route('admin.payment-verifications.reject', $submission) }}" class="space-y-4">
                                    @csrf
                                    <x-textarea label="Rejection notes" name="notes" rows="3" required />
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="this.closest('dialog').close()" class="px-4 py-2 text-sm text-gray-500">Cancel</button>
                                        <x-button type="submit" variant="danger">Reject</x-button>
                                    </div>
                                </form>
                            </dialog>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No manual payment submissions yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$submissions" />
        </div>
    </x-card>
@endsection
