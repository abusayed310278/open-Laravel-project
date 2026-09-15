<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case Orders = 'orders';
    case Payments = 'payments';
    case Products = 'products';
    case Verification = 'verification';
    case Subscriptions = 'subscriptions';
    case Support = 'support';
    case Warehouse = 'warehouse';

    public function label(): string
    {
        return match ($this) {
            self::Orders => 'Orders & shipping',
            self::Payments => 'Payments & refunds',
            self::Products => 'Product approvals',
            self::Verification => 'KYC & verification',
            self::Subscriptions => 'Subscriptions',
            self::Support => 'Support tickets',
            self::Warehouse => 'Warehouse',
        };
    }
}
