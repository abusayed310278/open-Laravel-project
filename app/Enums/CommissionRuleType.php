<?php

namespace App\Enums;

enum CommissionRuleType: string
{
    case Global = 'global';
    case Category = 'category';
    case Seller = 'seller';
    case Business = 'business';
    case Product = 'product';

    public function label(): string
    {
        return match ($this) {
            self::Global => 'Global (platform default)',
            self::Category => 'Category',
            self::Seller => 'Specific Seller',
            self::Business => 'Seller Type (business/saler)',
            self::Product => 'Specific Product',
        };
    }

    /**
     * Highest priority first — the first matching active rule in this
     * order wins, unless two rules share a type (then `priority` breaks
     * the tie).
     */
    public static function specificityOrder(): array
    {
        return [self::Product, self::Seller, self::Category, self::Business, self::Global];
    }
}
