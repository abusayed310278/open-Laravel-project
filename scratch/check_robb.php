<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVerification;

$verifier = User::where('email', 'robb.brakus@example.net')->first();

echo "Verifier Name: " . $verifier->name . "\n";
echo "Verifier ID: " . $verifier->id . "\n";
echo "Verifier Profile Location ID: " . ($verifier->verifierProfile?->assigned_location_id ?? 'NULL') . "\n";

$locationId = $verifier->verifierProfile?->assigned_location_id;

$pvAll = ProductVerification::query()->get();
echo "\nTotal ProductVerification records in database: " . $pvAll->count() . "\n";
foreach ($pvAll as $pv) {
    echo "PV ID: {$pv->id} | Product ID: {$pv->product_id} | Location ID: {$pv->location_id} | Verifier ID: {$pv->verifier_id} | Status: {$pv->status->value}\n";
}

// Check VerifierProductController index queries
$baseProductsQuery = Product::query();
$pendingProductsCount = (clone $baseProductsQuery)->whereIn('verification_status', ['scheduled', 'inspecting', 'pending'])->count();
echo "\nProducts with verification_status in ('scheduled', 'inspecting', 'pending'): {$pendingProductsCount}\n";

// Check ProductVerification appointments query for robb
$appQuery = ProductVerification::query()->whereIn('status', ['scheduled', 'inspecting', 'pending']);
if ($locationId) {
    $appQuery->where(function ($q) use ($verifier, $locationId) {
        $q->where('location_id', $locationId)
          ->orWhere('verifier_id', $verifier->id);
    });
}
echo "ProductVerifications for robb's location/verifier_id: " . $appQuery->count() . "\n";
