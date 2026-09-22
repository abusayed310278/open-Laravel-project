<?php

namespace App\Enums;

enum ProductCondition: string
{
    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';
    case Openbox = 'openbox';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Used => 'Used',
            self::Refurbished => 'Refurbished',
            self::Openbox => 'Openbox',
        };
    }
}
