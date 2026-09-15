<?php

namespace App\Enums;

enum ProductPublicationStatus: string
{
    case Unpublished = 'unpublished';
    case Published = 'published';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Unpublished => 'Unpublished',
            self::Published => 'Published',
            self::Expired => 'Expired',
        };
    }
}
