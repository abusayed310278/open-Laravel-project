<?php

namespace App\Enums;

enum KycStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Expired => 'Expired',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted, self::UnderReview => 'blue',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Expired => 'amber',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-gray-100 text-gray-700 border border-gray-200',
            self::Submitted, self::UnderReview => 'bg-blue-50 text-blue-700 border border-blue-200/60',
            self::Approved => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
            self::Rejected => 'bg-rose-50 text-rose-700 border border-rose-200/60',
            self::Expired => 'bg-amber-50 text-amber-700 border border-amber-200/60',
        };
    }
}
