<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DashboardMenu;

$adminMenus = DashboardMenu::where('role_slug', 'admin')->orderBy('sort_order')->get();

echo "Admin Menus Count: " . $adminMenus->count() . PHP_EOL;

foreach ($adminMenus as $menu) {
    echo "Group: {$menu->group_name} | Title: {$menu->title} | Route: {$menu->route_name}" . PHP_EOL;
}
