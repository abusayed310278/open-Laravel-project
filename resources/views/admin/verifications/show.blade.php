@extends('layouts.admin')

@section('title', $application->user->name.' — KYC Verification')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <x-breadcrumb :items="['KYC Verifications' => route('admin.verifications.index'), $application->user->name => null]" />
            <h1 class="text-xl sm:text-2xl font-black text-gray-950 tracking-tight mt-1">{{ $application->user->name }} — KYC Application</h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.verifications.index') }}" class="px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-xs transition">
                ← Back to KYC Queue
            </a>
        </div>
    </div>

    @session('status')
        <x-alert type="success" class="mb-5">{{ $value }}</x-alert>
    @endsession

    <div class="grid md:grid-cols-3 gap-6">
        {{-- Left: Submitted Documents --}}
        <x-card class="md:col-span-2 shadow-2xs" title="Submitted KYC Documents">
            @if ($application->documents->isEmpty())
                <div class="py-10 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <p class="font-bold text-gray-900 text-sm">No verification documents uploaded yet.</p>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
                        The user has not submitted physical document files. However, as an Administrator, you can manually verify and approve this user below.
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($application->documents as $document)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between border border-gray-200/80 rounded-xl p-4 gap-3 bg-gray-50/40">
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $document->document_type->label() }}</p>
                                @if ($document->document_number)
                                    <p class="text-xs font-mono text-gray-500 mt-0.5">Doc No: {{ $document->document_number }}</p>
                                @endif
                                <p class="text-[11px] text-gray-400 mt-0.5">Uploaded {{ $document->created_at->format('M j, Y g:ia') }}</p>
                            </div>
                            <div class="flex items-center gap-3 self-end sm:self-auto">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold {{ $document->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : ($document->status->value === 'rejected' ? 'bg-rose-50 text-rose-700 border border-rose-200/60' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $document->status->label() }}
                                </span>
                                <a
                                    href="{{ route('admin.verification-documents.show', $document) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand-500 hover:bg-brand-600 text-white rounded-lg text-xs font-bold shadow-2xs transition"
                                >
                                    <span>View File</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Right: Applicant Info & Decision Panel --}}
        <div class="space-y-5">
            {{-- Applicant Details Card --}}
            <x-card title="Applicant Details" class="shadow-2xs">
                <dl class="space-y-3.5 text-xs">
                    <div>
                        <dt class="text-gray-400 font-medium">Name</dt>
                        <dd class="text-gray-950 font-bold text-sm mt-0.5">{{ $application->user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Email</dt>
                        <dd class="text-gray-800 font-mono text-xs mt-0.5">{{ $application->user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Account Role</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold {{ $application->user->role->badgeClass() }}">
                                {{ $application->user->role->label() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 font-medium">Verification Status</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $application->status->badgeClass() }}">
                                {{ $application->status->label() }}
                            </span>
                        </dd>
                    </div>
                    @if ($application->submitted_at)
                        <div>
                            <dt class="text-gray-400 font-medium">Submitted Date</dt>
                            <dd class="text-gray-700 font-medium mt-0.5">{{ $application->submitted_at->format('M j, Y g:ia') }}</dd>
                        </div>
                    @endif
                    @if ($application->verifiedBy)
                        <div>
                            <dt class="text-gray-400 font-medium">Reviewed By</dt>
                            <dd class="text-gray-900 font-bold mt-0.5">{{ $application->verifiedBy->name }}</dd>
                        </div>
                    @endif
                    @if ($application->rejection_reason)
                        <div class="p-3 bg-rose-50 border border-rose-200/80 rounded-xl text-rose-800">
                            <dt class="font-bold text-xs uppercase tracking-wider text-rose-700">Rejection Reason</dt>
                            <dd class="text-xs mt-1 leading-relaxed">{{ $application->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            </x-card>

            {{-- Decision Action Panel (Always Accessible for Admin) --}}
            <x-card title="Admin Verification Action" class="shadow-2xs border-brand-200/60">
                @if ($application->status->value === 'approved')
                    <div class="p-3.5 bg-emerald-50 border border-emerald-200/80 rounded-xl mb-4 text-emerald-800 text-xs">
                        <div class="flex items-center gap-2 font-bold">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>KYC Approved & Verified</span>
                        </div>
                        <p class="text-[11px] text-emerald-700 mt-1">This user is fully verified and active on Openbox.</p>
                    </div>

                    {{-- Reject/Revoke Option --}}
                    <form method="POST" action="{{ route('admin.verifications.reject', $application) }}" class="space-y-3 pt-2 border-t border-gray-100">
                        @csrf
                        <label class="block text-xs font-bold text-gray-700">Revoke / Reject Verification</label>
                        <x-textarea name="reason" placeholder="Reason for revoking verification..." rows="2" required />
                        <x-button type="submit" variant="danger" class="w-full justify-center text-xs font-bold py-2.5">
                            Revoke Approval & Reject
                        </x-button>
                    </form>
                @else
                    <div class="space-y-4">
                        {{-- Approve Form --}}
                        <form method="POST" action="{{ route('admin.verifications.approve', $application) }}">
                            @csrf
                            <button
                                type="submit"
                                onclick="return confirm('Approve KYC verification for {{ $application->user->name }}?')"
                                class="w-full px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-xs transition cursor-pointer flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Approve User KYC Verification</span>
                            </button>
                        </form>

                        {{-- Reject Form --}}
                        <form method="POST" action="{{ route('admin.verifications.reject', $application) }}" class="space-y-3 pt-3 border-t border-gray-100">
                            @csrf
                            <label class="block text-xs font-bold text-gray-700">Reject Application</label>
                            <x-textarea name="reason" placeholder="State rejection reason..." rows="2" required />
                            <x-button type="submit" variant="danger" class="w-full justify-center text-xs font-bold py-2.5">
                                Reject KYC Application
                            </x-button>
                        </form>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
