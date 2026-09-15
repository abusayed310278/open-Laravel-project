<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Draft = 'draft';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case Sold = 'sold';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Published => 'Published',
            self::Expired => 'Expired',
            self::Suspended => 'Suspended',
            self::Sold => 'Sold',
            self::Archived => 'Archived',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft, self::Archived => 'gray',
            self::PendingApproval => 'blue',
            self::Approved => 'blue',
            self::Rejected, self::Suspended => 'red',
            self::Published => 'green',
            self::Expired => 'amber',
            self::Sold => 'gray',
        };
    }
}
