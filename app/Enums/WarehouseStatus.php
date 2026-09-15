<?php

namespace App\Enums;

enum WarehouseStatus: string
{
    case NotDeposited = 'not_deposited';
    case Pending = 'pending';
    case Stored = 'stored';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Released = 'released';

    public function label(): string
    {
        return match ($this) {
            self::NotDeposited => 'Not Deposited',
            self::Pending => 'Pending',
            self::Stored => 'Stored',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
            self::Released => 'Released',
        };
    }
}
