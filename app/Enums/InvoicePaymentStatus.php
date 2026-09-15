<?php

namespace App\Enums;

enum InvoicePaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Partial = 'partial';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Partial => 'Partial',
            self::Refunded => 'Refunded',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Unpaid => 'gray',
            self::Paid => 'green',
            self::Partial => 'blue',
            self::Refunded => 'amber',
        };
    }
}
