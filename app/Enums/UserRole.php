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
            self::Business => 'Store Owner',
            self::Saler => 'Seller',
            self::Customer => 'User',
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

    public function badgeClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-red-50 text-red-700 border border-red-200/60',
            self::Verifier => 'bg-amber-50 text-amber-800 border border-amber-200/60',
            self::Business => 'bg-purple-50 text-purple-700 border border-purple-200/60',
            self::Saler => 'bg-blue-50 text-blue-700 border border-blue-200/60',
            self::Customer => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
        };
    }
}
