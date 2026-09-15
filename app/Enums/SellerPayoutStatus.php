<?php

namespace App\Enums;

enum SellerPayoutStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Processing = 'processing';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Requested => 'Requested',
            self::Approved => 'Approved',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Requested => 'blue',
            self::Approved, self::Processing => 'amber',
            self::Completed => 'green',
            self::Rejected => 'red',
        };
    }
}
