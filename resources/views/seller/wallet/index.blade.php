@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Payouts')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession
    @error('amount')
        <x-alert type="error" class="mb-5">{{ $message }}</x-alert>
    @enderror

    <div class="grid sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Available</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($wallet->available_balance, 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pending</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($wallet->pending_balance, 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Earned</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($wallet->total_earned, 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Withdrawn</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($wallet->total_withdrawn, 2) }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card title="Recent Activity">
                <x-table :headers="['Date', 'Type', 'Source', 'Amount', 'Notes']" id="wallet-transactions-table">
                    @forelse ($transactions as $transaction)
                        <tr class="border-b border-gray-50">
                            <td class="px-4 py-3 text-gray-500">{{ $transaction->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3"><x-badge :color="$transaction->type->value === 'credit' ? 'green' : 'red'">{{ $transaction->type->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-500">{{ $transaction->source->label() }}</td>
                            <td class="px-4 py-3 text-gray-900">${{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $transaction->notes ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No activity yet.</td>
                        </tr>
                    @endforelse
                </x-table>
            </x-card>

            <x-card title="Payout History">
                <x-table :headers="['Payout', 'Amount', 'Method', 'Status', 'Date']" id="payout-history-table">
                    @forelse ($payouts as $payout)
                        <tr class="border-b border-gray-50">
                            <td class="px-4 py-3 font-mono text-gray-900">{{ $payout->payout_number }}</td>
                            <td class="px-4 py-3 text-gray-900">${{ number_format($payout->amount, 2) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $payout->method->label() }}</td>
                            <td class="px-4 py-3"><x-badge :color="$payout->status->badgeColor()">{{ $payout->status->label() }}</x-badge></td>
                            <td class="px-4 py-3 text-gray-500">{{ $payout->created_at->format('M j, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No payouts yet.</td>
                        </tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <div>
            <x-card title="Request Payout">
                <form method="POST" action="{{ route(auth()->user()->isBusiness() ? 'business.payouts.request' : 'saler.payouts.request') }}" class="space-y-4">
                    @csrf
                    <x-input label="Amount" name="amount" type="number" step="0.01" min="0.01" :max="$wallet->available_balance" required />
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Method</label>
                        <select name="method" class="w-full border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                            @foreach ($methods as $method)
                                <option value="{{ $method->value }}">{{ $method->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-button type="submit" class="w-full justify-center" :disabled="$wallet->available_balance <= 0">Request Payout</x-button>
                </form>
            </x-card>
        </div>
    </div>
@endsection
