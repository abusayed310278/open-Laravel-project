<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\VerifierProfile;
use App\Models\VerificationLocation;

$allVerifiers = User::where('role', 'verifier')->get();
echo "All Verifier Users:\n";
foreach ($allVerifiers as $v) {
    echo "ID: {$v->id} | Name: {$v->name} | Email: {$v->email} | LocationID: " . ($v->verifierProfile?->assigned_location_id ?? 'NULL') . "\n";
}

$locations = VerificationLocation::all();
echo "\nVerification Locations:\n";
foreach ($locations as $loc) {
    echo "ID: {$loc->id} | Name: {$loc->name}\n";
}
