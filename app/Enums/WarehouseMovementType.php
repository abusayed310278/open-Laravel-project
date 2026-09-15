<?php

namespace App\Enums;

enum WarehouseMovementType: string
{
    case In = 'in';
    case Out = 'out';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';

    public function label(): string
    {
        return match ($this) {
            self::In => 'Received In',
            self::Out => 'Released Out',
            self::Transfer => 'Transferred',
            self::Adjustment => 'Adjustment',
        };
    }
}
