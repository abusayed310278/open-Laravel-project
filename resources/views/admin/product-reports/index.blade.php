@extends('layouts.admin')

@section('title', 'Product Reports')

@section('content')
    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession
    @session('error')
        <x-alert type="danger" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <div class="flex items-center justify-between mb-5 flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Product Reports</h1>
                <p class="text-xs text-gray-500 mt-1">Review flagged products reported by buyers and sellers for policy violations.</p>
            </div>
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <x-table :headers="['Product', 'Seller', 'Reported by', 'Reason', 'Details', 'Status', 'Date', '']" id="admin-product-reports-table">
            @forelse ($reports as $report)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3">
                        @if ($report->product)
                            <a href="{{ route('products.show', $report->product->slug) }}" target="_blank" class="font-medium text-brand-600 hover:underline max-w-xs block truncate" title="{{ $report->product->title }}">
                                {{ $report->product->title }}
                            </a>
                            <span class="text-xs text-gray-400">ID: {{ $report->product->id }}</span>
                        @else
                            <span class="text-gray-400 italic">Product deleted</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $report->product?->user?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $report->reporter?->name ?? '—' }}
                        <div class="text-xs text-gray-400">{{ $report->reporter?->email }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-800">
                        {{ $report->reason }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ $report->details ?? '—' }}">
                        {{ $report->details ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="$report->status->badgeColor()">{{ $report->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $report->created_at->format('M j, Y H:i') }}
                    </td>
                    <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap text-sm">
                        @if ($report->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.product-reports.resolve', $report) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-semibold hover:underline">Resolve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.product-reports.dismiss', $report) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-500 font-medium hover:underline">Dismiss</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 text-sm">No product reports yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$reports" />
    </x-card>
@endsection
