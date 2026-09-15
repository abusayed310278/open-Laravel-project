<?php

namespace App\Enums;

/**
 * Physical product-inspection status (Phase 10 — Product Verification).
 * Shared by Product::verification_status and ProductVerification::status so
 * the two stay in lockstep. Not to be confused with KycStatus, which tracks
 * seller identity/business document review.
 */
enum VerificationStatus: string
{
    case NotRequested = 'not_requested';
    case Pending = 'pending';
    case Scheduled = 'scheduled';
    case Inspecting = 'inspecting';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::NotRequested => 'Not Requested',
            self::Pending => 'Pending',
            self::Scheduled => 'Scheduled',
            self::Inspecting => 'Inspecting',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::NotRequested, self::Cancelled => 'gray',
            self::Pending, self::Scheduled, self::Inspecting => 'blue',
            self::Verified => 'green',
            self::Rejected => 'red',
        };
    }
}
