<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Enums\VerificationStatus;

$verificationCounts = Product::query()
    ->selectRaw('verification_status, count(*) as count')
    ->groupBy('verification_status')
    ->pluck('count', 'verification_status')
    ->toArray();

echo "Verification Status Breakdown:\n";
print_r($verificationCounts);

$pendingQuery = Product::query()->whereIn('verification_status', [
    VerificationStatus::Scheduled->value,
    VerificationStatus::Inspecting->value,
    VerificationStatus::Pending->value,
    VerificationStatus::NotRequested->value,
]);

echo "Pending/Inspecting/Scheduled/NotRequested products:\n";
foreach ($pendingQuery->get() as $p) {
    echo "ID: {$p->id} | Title: {$p->title} | vStatus: {$p->verification_status->value} | Status: {$p->status->value}\n";
}
