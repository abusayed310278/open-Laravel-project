<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\RbacAndFeatureSettingsSeeder;
use App\Services\SiteManagementService;

echo "Reseeding RBAC and clearing site management cache..." . PHP_EOL;

(new RbacAndFeatureSettingsSeeder())->run();
app(SiteManagementService::class)->clearCache();

echo "Done!" . PHP_EOL;
