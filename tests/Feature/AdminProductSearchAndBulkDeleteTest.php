<?php

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RbacAndFeatureSettingsSeeder;

beforeEach(function () {
    $this->seed(RbacAndFeatureSettingsSeeder::class);
});

test('admin can search products by title or sku', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $prod1 = Product::factory()->create(['title' => 'Special Wireless Headphones', 'sku' => 'HD-900']);
    $prod2 = Product::factory()->create(['title' => 'Smart Watch Pro', 'sku' => 'SW-100']);

    $response = $this->actingAs($admin)->get(route('admin.products.index', ['search' => 'Wireless']));

    $response->assertStatus(200);
    $response->assertSee('Special Wireless Headphones');
    $response->assertDontSee('Smart Watch Pro');
});

test('admin can bulk delete multiple selected products', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $prod1 = Product::factory()->create(['title' => 'Item to delete 1']);
    $prod2 = Product::factory()->create(['title' => 'Item to delete 2']);
    $prod3 = Product::factory()->create(['title' => 'Item to keep']);

    $response = $this->actingAs($admin)->delete(route('admin.products.bulk-destroy'), [
        'ids' => [$prod1->id, $prod2->id],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('products', ['id' => $prod1->id]);
    $this->assertDatabaseMissing('products', ['id' => $prod2->id]);
    $this->assertDatabaseHas('products', ['id' => $prod3->id]);
});
