<?php

namespace App\Enums;

enum KycDocumentType: string
{
    case Nid = 'nid';
    case Passport = 'passport';
    case DrivingLicense = 'driving_license';
    case TradeLicense = 'trade_license';
    case Vat = 'vat';
    case Tax = 'tax';
    case BusinessRegistration = 'business_registration';
    case AddressProof = 'address_proof';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Nid => 'National ID',
            self::Passport => 'Passport',
            self::DrivingLicense => 'Driving License',
            self::TradeLicense => 'Trade License',
            self::Vat => 'VAT Certificate',
            self::Tax => 'Tax Certificate',
            self::BusinessRegistration => 'Business Registration',
            self::AddressProof => 'Proof of Address',
            self::Other => 'Other Document',
        };
    }
}
