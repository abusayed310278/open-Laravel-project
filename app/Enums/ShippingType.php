<?php

namespace App\Enums;

enum ShippingType: string
{
    case Free = 'free';
    case FlatRate = 'flat_rate';
    case Calculated = 'calculated';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free Shipping',
            self::FlatRate => 'Flat Rate',
            self::Calculated => 'Calculated at Checkout',
        };
    }
}
