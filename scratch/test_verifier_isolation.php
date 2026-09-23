<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ProductVerification;
use App\Models\Product;
use App\Enums\VerificationStatus;

$verifiers = User::where('role', 'verifier')->get();

echo "=== VERIFIER HUB ISOLATION REPORT ===\n\n";

foreach ($verifiers as $v) {
    $locId = $v->verifierProfile?->assigned_location_id;
    $locName = $v->verifierProfile?->location?->name ?? 'Unassigned (Null Location)';

    // 1. Appointment Queue
    $aQuery = ProductVerification::query()->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
    if ($locId) {
        $aQuery->where(function ($q) use ($v, $locId) {
            $q->where('location_id', $locId)->orWhere('verifier_id', $v->id);
        });
    } else {
        $aQuery->where(function ($q) use ($v) {
            $q->whereNull('location_id')->orWhere('verifier_id', $v->id);
        });
    }

    // 2. Product Inventory
    $pQuery = Product::query();
    if ($locId) {
        $pQuery->whereHas('verifications', function ($q) use ($v, $locId) {
            $q->where('location_id', $locId)->orWhere('verifier_id', $v->id);
        });
    } else {
        $pQuery->where(function ($q) use ($v) {
            $q->whereHas('verifications', fn($vq) => $vq->whereNull('location_id')->orWhere('verifier_id', $v->id))
              ->orDoesntHave('verifications');
        });
    }

    echo "Verifier: {$v->name} ({$v->email})\n";
    echo "  Hub: {$locName}\n";
    echo "  Appointment Queue Pending Items: " . $aQuery->count() . "\n";
    echo "  Product Inventory Scoped Items: " . $pQuery->count() . "\n\n";
}
