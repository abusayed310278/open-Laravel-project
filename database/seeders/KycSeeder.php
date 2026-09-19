<?php

namespace Database\Seeders;

use App\Enums\KycDocumentStatus;
use App\Enums\KycDocumentType;
use App\Enums\KycStatus;
use App\Models\User;
use App\Models\UserVerification;
use App\Models\VerificationDocument;
use Illuminate\Database\Seeder;

class KycSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. TechHub Electronics Admin (Business) - Submitted
        $v1 = UserVerification::updateOrCreate(
            ['user_id' => 7],
            [
                'status' => KycStatus::Submitted,
                'submitted_at' => now()->subDays(2),
                'notes' => 'Submitted trade license and VAT documents for business verification.',
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v1->id, 'document_type' => KycDocumentType::TradeLicense],
            [
                'document_number' => 'TL-2026-98765',
                'file_path' => 'kyc/7/trade_license.pdf',
                'status' => KycDocumentStatus::Pending,
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v1->id, 'document_type' => KycDocumentType::Vat],
            [
                'document_number' => 'VAT-44589201',
                'file_path' => 'kyc/7/vat_certificate.pdf',
                'status' => KycDocumentStatus::Pending,
            ]
        );

        // 2. Prime Gadgets Trading Admin (Business) - Under Review
        $v2 = UserVerification::updateOrCreate(
            ['user_id' => 8],
            [
                'status' => KycStatus::UnderReview,
                'submitted_at' => now()->subDays(4),
                'notes' => 'Under review by compliance team.',
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v2->id, 'document_type' => KycDocumentType::Nid],
            [
                'document_number' => 'NID-8849201948',
                'file_path' => 'kyc/8/nid_front_back.pdf',
                'status' => KycDocumentStatus::Pending,
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v2->id, 'document_type' => KycDocumentType::TradeLicense],
            [
                'document_number' => 'TL-2026-11223',
                'file_path' => 'kyc/8/trade_license.pdf',
                'status' => KycDocumentStatus::Pending,
            ]
        );

        // 3. NextGen Devices Co. Admin (Business) - Approved
        $v3 = UserVerification::updateOrCreate(
            ['user_id' => 9],
            [
                'status' => KycStatus::Approved,
                'submitted_at' => now()->subDays(10),
                'verified_at' => now()->subDays(8),
                'verified_by' => $admin?->id,
                'notes' => 'Verified successfully after document cross-checking.',
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v3->id, 'document_type' => KycDocumentType::Passport],
            [
                'document_number' => 'A12948201',
                'file_path' => 'kyc/9/passport.pdf',
                'status' => KycDocumentStatus::Approved,
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(8),
            ]
        );

        // 4. Ahmed's Electronics Corner (Seller) - Rejected
        $v4 = UserVerification::updateOrCreate(
            ['user_id' => 11],
            [
                'status' => KycStatus::Rejected,
                'submitted_at' => now()->subDays(5),
                'verified_at' => now()->subDays(3),
                'verified_by' => $admin?->id,
                'rejection_reason' => 'Trade license image was blurry and expired. Please re-upload a valid license.',
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v4->id, 'document_type' => KycDocumentType::TradeLicense],
            [
                'document_number' => 'TL-2024-00192',
                'file_path' => 'kyc/11/trade_license_expired.pdf',
                'status' => KycDocumentStatus::Rejected,
                'verified_by' => $admin?->id,
                'verified_at' => now()->subDays(3),
                'remarks' => 'Expired trade license.',
            ]
        );

        // 5. Fatima's Tech Deals (Seller) - Submitted
        $v5 = UserVerification::updateOrCreate(
            ['user_id' => 12],
            [
                'status' => KycStatus::Submitted,
                'submitted_at' => now()->subHours(5),
                'notes' => 'Newly submitted vendor application.',
            ]
        );
        VerificationDocument::updateOrCreate(
            ['verification_id' => $v5->id, 'document_type' => KycDocumentType::Nid],
            [
                'document_number' => 'NID-9920194821',
                'file_path' => 'kyc/12/nid.pdf',
                'status' => KycDocumentStatus::Pending,
            ]
        );
    }
}

