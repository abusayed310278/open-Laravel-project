<?php

namespace App\Enums;

enum TransactionType: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Payout = 'payout';
    case Adjustment = 'adjustment';
}
