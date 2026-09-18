<?php

namespace App\Enums;

enum ProductGrade: string
{
    case A = 'A';
    case B = 'B';
    case C = 'C';
    case Ungraded = 'ungraded';

    public function label(): string
    {
        return match ($this) {
            self::A => 'Grade A · Like New',
            self::B => 'Grade B · Good',
            self::C => 'Grade C · Fair',
            self::Ungraded => 'Ungraded',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::A => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60',
            self::B => 'bg-blue-50 text-blue-700 border border-blue-200/60',
            self::C => 'bg-amber-50 text-amber-800 border border-amber-200/60',
            self::Ungraded => 'bg-gray-100 text-gray-700 border border-gray-200',
        };
    }
}
