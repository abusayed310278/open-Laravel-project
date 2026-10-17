<?php

use App\Enums\ReviewableType;
use App\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

it('asks guests to log in before writing a review', function () {
    $product = Product::factory()->live()->create();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('to write a review');

    $this->post(route('products.reviews.store', $product), ['rating' => 5, 'body' => 'Great'])
        ->assertRedirect(route('login'));
});

it('lets any logged-in user review a product once without ordering it', function () {
    $product = Product::factory()->live()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 4, 'title' => 'Nice', 'body' => 'Works well'])
        ->assertRedirect();

    $review = Review::first();
    expect($review->reviewer_id)->toBe($user->id)
        ->and($review->reviewable_type)->toBe(ReviewableType::Product)
        ->and($review->reviewable_id)->toBe($product->id)
        ->and($review->order_id)->toBeNull()
        ->and($review->status)->toBe(ReviewStatus::Pending);

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'body' => 'Again'])
        ->assertStatus(422);

    expect(Review::count())->toBe(1);
});

it('does not let sellers review their own product', function () {
    $product = Product::factory()->live()->create();

    $this->actingAs($product->user)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'body' => 'Mine is great'])
        ->assertForbidden();
});
