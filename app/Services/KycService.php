<?php

namespace App\Services;

use App\Enums\KycDocumentStatus;
use App\Enums\KycStatus;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\VerificationRequirement;
use App\Notifications\KycApproved;
use App\Notifications\KycRejected;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class KycService
{
    /**
     * Requirements for the user's role, active only, in display order.
     */
    public function requirementsFor(User $user): Collection
    {
        return VerificationRequirement::query()
            ->where('role', $user->role)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * The verification "application" the user is currently building or has
     * submitted — draft rows are reused so re-visiting the form doesn't
     * create duplicates.
     */
    public function currentApplication(User $user): UserVerification
    {
        return $user->verifications()
            ->whereIn('status', [KycStatus::Draft, KycStatus::Submitted, KycStatus::UnderReview, KycStatus::Rejected])
            ->latest()
            ->first()
            ?? $user->verifications()->create(['status' => KycStatus::Draft]);
    }

    /**
     * @param  array<string, UploadedFile>  $files  Keyed by document_type value.
     * @param  array<string, string|null>  $documentNumbers  Keyed by document_type value.
     */
    public function submit(User $user, array $files, array $documentNumbers = []): UserVerification
    {
        $application = $this->currentApplication($user);

        foreach ($files as $type => $file) {
            $path = $file->store("kyc/{$user->id}", 'local');

            $application->documents()->updateOrCreate(
                ['document_type' => $type],
                [
                    'document_number' => $documentNumbers[$type] ?? null,
                    'file_path' => $path,
                    'status' => KycDocumentStatus::Pending,
                ],
            );
        }

        $application->update([
            'status' => KycStatus::Submitted,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        ActivityLog::record('kyc.submitted', $application);

        return $application->fresh();
    }

    public function approve(UserVerification $application, User $admin): UserVerification
    {
        $application->update([
            'status' => KycStatus::Approved,
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'rejection_reason' => null,
        ]);

        $application->documents()->update([
            'status' => KycDocumentStatus::Approved,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        $application->user->notify(new KycApproved);

        ActivityLog::record('kyc.approved', $application);

        return $application->fresh();
    }

    public function reject(UserVerification $application, User $admin, string $reason): UserVerification
    {
        $application->update([
            'status' => KycStatus::Rejected,
            'verified_at' => now(),
            'verified_by' => $admin->id,
            'rejection_reason' => $reason,
        ]);

        $application->documents()->update([
            'status' => KycDocumentStatus::Rejected,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        $application->user->notify(new KycRejected($reason));

        ActivityLog::record('kyc.rejected', $application, ['reason' => $reason]);

        return $application->fresh();
    }
}
