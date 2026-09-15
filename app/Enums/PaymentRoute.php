<?php

namespace App\Enums;

enum PaymentRoute: string
{
    case Openbox = 'openbox';
    case Seller = 'seller';
}
