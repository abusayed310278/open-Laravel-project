<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Verifier = 'verifier';
    case Business = 'business';
    case Saler = 'saler';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Verifier => 'Verifier',
            self::Business => 'Business',
            self::Saler => 'Saler',
            self::Customer => 'Customer',
        };
    }

    /**
     * The named route to redirect this role to immediately after login.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Verifier => 'verifier.dashboard',
            self::Business => 'business.dashboard',
            self::Saler => 'saler.dashboard',
            self::Customer => 'account.dashboard',
        };
    }
}
