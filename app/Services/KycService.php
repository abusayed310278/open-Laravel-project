<?php

namespace App\Services;

use App\Enums\KycDocumentStatus;
use App\Enums\KycDocumentType;
use App\Enums\KycStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\VerificationRequirement;
use App\Notifications\KycApproved;
use App\Notifications\KycRejected;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KycService
{
    /**
     * Requirements for the user's role, active only, in display order.
     */
    public function requirementsFor(User $user): Collection
    {
        $requirements = VerificationRequirement::query()
            ->where('role', $user->role)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($requirements->isEmpty()) {
            $this->ensureDefaultRequirementsExistFor($user->role);

            $requirements = VerificationRequirement::query()
                ->where('role', $user->role)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        return $requirements;
    }

    public function ensureDefaultRequirementsExistFor(UserRole $role): void
    {
        $defaults = match ($role) {
            UserRole::Business => [
                ['document_type' => KycDocumentType::TradeLicense, 'is_required' => true, 'sort_order' => 1],
                ['document_type' => KycDocumentType::BusinessRegistration, 'is_required' => true, 'sort_order' => 2],
                ['document_type' => KycDocumentType::Tax, 'is_required' => false, 'sort_order' => 3],
                ['document_type' => KycDocumentType::Vat, 'is_required' => false, 'sort_order' => 4],
            ],
            UserRole::Saler => [
                ['document_type' => KycDocumentType::Nid, 'is_required' => true, 'sort_order' => 1],
                ['document_type' => KycDocumentType::AddressProof, 'is_required' => true, 'sort_order' => 2],
                ['document_type' => KycDocumentType::Passport, 'is_required' => false, 'sort_order' => 3],
            ],
            default => [
                ['document_type' => KycDocumentType::Nid, 'is_required' => true, 'sort_order' => 1],
                ['document_type' => KycDocumentType::AddressProof, 'is_required' => false, 'sort_order' => 2],
            ],
        };

        foreach ($defaults as $req) {
            VerificationRequirement::firstOrCreate(
                [
                    'role' => $role,
                    'document_type' => $req['document_type'],
                ],
                [
                    'is_required' => $req['is_required'],
                    'is_active' => true,
                    'sort_order' => $req['sort_order'],
                ]
            );
        }
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
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $realPath = $file->getRealPath();
            if (! $realPath || ! file_exists($realPath)) {
                continue;
            }

            $ext = $file->getClientOriginalExtension() ?: 'pdf';
            $filename = Str::random(40).'.'.$ext;
            $path = "kyc/{$user->id}/{$filename}";

            Storage::disk('local')->put($path, file_get_contents($realPath));

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
