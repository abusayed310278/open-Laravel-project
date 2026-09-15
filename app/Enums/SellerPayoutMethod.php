<?php

namespace App\Enums;

enum SellerPayoutMethod: string
{
    case ManualBank = 'manual_bank';
    case Stripe = 'stripe';
    case Paypal = 'paypal';

    public function label(): string
    {
        return match ($this) {
            self::ManualBank => 'Bank Transfer',
            self::Stripe => 'Stripe',
            self::Paypal => 'PayPal',
        };
    }
}
