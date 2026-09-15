<?php

namespace App\Enums;

enum BillingCycle: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-time',
            self::Monthly => 'Monthly',
            self::Yearly => 'Yearly',
        };
    }
}
