@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
    <x-card>
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5 max-w-xl">
            <div class="relative flex-1">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search invoice #, order #, seller, buyer..."
                    autocomplete="off"
                    class="w-full text-xs pl-8 pr-8 py-2 border border-gray-200 rounded-lg focus:outline-hidden focus:border-brand-500 bg-white"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @if (request()->filled('search'))
                    <a href="{{ route('admin.invoices.index', ['status' => request('status')]) }}" class="absolute right-2.5 top-2 text-gray-400 hover:text-gray-600 text-xs search-clear-btn" title="Clear">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-2 text-xs bg-white text-gray-700 hover:border-gray-300 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 cursor-pointer shrink-0">
                <option value="">All statuses</option>
                @foreach (\App\Enums\InvoicePaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <div id="search-results-wrapper">
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
        </div>
    </x-card>
@endsection
