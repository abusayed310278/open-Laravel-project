<?php

namespace App\Enums;

enum ProductReportReason: string
{
    case Counterfeit = 'counterfeit';
    case Misleading = 'misleading';
    case Prohibited = 'prohibited';
    case Defective = 'defective';
    case Fraud = 'fraud';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Counterfeit => 'Counterfeit or fake item',
            self::Misleading => 'Misleading title, description, or photos',
            self::Prohibited => 'Prohibited, illegal, or unsafe item',
            self::Defective => 'Defective, damaged, or incorrect condition',
            self::Fraud => 'Suspected fraud, scam, or price gouging',
            self::Other => 'Other violation or issue',
        };
    }
}
