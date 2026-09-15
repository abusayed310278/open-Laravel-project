<?php

namespace App\Enums;

enum SupportTicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Normal => 'Normal',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Low => 'gray',
            self::Normal => 'blue',
            self::High => 'amber',
            self::Urgent => 'red',
        };
    }
}
