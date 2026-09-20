<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DashboardMenu;
use App\Services\SiteManagementService;
use Database\Seeders\RbacAndFeatureSettingsSeeder;

echo "Cleaning up duplicate and merged menu items for Admin..." . PHP_EOL;

// Delete all old admin dashboard menus
DashboardMenu::where('role_slug', 'admin')->delete();

// Re-run seeder
(new RbacAndFeatureSettingsSeeder())->run();

// Clear cache
app(SiteManagementService::class)->clearCache();

echo "Menus after cleanup for Admin:" . PHP_EOL;
$menus = DashboardMenu::where('role_slug', 'admin')->orderBy('sort_order')->get();
foreach ($menus as $m) {
    echo "- [{$m->group_name}] {$m->title} ({$m->route_name})" . PHP_EOL;
}
