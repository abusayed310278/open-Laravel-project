@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Payment Verifications')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
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

        <x-table :headers="['Order', 'Amount', 'Reference', 'Status', 'Submitted', '']" id="seller-payment-verifications-table">
            @forelse ($submissions as $submission)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $submission->vendorOrder->vendor_order_number }}</p>
                        <p class="text-xs text-gray-400">{{ $submission->order->order_number }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-900">${{ number_format($submission->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->reference ?? '—' }}</td>
                    <td class="px-4 py-3"><x-badge :color="$submission->status->badgeColor()">{{ $submission->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route($routePrefix.'payment-verifications.proof', $submission) }}" target="_blank" class="text-gray-500 font-medium hover:underline">Proof</a>
                        @if ($submission->status->value === 'pending')
                            <form method="POST" action="{{ route($routePrefix.'payment-verifications.verify', $submission) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Verify</button>
                            </form>
                            <button type="button" onclick="document.getElementById('reject-{{ $submission->id }}').showModal()" class="text-red-600 font-medium hover:underline">Reject</button>

                            <dialog id="reject-{{ $submission->id }}" class="rounded-md p-6 w-full max-w-sm backdrop:bg-black/40">
                                <form method="POST" action="{{ route($routePrefix.'payment-verifications.reject', $submission) }}" class="space-y-4">
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
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No manual payment submissions yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$submissions" />
    </x-card>
@endsection
