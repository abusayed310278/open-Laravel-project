<?php

namespace App\Enums;

enum StorageStatus: string
{
    case PendingDelivery = 'pending_delivery';
    case Received = 'received';
    case Stored = 'stored';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Released = 'released';
    case Returned = 'returned';
    case Damaged = 'damaged';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::PendingDelivery => 'Pending Delivery',
            self::Received => 'Received',
            self::Stored => 'Stored',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
            self::Released => 'Released',
            self::Returned => 'Returned',
            self::Damaged => 'Damaged',
            self::Lost => 'Lost',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::PendingDelivery => 'gray',
            self::Received, self::Stored, self::Reserved => 'blue',
            self::Sold, self::Released => 'green',
            self::Returned, self::Damaged, self::Lost => 'red',
        };
    }
}
