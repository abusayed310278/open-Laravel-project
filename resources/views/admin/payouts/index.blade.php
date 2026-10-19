@extends('layouts.admin')

@section('title', 'Payouts')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" action="{{ route('admin.payouts.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5 max-w-xl">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search payout #, seller, amount, notes..."
                    autocomplete="off"
                    class="w-full text-xs pl-8 pr-8 py-2 border border-gray-200 rounded-lg focus:outline-hidden focus:border-brand-500 bg-white"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if (request()->filled('search'))
                    <a href="{{ route('admin.payouts.index', ['status' => request('status')]) }}" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs search-clear-btn" title="Clear">
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
        <x-table :headers="['Payout', 'Seller', 'Amount', 'Method', 'Status', 'Date', '']" id="admin-payouts-table">
            @forelse ($payouts as $payout)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-mono text-gray-900">{{ $payout->payout_number }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $payout->user->name }}</td>
                    <td class="px-4 py-3 text-gray-900">${{ number_format($payout->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $payout->method->label() }}</td>
                    <td class="px-4 py-3"><x-badge :color="$payout->status->badgeColor()">{{ $payout->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $payout->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        @if ($payout->status->value === 'requested')
                            <form method="POST" action="{{ route('admin.payouts.approve', $payout) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payouts.reject', $payout) }}" class="inline" onsubmit="return confirm('Reject this payout?')">
                                @csrf
                                <input type="hidden" name="notes" value="Rejected by admin">
                                <button type="submit" class="text-red-600 font-medium hover:underline">Reject</button>
                            </form>
                        @elseif ($payout->status->value === 'approved')
                            <form method="POST" action="{{ route('admin.payouts.processing', $payout) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-brand-600 font-medium hover:underline">Mark Processing</button>
                            </form>
                        @elseif ($payout->status->value === 'processing')
                            <form method="POST" action="{{ route('admin.payouts.complete', $payout) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Mark Completed</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No payout requests yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$payouts" />
        </div>
    </x-card>
@endsection
