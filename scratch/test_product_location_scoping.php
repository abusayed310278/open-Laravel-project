<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Enums\VerificationStatus;

$verifiers = User::where('role', 'verifier')->get();

foreach ($verifiers as $v) {
    $locId = $v->verifierProfile?->assigned_location_id;
    $locName = $v->verifierProfile?->location?->name ?? 'Unassigned (Null Location)';

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

    $pendingCount = (clone $pQuery)->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending])->count();
    $totalCount = (clone $pQuery)->count();

    echo "Verifier: {$v->name} ({$v->email})\n";
    echo "  -> Location: {$locName} (ID: " . ($locId ?? 'NULL') . ")\n";
    echo "  -> Scoped Pending Products: {$pendingCount}\n";
    echo "  -> Scoped Total Products: {$totalCount}\n\n";
}
