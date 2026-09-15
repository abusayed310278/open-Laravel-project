<?php

namespace App\Enums;

enum CommissionRecordStatus: string
{
    case Pending = 'pending';
    case Available = 'available';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Available => 'Available',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Available => 'green',
            self::Refunded => 'red',
        };
    }
}
