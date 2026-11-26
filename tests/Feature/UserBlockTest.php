<?php

use App\Models\Product;
use App\Models\User;
use App\Models\UserBlock;

it('requires authentication to block a user', function () {
    $user = User::factory()->create();

    $this->post(route('users.block', $user))
        ->assertRedirect(route('login'));
});

it('prevents a user from blocking themselves', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('users.block', $user))
        ->assertRedirect()
        ->assertSessionHas('error', 'You cannot block yourself.');

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/users/{$user->id}/block")
        ->assertStatus(422)
        ->assertJsonFragment(['message' => 'You cannot block yourself.']);

    expect(UserBlock::count())->toBe(0);
});

it('allows a user to block another user via web and API', function () {
    $userA = User::factory()->create();
    $seller = User::factory()->create();

    $this->actingAs($userA)
        ->post(route('users.block', $seller))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($userA->hasBlocked($seller))->toBeTrue();
    expect($seller->isBlockedBy($userA))->toBeTrue();

    // Test API block for another user
    $userB = User::factory()->create();
    $this->actingAs($userB, 'sanctum')
        ->postJson("/api/users/{$seller->id}/block")
        ->assertStatus(200)
        ->assertJsonFragment(['message' => 'User blocked successfully.']);

    expect($userB->hasBlocked($seller))->toBeTrue();
});

it('does not show blocked sellers products to the user', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();
    $otherSeller = User::factory()->create();

    $blockedProduct = Product::factory()->live()->create(['user_id' => $seller->id]);
    $visibleProduct = Product::factory()->live()->create(['user_id' => $otherSeller->id]);

    // Before blocking, products are visible
    $this->actingAs($user);
    $liveProductIds = Product::query()->live()->pluck('id')->all();
    expect($liveProductIds)->toContain($blockedProduct->id)
        ->and($liveProductIds)->toContain($visibleProduct->id);

    // Block the seller
    $user->block($seller);

    // Live query for authenticated user automatically excludes blocked seller products
    $filteredProductIds = Product::query()->live()->pluck('id')->all();
    expect($filteredProductIds)->not->toContain($blockedProduct->id)
        ->and($filteredProductIds)->toContain($visibleProduct->id);

    // API query also excludes blocked seller products
    $this->actingAs($user, 'sanctum')
        ->getJson('/api/products')
        ->assertOk()
        ->assertJsonMissing(['id' => $blockedProduct->id]);
});

it('allows a user to unblock a previously blocked user', function () {
    $user = User::factory()->create();
    $seller = User::factory()->create();

    $user->block($seller);
    expect($user->hasBlocked($seller))->toBeTrue();

    $this->actingAs($user)
        ->post(route('users.unblock', $seller))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($user->fresh()->hasBlocked($seller))->toBeFalse();
});
