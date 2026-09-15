@extends('layouts.admin')

@section('title', 'KYC Verifications')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <form method="GET" class="flex flex-col sm:flex-row gap-3 mb-5">
            <select name="status" onchange="this.form.submit()" class="border border-gray-200 rounded-md px-4 py-2.5 text-sm bg-white">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    @continue($status->value === 'draft')
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </form>

        <x-table :headers="['Applicant', 'Role', 'Status', 'Submitted', '']" id="verifications-table">
            @forelse ($applications as $application)
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900">{{ $application->user->name }}</p>
                        <p class="text-xs text-gray-400">{{ $application->user->email }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $application->user->role->label() }}</td>
                    <td class="px-4 py-3"><x-badge :color="$application->status->badgeColor()">{{ $application->status->label() }}</x-badge></td>
                    <td class="px-4 py-3 text-gray-500">{{ $application->submitted_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.verifications.show', $application) }}" class="text-brand-600 font-medium hover:underline">Review</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No submissions yet.</td>
                </tr>
            @endforelse
        </x-table>

        <x-pagination :paginator="$applications" />
    </x-card>
@endsection
