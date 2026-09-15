@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach (\App\Enums\InvoicePaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Invoice', 'Order', 'Seller', 'Buyer', 'Date', 'Status', 'Total', '']" id="admin-invoices-table">
            @forelse ($invoices as $invoice)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900 font-mono">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono">{{ $invoice->vendorOrder->vendor_order_number }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->seller->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->buyer->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->issued_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3"><x-badge :color="$invoice->payment_status->badgeColor()">{{ $invoice->payment_status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-800">${{ number_format($invoice->total, 2) }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-brand-600 font-medium hover:underline text-sm">View</a>
                        <a href="{{ route('admin.invoices.download', $invoice) }}" class="text-gray-500 font-medium hover:underline text-sm">Download</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">No invoices yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$invoices" />
    </x-card>
@endsection
