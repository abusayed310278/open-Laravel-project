@extends('layouts.admin')

@section('title', 'KYC Verifications')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-5">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </form>
            <div class="text-xs text-gray-500 font-medium">
                Showing {{ $applications->total() }} verification application{{ $applications->total() === 1 ? '' : 's' }}
            </div>
        </div>

        <x-table :headers="['Applicant', 'Role', 'Documents', 'Status', 'Submitted', '']" id="verifications-table">
            @forelse ($applications as $application)
                <tr class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
                    <td class="px-4 py-3.5">
                        <p class="font-semibold text-gray-900">{{ $application->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $application->user->email }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 font-medium text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                            {{ $application->user->role->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-600 text-sm">
                        @php $docCount = $application->documents->count(); @endphp
                        @if ($docCount > 0)
                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded bg-blue-50 text-blue-700">
                                {{ $docCount }} doc{{ $docCount === 1 ? '' : 's' }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">0 docs</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <x-badge :color="$application->status->badgeColor()">{{ $application->status->label() }}</x-badge>
                    </td>
                    <td class="px-4 py-3.5 text-sm text-gray-500">
                        {{ $application->submitted_at?->format('M j, Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <a href="{{ route('admin.verifications.show', $application) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 hover:underline">
                            Review
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <p class="font-medium">No verification applications found.</p>
                            @if (request('status'))
                                <a href="{{ route('admin.verifications.index') }}" class="text-xs text-brand-600 hover:underline mt-1">Clear filter</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$applications" />
    </x-card>
@endsection
