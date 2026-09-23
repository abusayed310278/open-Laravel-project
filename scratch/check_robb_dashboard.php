<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ProductVerification;
use App\Models\Product;
use App\Enums\VerificationStatus;
use App\Enums\ProductStatus;
use App\Enums\ProductApprovalStatus;

$robb = User::where('email', 'robb.brakus@example.net')->first();
echo "Testing Dashboard data for: {$robb->name} ({$robb->email})\n";
echo "Verifier Profile Location ID: " . ($robb->verifierProfile?->assigned_location_id ?? 'NULL') . "\n\n";

$locationId = $robb->verifierProfile?->assigned_location_id;
$baseQuery = ProductVerification::query();

if ($locationId) {
    $locationPendingCount = (clone $baseQuery)
        ->where(function ($q) use ($robb, $locationId) {
            $q->where('location_id', $locationId)
              ->orWhere('verifier_id', $robb->id);
        })
        ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
        ->count();

    if ($locationPendingCount > 0) {
        $baseQuery->where(function ($q) use ($robb, $locationId) {
            $q->where('location_id', $locationId)
              ->orWhere('verifier_id', $robb->id);
        });
    }
}

$todayAppointmentsCount = (clone $baseQuery)
    ->whereDate('scheduled_at', '<=', today())
    ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
    ->count();

$pendingInspectionsCount = (clone $baseQuery)
    ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
    ->count();

echo "Dashboard Stat Cards:\n";
echo "- Today's Appointments: {$todayAppointmentsCount}\n";
echo "- Pending Queue: {$pendingInspectionsCount}\n";

// Check Verifier ProductController index stat cards
$baseProductQuery = Product::query();
$totalCount = (clone $baseProductQuery)->count();
$activeCount = (clone $baseProductQuery)->where(function ($q) {
    $q->where('status', ProductStatus::Published)
        ->orWhere('approval_status', ProductApprovalStatus::Approved);
})->count();
$inactiveCount = (clone $baseProductQuery)->where(function ($q) {
    $q->where('status', ProductStatus::Draft)
        ->orWhere('status', ProductStatus::Suspended)
        ->orWhere('status', ProductStatus::Archived);
})->count();
$pendingCount = (clone $baseProductQuery)->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])->count();
$verifiedCount = (clone $baseProductQuery)->where('verification_status', VerificationStatus::Verified)->count();

echo "\nProducts Index Stat Cards:\n";
echo "- Total Listings: {$totalCount}\n";
echo "- Active Products: {$activeCount}\n";
echo "- Inactive / Draft: {$inactiveCount}\n";
echo "- Pending Queue: {$pendingCount}\n";
echo "- Verified & Graded: {$verifiedCount}\n";
