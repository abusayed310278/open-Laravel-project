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
}
