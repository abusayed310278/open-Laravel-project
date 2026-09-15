<?php

namespace App\Enums;

enum SupportTicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingCustomer = 'waiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::WaitingCustomer => 'Waiting on Customer',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::InProgress => 'amber',
            self::WaitingCustomer => 'amber',
            self::Resolved => 'green',
            self::Closed => 'gray',
        };
    }
}
