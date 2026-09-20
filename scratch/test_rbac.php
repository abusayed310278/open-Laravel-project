<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacAndFeatureSettingsSeeder;
use Illuminate\Support\Facades\Auth;

echo "--- Testing Administrator Permission Checks ---" . PHP_EOL;

// 1. Seed database
(new RbacAndFeatureSettingsSeeder())->run();

// 2. Create Admin user
$admin = User::factory()->create(['role' => UserRole::Admin, 'email_verified_at' => now()]);
$adminRole = Role::where('slug', 'admin')->first();
$inventoryPerm = Permission::where('slug', 'inventory.manage')->first();

Auth::login($admin);

// Test 1: Attached permission -> user_can returns true
$adminRole->permissions()->syncWithoutDetaching([$inventoryPerm->id]);
$can1 = user_can('inventory.manage');
echo "Check 1 (user_can('inventory.manage') when attached): " . ($can1 ? "PASS (true)" : "FAIL (false)") . PHP_EOL;

// Test 2: Detached permission -> user_can returns false
$adminRole->permissions()->detach($inventoryPerm->id);
$can2 = user_can('inventory.manage');
echo "Check 2 (user_can('inventory.manage') when detached): " . (!$can2 ? "PASS (false)" : "FAIL (true)") . PHP_EOL;

// Test 3: Re-attach -> user_can returns true
$adminRole->permissions()->attach($inventoryPerm->id);
$can3 = user_can('inventory.manage');
echo "Check 3 (user_can('inventory.manage') when re-attached): " . ($can3 ? "PASS (true)" : "FAIL (false)") . PHP_EOL;

echo "--- All checks completed successfully! ---" . PHP_EOL;
