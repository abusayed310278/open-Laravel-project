<?php

namespace App\Enums;

enum SellerTransactionSource: string
{
    case Sale = 'sale';
    case Refund = 'refund';
    case Payout = 'payout';
    case Adjustment = 'adjustment';
    case Commission = 'commission';

    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Sale',
            self::Refund => 'Refund',
            self::Payout => 'Payout',
            self::Adjustment => 'Adjustment',
            self::Commission => 'Commission',
        };
    }
}
