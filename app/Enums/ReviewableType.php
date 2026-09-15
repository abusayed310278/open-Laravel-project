<?php

namespace App\Enums;

enum ReviewableType: string
{
    case Product = 'product';
    case Seller = 'seller';

    public function label(): string
    {
        return match ($this) {
            self::Product => 'Product',
            self::Seller => 'Seller',
        };
    }
}
