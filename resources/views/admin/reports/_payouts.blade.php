    <div class="grid sm:grid-cols-2 gap-4 mt-6">
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Total Paid Out</p>
            <p class="text-xl font-bold text-gray-900">${{ number_format($d['totalPaid'], 2) }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-md p-5">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Pending Requests</p>
            <p class="text-xl font-bold text-gray-900">{{ number_format($d['pending']) }}</p>
        </div>
    </div>

    <x-card title="By Status" class="mt-6">
        <x-table :headers="['Status', 'Count', 'Total']" id="payout-status-table">
            @forelse ($d['byStatus'] as $row)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-700">{{ $row['status'] }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $row['count'] }}</td>
                    <td class="px-4 py-3 text-gray-900">${{ number_format($row['total'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-sm">No payouts in this range.</td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
