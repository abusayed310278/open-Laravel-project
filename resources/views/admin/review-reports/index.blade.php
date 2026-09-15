@extends('layouts.admin')

@section('title', 'Review Reports')

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

        <x-table :headers="['Review', 'Reported by', 'Reason', 'Status', 'Date', '']" id="admin-review-reports-table">
            @forelse ($reports as $report)
                <tr class="border-b border-gray-50">
                    <td class="px-4 py-3 text-gray-600 max-w-xs truncate" title="{{ $report->review->body }}">{{ $report->review->body }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $report->reporter->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $report->reason }}</td>
                    <td class="px-4 py-3"><x-badge :color="$report->status->badgeColor()">{{ $report->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $report->created_at->format('M j, Y') }}</td>
                    <td class="px-4 py-3 text-right space-x-3">
                        @if ($report->status->value === 'pending')
                            <form method="POST" action="{{ route('admin.review-reports.resolve', $report) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 font-medium hover:underline">Resolve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.review-reports.dismiss', $report) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-500 font-medium hover:underline">Dismiss</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">No reports yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$reports" />
    </x-card>
@endsection
