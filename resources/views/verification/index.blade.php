@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Verification')

@section('content')

    @session('status')
        <x-alert type="success">{{ $value }}</x-alert>
    @endsession

    <x-card>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-base font-semibold text-gray-800">Identity &amp; document verification</h2>
                <p class="text-sm text-gray-500 mt-1">Upload the documents below so Openbox can verify your account.</p>
            </div>
            <x-badge :color="$application->status->badgeColor()">{{ $application->status->label() }}</x-badge>
        </div>

        @if ($application->status->value === 'rejected' && $application->rejection_reason)
            <x-alert type="error" class="mb-5">
                <strong>Not approved:</strong> {{ $application->rejection_reason }}
            </x-alert>
        @elseif ($application->status->value === 'submitted' || $application->status->value === 'under_review')
            <x-alert type="info" class="mb-5">
                Your documents are being reviewed. We'll email you once a decision is made.
            </x-alert>
        @elseif ($application->status->value === 'approved')
            <x-alert type="success" class="mb-5">
                You're verified! You can update these documents any time if they change.
            </x-alert>
        @endif

        @if ($requirements->isEmpty())
            <p class="text-sm text-gray-400">No document requirements have been configured yet — check back soon.</p>
        @else
            <form method="POST" action="{{ route(auth()->user()->isBusiness() ? 'business.verification.store' : 'saler.verification.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                @foreach ($requirements as $requirement)
                    @php
                        $existing = $application->documents->firstWhere('document_type', $requirement->document_type);
                    @endphp
                    <div class="border border-gray-100 rounded-md p-4">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm font-medium text-gray-800">
                                {{ $requirement->document_type->label() }}
                                @if ($requirement->is_required)
                                    <span class="text-red-500">*</span>
                                @endif
                            </p>
                            @if ($existing)
                                <x-badge color="gray">Uploaded</x-badge>
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

                <x-button type="submit">Submit for Review</x-button>
            </form>
        @endif
    </x-card>
@endsection
