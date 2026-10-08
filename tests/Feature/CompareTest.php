<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('shows an empty state when no products are given', function () {
    $this->get(route('compare'))->assertOk()->assertSee('No products to compare yet.');
});

it('renders a shareable comparison from the ids in the url, in order', function () {
    $first = Product::factory()->live()->create(['title' => 'Alpha Phone']);
    $second = Product::factory()->live()->create(['title' => 'Beta Phone']);

    $response = $this->get(route('compare', ['ids' => "{$second->id},{$first->id}"]))
        ->assertOk()
        ->assertSeeInOrder(['Beta Phone', 'Alpha Phone']);

    expect($response->viewData('products')->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('ignores unpublished products, junk ids and anything past four', function () {
    $live = Product::factory()->live()->count(5)->create();
    $draft = Product::factory()->create();

    $ids = $live->pluck('id')->push($draft->id, 'abc', 0)->implode(',');

    $products = $this->get(route('compare', ['ids' => $ids]))->assertOk()->viewData('products');

    expect($products)->toHaveCount(4)->and($products->pluck('id'))->not->toContain($draft->id);
});
