@extends('layouts.admin')

@section('title', 'Payouts')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

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
    </x-card>
@endsection
