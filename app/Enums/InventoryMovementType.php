<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case WarehouseIn = 'warehouse_in';
    case WarehouseOut = 'warehouse_out';
    case Transfer = 'transfer';
    case Reservation = 'reservation';
    case Release = 'release';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sale => 'Sale',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
            self::WarehouseIn => 'Warehouse In',
            self::WarehouseOut => 'Warehouse Out',
            self::Transfer => 'Transfer',
            self::Reservation => 'Reservation',
            self::Release => 'Release',
        };
    }
}
