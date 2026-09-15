@extends('layouts.admin')

@section('title', $application->user->name.' — Verification')

@section('content')
    <x-breadcrumb :items="['Verifications' => route('admin.verifications.index'), $application->user->name => null]" />

    <div class="grid md:grid-cols-3 gap-5">
        <x-card class="md:col-span-2" title="Submitted Documents">
            @if ($application->documents->isEmpty())
                <p class="text-sm text-gray-400">No documents uploaded.</p>
            @else
                <div class="space-y-3">
                    @foreach ($application->documents as $document)
                        <div class="flex items-center justify-between border border-gray-100 rounded-md p-4">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $document->document_type->label() }}</p>
                                @if ($document->document_number)
                                    <p class="text-xs text-gray-400">No. {{ $document->document_number }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3">
                                <x-badge :color="$document->status->value === 'approved' ? 'green' : ($document->status->value === 'rejected' ? 'red' : 'gray')">
                                    {{ $document->status->label() }}
                                </x-badge>
                                <a href="{{ route('admin.verification-documents.show', $document) }}" target="_blank" class="text-sm text-brand-600 font-medium hover:underline">View</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <div class="space-y-5">
            <x-card title="Applicant">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Name</dt>
                        <dd class="text-gray-800 font-medium mt-0.5">{{ $application->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Email</dt>
                        <dd class="text-gray-800 font-medium mt-0.5">{{ $application->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Role</dt>
                        <dd class="text-gray-800 font-medium mt-0.5">{{ $application->user->role->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Status</dt>
                        <dd class="mt-0.5"><x-badge :color="$application->status->badgeColor()">{{ $application->status->label() }}</x-badge></dd>
                    </div>
                    @if ($application->verifiedBy)
                        <div>
                            <dt class="text-gray-400 text-xs">Reviewed by</dt>
                            <dd class="text-gray-800 font-medium mt-0.5">{{ $application->verifiedBy->name }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            @if (in_array($application->status->value, ['submitted', 'under_review'], true))
                <x-card title="Decision">
                    <form method="POST" action="{{ route('admin.verifications.approve', $application) }}" class="mb-3">
                        @csrf
                        <x-button type="submit" class="w-full justify-center">Approve</x-button>
                    </form>

                    <form method="POST" action="{{ route('admin.verifications.reject', $application) }}" class="space-y-3">
                        @csrf
                        <x-textarea name="reason" placeholder="Reason for rejection..." rows="3" />
                        <x-button type="submit" variant="danger" class="w-full justify-center">Reject</x-button>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
@endsection
