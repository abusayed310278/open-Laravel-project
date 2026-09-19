<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case OutForDelivery = 'out_for_delivery';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Returned = 'returned';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Packed => 'Packed',
            self::Shipped => 'Shipped',
            self::OutForDelivery => 'Out for Delivery',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Returned => 'Returned',
            self::Refunded => 'Refunded',
            self::Failed => 'Failed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending, self::Confirmed => 'gray',
            self::Processing, self::Packed, self::Shipped, self::OutForDelivery => 'blue',
            self::Delivered => 'green',
            self::Cancelled, self::Failed => 'red',
            self::Returned, self::Refunded => 'amber',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending, self::Confirmed => 'bg-amber-50 text-amber-700 border border-amber-200/60',
            self::Processing, self::Packed, self::Shipped, self::OutForDelivery => 'bg-blue-50 text-blue-700 border border-blue-200/60',
            self::Delivered => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
            self::Cancelled, self::Failed => 'bg-rose-50 text-rose-700 border border-rose-200/60',
            self::Returned, self::Refunded => 'bg-orange-50 text-orange-700 border border-orange-200/60',
        };
    }
}
