<?php

use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('shows active hero slides on the home page linking to their product', function () {
    $product = Product::factory()->live()->create();
    HeroSlide::factory()->create(['image' => 'https://cdn.test/slide-one.jpg', 'product_id' => $product->id]);
    HeroSlide::factory()->create(['image' => 'https://cdn.test/slide-two.jpg', 'is_active' => false]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSee('slide-one.jpg', false);
    $response->assertSee(route('products.show', $product), false);
    $response->assertDontSee('slide-two.jpg', false);
});

it('lets an admin upload several slides at once', function () {
    Storage::fake('public');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.settings.hero-slides.store'), [
            'slides' => [
                UploadedFile::fake()->image('a.jpg', 1920, 550),
                UploadedFile::fake()->image('b.jpg', 1920, 550),
            ],
        ])
        ->assertRedirect();

    expect(HeroSlide::count())->toBe(2);
});

it('lets an admin assign a product to a slide', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->live()->create();
    $slide = HeroSlide::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.settings.hero-slides.update', $slide), ['product_id' => $product->id, 'sort_order' => 3, 'is_active' => 1])
        ->assertRedirect();

    expect($slide->fresh()->product_id)->toBe($product->id);
});
