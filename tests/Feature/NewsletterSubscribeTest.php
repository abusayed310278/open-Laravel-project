<?php

use App\Models\NewsletterSubscriber;

it('stores a new subscriber and returns a json success message', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'Buyer@Example.com'])
        ->assertOk()
        ->assertJson(['type' => 'success']);

    $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'buyer@example.com']);
});

it('does not duplicate an existing subscriber', function () {
    NewsletterSubscriber::factory()->create(['email' => 'buyer@example.com']);

    $this->postJson(route('newsletter.subscribe'), ['email' => 'buyer@example.com'])
        ->assertOk()
        ->assertJson(['type' => 'info']);

    expect(NewsletterSubscriber::count())->toBe(1);
});

it('rejects an invalid email', function () {
    $this->postJson(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});
