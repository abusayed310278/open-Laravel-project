<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('renders the advanced search page', function () {
    $this->get(route('search.advanced'))->assertOk()->assertSee('Advanced Search');
});

it('filters shop results by match mode, exclusions and condition', function () {
    $blue = Product::factory()->live()->create(['title' => 'Blue Phone', 'condition' => 'used']);
    $red = Product::factory()->live()->create(['title' => 'Red Phone', 'condition' => 'new']);
    $cracked = Product::factory()->live()->create(['title' => 'Blue Phone cracked', 'condition' => 'used']);

    $titles = fn (array $query) => collect($this->get(route('shop', $query))->assertOk()->viewData('products')->items())->pluck('id');

    expect($titles(['q' => 'blue phone', 'match' => 'all']))->toContain($blue->id, $cracked->id)->not->toContain($red->id)
        ->and($titles(['q' => 'blue red', 'match' => 'any']))->toContain($blue->id, $red->id)
        ->and($titles(['q' => 'phone', 'exclude' => 'cracked']))->not->toContain($cracked->id)
        ->and($titles(['q' => 'phone', 'condition' => ['used']]))->not->toContain($red->id);
});
