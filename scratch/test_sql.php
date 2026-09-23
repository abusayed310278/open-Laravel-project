<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Enums\VerificationStatus;

$q1 = Product::query()->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
echo "Q1 SQL: " . $q1->toRawSql() . "\n";
echo "Q1 Count: " . $q1->count() . "\n";

$q2 = Product::query()->whereIn('verification_status', ['scheduled', 'inspecting', 'pending']);
echo "Q2 SQL: " . $q2->toRawSql() . "\n";
echo "Q2 Count: " . $q2->count() . "\n";

$q3 = Product::query()->whereIn('verification_status', [
    VerificationStatus::Scheduled->value,
    VerificationStatus::Inspecting->value,
    VerificationStatus::Pending->value,
]);
echo "Q3 SQL: " . $q3->toRawSql() . "\n";
echo "Q3 Count: " . $q3->count() . "\n";
