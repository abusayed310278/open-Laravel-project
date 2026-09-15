<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending, self::Processing => 'blue',
            self::Completed => 'green',
            self::Failed => 'red',
            self::Refunded => 'amber',
        };
    }
}
