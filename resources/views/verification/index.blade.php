@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Verification')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    @php
        $routePrefix = auth()->user()->isBusiness() ? 'business.' : 'saler.';
        $submittedDocs = $application->documents;
        $isPending = in_array($application->status->value, ['submitted', 'under_review'], true);
    @endphp

    <div class="space-y-6">
        {{-- Status Header Card --}}
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-950">Identity &amp; Document Verification</h1>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Track your submitted verification documents and view status updates.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400 font-medium">Overall Status:</span>
                    <x-badge :color="$application->status->badgeColor()">{{ $application->status->label() }}</x-badge>
                </div>
            </div>

            @if ($application->status->value === 'rejected' && $application->rejection_reason)
                <x-alert type="error" class="mb-2">
                    <strong>Verification Not Approved:</strong> {{ $application->rejection_reason }}
                </x-alert>
            @elseif ($isPending)
                <x-alert type="info" class="mb-2">
                    Your documents have been submitted and are currently being reviewed by our verification team. You can inspect your uploaded files and check document status below.
                </x-alert>
            @elseif ($application->status->value === 'approved')
                <x-alert type="success" class="mb-2">
                    <strong>You are Verified!</strong> Your identity and account documents have been approved by Openbox.
                </x-alert>
            @endif
        </x-card>

        {{-- Submitted Documents Status Section --}}
        @if ($submittedDocs->isNotEmpty())
            <x-card>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div>
                        <h2 class="text-base font-bold text-gray-950">Submitted Documents</h2>
                        <p class="text-xs text-gray-500 mt-0.5">List of documents you have uploaded for verification.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-bold">
                        {{ $submittedDocs->count() }} {{ Str::plural('Document', $submittedDocs->count()) }} Uploaded
                    </span>
                </div>

                <div class="grid gap-4">
                    @foreach ($submittedDocs as $doc)
                        @php
                            $req = $requirements->firstWhere('document_type', $doc->document_type);
                        @endphp
                        <div class="border border-gray-200/80 rounded-xl p-4 bg-gray-50/50 hover:bg-white hover:border-amber-300/80 transition-all shadow-2xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100/80 text-amber-700 flex items-center justify-center shrink-0">
                                        @if ($doc->isImage())
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-950 flex items-center gap-2">
                                            <span>{{ $doc->document_type->label() }}</span>
                                            @if ($req?->is_required)
                                                <span class="text-[10px] bg-red-50 text-red-600 border border-red-200 px-1.5 py-0.5 rounded-md font-semibold">Required</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5 font-mono truncate max-w-xs">
                                            {{ $doc->fileName() }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <x-badge :color="$doc->status->badgeColor()">{{ $doc->status->label() }}</x-badge>
                                    @if (Route::has($routePrefix.'verification.document'))
                                        <a
                                            href="{{ route($routePrefix.'verification.document', $doc) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 hover:border-amber-400 hover:bg-amber-50/60 text-gray-700 hover:text-amber-800 rounded-xl text-xs font-bold transition-all shadow-2xs"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View File</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3 grid sm:grid-cols-3 gap-3 text-xs text-gray-500">
                                <div>
                                    <span class="font-semibold text-gray-700">Document Number:</span>
                                    <span class="font-mono text-gray-800 ml-1">{{ $doc->document_number ?: 'Not specified' }}</span>
                                </div>
                                <div>
                                    <span class="font-semibold text-gray-700">Uploaded:</span>
                                    <span class="text-gray-800 ml-1">{{ $doc->created_at?->format('M j, Y g:i A') ?? 'Submitted' }}</span>
                                </div>
                                @if ($doc->remarks)
                                    <div class="sm:col-span-3 pt-2 border-t border-gray-100 text-amber-800 bg-amber-50/80 p-2.5 rounded-lg">
                                        <strong class="font-bold">Admin Remarks:</strong> {{ $doc->remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        {{-- Upload / Update Documents Form --}}
        <x-card>
            @if ($isPending && $submittedDocs->isNotEmpty())
                <details class="group">
                    <summary class="flex items-center justify-between cursor-pointer list-none select-none py-1">
                        <div>
                            <h2 class="text-base font-bold text-gray-950 inline-flex items-center gap-2">
                                <span>Re-upload or Replace Documents</span>
                                <span class="text-xs font-normal text-gray-400">(Optional)</span>
                            </h2>
                            <p class="text-xs text-gray-500 mt-0.5">Click here if you need to submit updated document files or document numbers.</p>
                        </div>
                        <span class="p-1.5 rounded-lg bg-gray-100 text-gray-400 group-open:rotate-180 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </summary>

                    <div class="mt-5 pt-5 border-t border-gray-100">
            @else
                <div class="mb-5">
                    <h2 class="text-base font-bold text-gray-950">Upload Verification Documents</h2>
                    <p class="text-xs sm:text-sm text-gray-500 mt-0.5">Select and upload the required documents below for account verification.</p>
                </div>
            @endif

            @if ($requirements->isEmpty())
                <p class="text-sm text-gray-400">No document requirements have been configured yet — check back soon.</p>
            @else
                <form method="POST" action="{{ route($routePrefix.'verification.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    @foreach ($requirements as $requirement)
                        @php
                            $existing = $application->documents->firstWhere('document_type', $requirement->document_type);
                        @endphp
                        <div class="border border-gray-200/80 rounded-xl p-4 bg-white shadow-2xs space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ $requirement->document_type->label() }}
                                    @if ($requirement->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </p>
                                @if ($existing)
                                    <span class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md text-[11px] font-semibold">
                                        Current: {{ $existing->status->label() }}
                                    </span>
                                @endif
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <x-file-upload :name="'documents['.$requirement->document_type->value.']'" hint="PDF, JPG or PNG up to 5MB" />
                                <x-input
                                    label="Document number (optional)"
                                    :name="'document_numbers['.$requirement->document_type->value.']'"
                                    type="text"
                                    :value="old('document_numbers.'.$requirement->document_type->value, $existing?->document_number)"
                                />
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-2">
                        <x-button type="submit">Submit for Review</x-button>
                    </div>
                </form>
            @endif

            @if ($isPending && $submittedDocs->isNotEmpty())
                    </div>
                </details>
            @endif
        </x-card>
    </div>
@endsection
