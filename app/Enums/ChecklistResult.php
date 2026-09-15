<?php

namespace App\Enums;

enum ChecklistResult: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case NotApplicable = 'na';

    public function label(): string
    {
        return match ($this) {
            self::Pass => 'Pass',
            self::Fail => 'Fail',
            self::NotApplicable => 'N/A',
        };
    }
}
