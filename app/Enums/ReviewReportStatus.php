<?php

namespace App\Enums;

enum ReviewReportStatus: string
{
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Resolved => 'Resolved',
            self::Dismissed => 'Dismissed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Pending => 'blue',
            self::Resolved => 'green',
            self::Dismissed => 'gray',
        };
    }
}
