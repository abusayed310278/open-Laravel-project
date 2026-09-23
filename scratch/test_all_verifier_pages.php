<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;

$robb = User::where('email', 'robb.brakus@example.net')->first();
echo "Testing for Verifier: {$robb->name} ({$robb->email})\n";
echo "Assigned Location ID: " . ($robb->verifierProfile?->assigned_location_id ?? 'NULL') . "\n\n";

// 1. Test Verifier ProductController index with verification_status=pending_queue
$req1 = Request::create('/verifier/products', 'GET', ['verification_status' => 'pending_queue']);
$pQuery = Product::query()
    ->with(['user', 'category', 'brand', 'images'])
    ->latest();

if ($req1->filled('verification_status')) {
    $vStatusVal = $req1->query('verification_status');
    if (in_array($vStatusVal, ['pending', 'pending_queue', 'queue'], true)) {
        $pQuery->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
    } else {
        $pQuery->where('verification_status', $vStatusVal);
    }
}
$pResults = $pQuery->get();
echo "1. Verifier ProductController (verification_status=pending_queue): " . $pResults->count() . " products\n";

// 2. Test AppointmentController index with status=pending_queue for robb
$req2 = Request::create('/verifier/appointments', 'GET', ['status' => 'pending_queue']);
$aQuery = ProductVerification::query()
    ->with(['product', 'seller', 'location'])
    ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);

$locationId = $robb->verifierProfile?->assigned_location_id;
if ($locationId) {
    $aQuery->where(function ($q) use ($robb, $locationId) {
        $q->where('location_id', $locationId)
          ->orWhere('verifier_id', $robb->id);
    });
}

if ($req2->filled('status')) {
    $statusVal = $req2->query('status');
    if (in_array($statusVal, ['pending', 'pending_queue', 'queue'], true)) {
        $aQuery->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
    } else {
        $aQuery->where('status', $statusVal);
    }
}
$aResults = $aQuery->get();
echo "2. AppointmentController index for robb (status=pending_queue): " . $aResults->count() . " appointments\n";

// 3. Test for a verifier WITH an assigned location (e.g. Location 1 or Location 4)
$verifierWithLocation = User::where('role', 'verifier')->whereHas('verifierProfile', fn($q) => $q->whereNotNull('assigned_location_id'))->first();
if ($verifierWithLocation) {
    $locId = $verifierWithLocation->verifierProfile->assigned_location_id;
    echo "\nTesting for Verifier WITH Location: {$verifierWithLocation->name} (Location ID: {$locId})\n";
    
    $locQuery = ProductVerification::query()
        ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])
        ->where(function ($q) use ($verifierWithLocation, $locId) {
            $q->where('location_id', $locId)
              ->orWhere('verifier_id', $verifierWithLocation->id);
        });
    echo "Appointments for this verifier's location: " . $locQuery->count() . "\n";
}
