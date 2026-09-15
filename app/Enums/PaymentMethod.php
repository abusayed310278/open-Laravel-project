<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cod = 'cod';
    case ManualBank = 'manual_bank';
    case Stripe = 'stripe';
    case Paypal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::Cod => 'Cash on Delivery',
            self::ManualBank => 'Manual Bank Transfer',
            self::Stripe => 'Card (Stripe)',
            self::Paypal => 'PayPal',
        };
    }
}
