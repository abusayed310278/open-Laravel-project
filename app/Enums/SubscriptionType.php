<?php

namespace App\Enums;

enum SubscriptionType: string
{
    case Saler = 'saler';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Saler => 'Saler',
            self::Business => 'Business',
        };
    }
}
