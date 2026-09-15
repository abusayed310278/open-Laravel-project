<?php

namespace App\Enums;

enum PaymentSubmissionStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Review',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'blue',
            self::Verified => 'green',
            self::Rejected => 'red',
        };
    }
}
