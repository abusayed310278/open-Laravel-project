<?php

use App\Enums\UserRole;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RbacAndFeatureSettingsSeeder;

beforeEach(function () {
    $this->seed(RbacAndFeatureSettingsSeeder::class);
});

test('administrator has permission when assigned in rbac matrix', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->hasPermission('inventory.manage'))->toBeTrue();
});

test('administrator is denied permission when unassigned in rbac matrix', function () {
    $adminRole = Role::where('slug', 'admin')->first();
    $inventoryPerm = Permission::where('slug', 'inventory.manage')->first();

    // Detach inventory.manage from admin role
    $adminRole->permissions()->detach($inventoryPerm->id);

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    expect($admin->hasPermission('inventory.manage'))->toBeFalse();
});

test('administrator inventory route is protected by inventory.manage permission', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    // When inventory.manage is attached to admin
    $response = $this->actingAs($admin)->get(route('admin.inventory.index'));
    $response->assertStatus(200);

    // Detach inventory.manage from admin role
    $adminRole = Role::where('slug', 'admin')->first();
    $inventoryPerm = Permission::where('slug', 'inventory.manage')->first();
    $adminRole->permissions()->detach($inventoryPerm->id);

    $response = $this->actingAs($admin)->get(route('admin.inventory.index'));
    $response->assertStatus(403);
});
