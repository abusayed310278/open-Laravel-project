<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\ChecklistResult;
use App\Enums\VerificationStatus;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Models\User;
use App\Models\VerificationChecklist;
use App\Notifications\VerificationRejected;
use App\Notifications\VerificationScheduled;
use App\Notifications\VerificationVerified;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductVerificationService
{
    /**
     * A seller books their own appointment slot — there's no separate admin
     * confirmation step in v1, so the appointment is confirmed immediately.
     */
    public function request(Product $product, User $seller, int $locationId, string $date, string $time): ProductVerification
    {
        return DB::transaction(function () use ($product, $seller, $locationId, $date, $time) {
            $verification = ProductVerification::create([
                'product_id' => $product->id,
                'seller_id' => $seller->id,
                'location_id' => $locationId,
                'status' => VerificationStatus::Scheduled,
                'requested_at' => now(),
                'scheduled_at' => "{$date} {$time}",
            ]);

            $verification->appointments()->create([
                'appointment_date' => $date,
                'appointment_time' => $time,
                'status' => AppointmentStatus::Confirmed,
            ]);

            $product->update(['verification_status' => VerificationStatus::Scheduled]);

            $seller->notify(new VerificationScheduled($verification));

            ActivityLog::record('product_verification.requested', $verification);

            return $verification;
        });
    }

    /**
     * @return Collection<int, VerificationChecklist>
     */
    public function checklistFor(Product $product): Collection
    {
        return VerificationChecklist::query()
            ->where(fn ($q) => $q->whereNull('category_id')->orWhere('category_id', $product->category_id))
            ->orderBy('sort_order')
            ->get();
    }

    public function startInspection(ProductVerification $verification, User $verifier): ProductVerification
    {
        $verification->update([
            'status' => VerificationStatus::Inspecting,
            'verifier_id' => $verifier->id,
        ]);

        $verification->product->update(['verification_status' => VerificationStatus::Inspecting]);

        return $verification;
    }

    /**
     * @param  array<int, string>  $results  Keyed by checklist_item_id => ChecklistResult value.
     */
    public function pass(ProductVerification $verification, User $verifier, array $results, string $grade, ?int $batteryHealth, ?string $notes): ProductVerification
    {
        return DB::transaction(function () use ($verification, $verifier, $results, $grade, $batteryHealth, $notes) {
            $this->saveResults($verification, $results);

            $verification->gradeAssignment()->create([
                'product_id' => $verification->product_id,
                'verifier_id' => $verifier->id,
                'grade' => $grade,
                'grade_notes' => $notes,
                'battery_health' => $batteryHealth,
                'assigned_at' => now(),
            ]);

            $verification->update([
                'status' => VerificationStatus::Verified,
                'verifier_id' => $verifier->id,
                'inspected_at' => now(),
                'verified_at' => now(),
            ]);

            $verification->product->update([
                'verification_status' => VerificationStatus::Verified,
                'grade' => $grade,
                'grade_notes' => $notes,
            ]);

            $verification->seller->notify(new VerificationVerified($verification));

            ActivityLog::record('product_verification.passed', $verification, ['grade' => $grade]);

            return $verification->fresh();
        });
    }

    /**
     * @param  array<int, string>  $results
     */
    public function fail(ProductVerification $verification, User $verifier, array $results, string $reason): ProductVerification
    {
        return DB::transaction(function () use ($verification, $verifier, $results, $reason) {
            $this->saveResults($verification, $results);

            $verification->update([
                'status' => VerificationStatus::Rejected,
                'verifier_id' => $verifier->id,
                'inspected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $verification->product->update(['verification_status' => VerificationStatus::Rejected]);

            $verification->seller->notify(new VerificationRejected($verification, $reason));

            ActivityLog::record('product_verification.failed', $verification, ['reason' => $reason]);

            return $verification->fresh();
        });
    }

    /**
     * @param  array<int, string>  $results
     */
    private function saveResults(ProductVerification $verification, array $results): void
    {
        foreach ($results as $checklistItemId => $result) {
            $verification->results()->updateOrCreate(
                ['checklist_item_id' => $checklistItemId],
                ['result' => ChecklistResult::from($result)],
            );
        }
    }
}
