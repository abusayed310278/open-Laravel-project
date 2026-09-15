@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Invoices')

@section('content')
    <x-card>
        <x-table :headers="['Invoice', 'Order', 'Buyer', 'Date', 'Status', 'Total', '']" id="seller-invoices-table">
            @forelse ($invoices as $invoice)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">{{ $invoice->vendorOrder->vendor_order_number }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->buyer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->issued_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3"><x-badge :color="$invoice->payment_status->badgeColor()">{{ $invoice->payment_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route($routePrefix.'invoices.show', $invoice) }}" class="text-brand-600 font-medium hover:underline text-sm">View</a>
                        <a href="{{ route($routePrefix.'invoices.download', $invoice) }}" class="text-gray-500 font-medium hover:underline text-sm">Download</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No invoices yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$invoices" />
    </x-card>
@endsection
