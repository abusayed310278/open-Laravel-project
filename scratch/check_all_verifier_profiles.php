<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\VerifierProfile;
use App\Models\VerificationLocation;
use App\Models\ProductVerification;

$verifiers = User::where('role', 'verifier')->get();
echo "Verifier Users:\n";
foreach ($verifiers as $v) {
    $profile = $v->verifierProfile;
    $locName = $profile?->location?->name ?? 'No Location Assigned (Global)';
    $locPendingCount = $profile?->assigned_location_id 
        ? ProductVerification::where('location_id', $profile->assigned_location_id)->whereIn('status', ['scheduled', 'inspecting', 'pending'])->count()
        : ProductVerification::whereIn('status', ['scheduled', 'inspecting', 'pending'])->count();
    
    echo "ID: {$v->id} | Name: {$v->name} | Email: {$v->email}\n";
    echo "  -> Location: {$locName} (ID: " . ($profile?->assigned_location_id ?? 'NULL') . ")\n";
    echo "  -> Location Pending Queue Count: {$locPendingCount}\n";
}
